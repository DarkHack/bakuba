<?php
/**
 * Copyright 2011-2026 Nicolas "DarkHack" Chapuis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

global $bakubaFolder;if (!isset($bakubaFolder)) $bakubaFolder="$dwFrameWorkDir/bakuba";

require_once "$bakubaFolder/controller/abstract/ctrlAuthenticated.php";
require_once "$bakubaFolder/object/objValue64.php";

require_once "$bakubaFolder/object/json/objJsonCache.php";

abstract class ctrlJsonCache extends ctrlAuthenticated {
	
	protected $actions = array();
	protected $valObject = null;
	
	static public function getJsonCacheName( $className, $objectDir, $dataDir ) {
		// load cache
		global $cache;if (!isset($cache) || ! is_array($cache)) $cache=array();
		
		// include object php struct
		require_once "$objectDir/{$className}.php";
		//var_dump(array ("require" => "$objectDir/{$className}.php","objectDir" => $objectDir,"dataDir" => $dataDir, "test" => new $className()));
		
		if ( ! is_dir($dataDir) ) mkdir($dataDir,0777,true);
		
		if ( ! isset($cache["".$className]) || !is_object($cache["".$className]) )
			$cache["".$className] = new objJsonCache($className,"$dataDir/{$className}.json");
		
		return $cache["".$className];	
	}
	
	protected function getJsonCache( $objectDir, $dataDir ) {
		//var_dump(array ("objectDir" => $objectDir,"dataDir" => $dataDir, "test" => $this->getClassName()));
		return self::getJsonCacheName( $this->getClassName(), $objectDir, $dataDir );
	}
	
	public function __construct( $className ) {
		parent::__construct( $className );
		$this->valObject = new objValue64( $this->getClassName()."_json" );
		$this->valObject->setValue( "{}" );
		$this->addUnsecureValue( $this->valObject );
	}
	
	public function actionLogged() {
		$action = $this->searchAction();
		if ( $action == null )
			return json_encode(array("error" =>"action is null","controller" => "ctrlJsonCache"));
		else {
			$jsonStr = $this->valObject->getValue( );
			$jsonObj = !empty($jsonStr)?  
				dwJson::readJsonObject($this->getClassName(), json_decode($jsonStr)) 
				: null
			;
			if ( $action["writelog"] == "true" )
				$this->logAction($action,$jsonObj);
			$cpt=0;
			if ( is_object($this->dwSession) ) {
				while ($this->dwSession->_isset("last-action-".$cpt))
					$cpt++;
				$this->dwSession->set("last-action-".$cpt,$this->getClassName()."/".$action["action"]);
			}
			return $this->action( $action["action"],  $jsonObj );
		}
	}
	
	private function searchAction() {
		$actionName = null;
		if ( isset($_POST["action"]) )
			$actionName = base64_decode($_POST["action"]);
		
		foreach($this->actions as $actionDef) {
			if ( $actionName == $actionDef["action"] )
				return $actionDef;
		}
		return null;
	}
	/*
	public function getJs() {
		$jsCode="
		var {$this->className} = function (urlController) {
			var ctrlObj = dwController.create(urlController);
		";
		foreach($this->actions as $actionDef) {
			$action = $actionDef["action"];
			$jsCode.="
				this.{$action} = function(my{$this->className}, handle) {
					dwKernel.debug(\"{$this->className} {$action} [\"+urlController+\"]\",my{$this->className});
					ctrlObj( { sessionid: thedwSession.getId(),action: encode64(\"{$action}\"), {$this->className}: encode64(dwJson.toJSON(my{$this->className})) }, handle);
				};";
		}
		$jsCode.="};";
		return $jsCode;
	}
	*/
	
	private function logAction($action,$jsonObj) {
		global $dwActionLogger,$dwFrameWorkDir;
		if (! isset($dwActionLogger) )  $dwActionLogger = self::getJsonCacheName( "dwActionLogger", "$dwFrameWorkDir/objects", session_save_path()."/WP_dwFrameWork" );
		$jsonActionLogger = new dwActionLogger();
		$jsonActionLogger->_user = $this->dwSession->_logged;
		$jsonActionLogger->_date = date(DATE_RFC2822);
		$jsonActionLogger->_classname = $this->className;
		$jsonActionLogger->_action = $action["action"];
		$jsonActionLogger->_object = $jsonObj;
		
		$dwActionLogger->add($jsonActionLogger);
	}
}
