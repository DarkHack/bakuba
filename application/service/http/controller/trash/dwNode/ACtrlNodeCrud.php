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


use darkphp\logger\DwLogger as DwLogger;

use bakuba\kernel\DwCrudCache as DwCrudCache;
use bakuba\kernel\objects\web\DwWebAction as DwWebAction;
use darkphp\web\DwWebValue64 as DwWebValue64;

use bakuba\kernel\dwController\abstracts\ACtrlLoginable as ACtrlLoginable;

//use bakuba\page\PageCrud as PageCrud;
abstract class ACtrlCrudHtml extends ACtrlLoginable {
	
	public $actList;
	public $actAdd;
	public $actAddHtml;
	public $actEdit;
	public $actEditHtml;
	public $actDelete;
	public $actDeleteHtml;
	public $valId;
	
	private $crudActions = array();
	private $objPrototype = null;
	public $crudValues = array();
	
	private $log;
	public function __construct( $className, $objectDir/*, $dataDir */) {
		try {
			$this->log = DwLogger::getLogger(__CLASS__);
			$this->log->info(".");
			parent::__construct( $className, __CLASS__."_".$className );
			
			//$this->jsonCache = $this->getJsonCache( $objectDir,$dataDir );
			$this->dwCrudCache = new DwCrudCache();
			$this->objPrototype = new $className();
			if (is_array($this->objPrototype) || is_object($this->objPrototype)) {
				foreach($this->objPrototype as $protoKey => $protoValue) {
					$objValue = new objValue( $this->getCanonicalName()."_".$protoKey );
					$objValue->setValue( $protoValue );
					$this->addUnsecureValue( $objValue );
					$this->crudValues[ "".$protoKey ] = $objValue;
				}
			}
			
			$this->actList = new DwWebAction( $this->getCanonicalName()."_list" );
			$this->addCrudAction($this->actList, "list");
			
			$this->actAdd = new DwWebAction( $this->getCanonicalName()."_add" );
			$this->addCrudAction($this->actAdd, "add", true);
			$this->actAddHtml = new DwWebAction( $this->getCanonicalName()."_addHtml" );
			$this->addCrudAction($this->actAddHtml, "addHtml", true);
			
			$this->actEdit = new DwWebAction( $this->getCanonicalName()."_edit" );
			$this->addCrudAction($this->actEdit, "edit", true);
			$this->actEditHtml = new DwWebAction( $this->getCanonicalName()."_editHtml" );
			$this->addCrudAction($this->actEditHtml, "editHtml", true);
			
			$this->actDelete = new DwWebAction( $this->getCanonicalName()."_delete" );
			$this->addCrudAction($this->actDelete, "delete", true);
			$this->actDeleteHtml = new DwWebAction( $this->getCanonicalName()."_deleteHtml" );
			$this->addCrudAction($this->actDeleteHtml, "deleteHtml", true);
			
			$this->valId = new objValue64( $this->getCanonicalName()."_valId" );
			$this->valId->setValue( -1 );
			$this->addUnsecureValue( $this->valId );
	}
	
	protected $dwCrudCache;//DwCrudCache
	public function getCache() {
		return $this->dwCrudCache;
	}
	
	public function addCrudAction(DwWebAction $dwWebAction ,$jsAction ,$writeLog = false ) {
		$this->addDebug(__CLASS__."::addCrudAction");
		$this->crudActions[]=Array(
			"action" => $dwWebAction
			,"jsaction" => "$jsAction"
			,"writelog" => $writeLog
		);
		$this->addUnsecureAction( $dwWebAction );
		//$this->addSessionAction( $dwWebAction );
	}
	
	public function htmlInit( $server ,$get ,$post ,$titre ) {
		$this->pageCrud = new pageCrud( $this, $titre );
		$this->pageCrud->execute( $server ,$get ,$post );
		$this->pageCrud->init($server);
		return $this->pageCrud;
	}
	public function htmlExecute( $server ,$get ,$post ) {
		$this->htmlInit( $server,$get ,$post ,"[".$this->getClassName()."/".$this->getInstanceKey()."] for Drg" );
		$this->pageCrud->listForm( $server );
		return $this->pageCrud;
	}
	public function htmlAction( objAction $objAction, $server, $get, $post ) {
		$dataReturn = $this->htmlInit( $server, $get ,$post ,"[".$this->getClassName().":".$objAction."] for Drg" );
		$this->pageCrud->add("htmlAction[".$objAction."]");
		$className = $this->getClassName();
		if ( $objAction == $this->actDeleteHtml) {
			$jsonObjectId = $this->valId->getValuePlain();
			$jsonObject = $this->jsonCache->getObject($jsonObjectId);
			$this->jsonCache->delete($jsonObject);
			$this->pageCrud->add("delete object(".$jsonObjectId.") by html :ok");
		}elseif ( $objAction == $this->actDelete) {
			$jsonObjectId = $this->valId->getValuePlain();
			$jsonObject = $this->jsonCache->getObject($jsonObjectId);
			$this->pageCrud->add("obj (".$jsonObjectId."):".$jsonObject."<br/>\n");
			if ( $jsonObjectId >= 0 ) {
				$htmlFormAct = $this->getHtmlFormAction( $this->actDeleteHtml, $server );
				$htmlFormAct->add(
					new htmlInput( array( 
						'type' => "hidden"
						, 'name' => $this->valId
						,'value' => base64_encode($jsonObject->_id)
					))
				);
				$htmlFormAct->add(new htmlInput( array( 'type' => "submit",'value' => "Confirmer Suppression" ) ));
				$this->pageCrud->add($htmlFormAct);
				$this->pageCrud->add("ask delete object by html :ok");
			}
		}elseif ( $objAction == $this->actEditHtml ) {
			$jsonObj = new $className();
			if (! empty($this->crudValues)) {
				foreach($this->crudValues as $protoKey => $protoValue) {
					$objValue = new objValue( $this->getCanonicalName()."_".$protoKey );
					$jsonObj->$protoKey = $protoValue->getValue( );
				}
			}
			$this->jsonCache->edit($jsonObj);
			$this->pageCrud->add("edit object by html :ok");
		}elseif ( $objAction == $this->actEdit) {
			$this->pageCrud->editForm($server, $this->jsonCache->getObject($this->valId->getValuePlain()));
			$this->pageCrud->add("edit object by html :ok");
		}elseif ( $objAction == $this->actAddHtml) {
			$jsonObj = new $className();
			if (! empty($this->crudValues)) {
				foreach($this->crudValues as $protoKey => $protoValue) {
					$objValue = new objValue( $this->getCanonicalName()."_".$protoKey );
					$jsonObj->$protoKey = $protoValue->getValue( );
				}
			}
			$this->jsonCache->add($jsonObj);
			$this->pageCrud->add("add object by html :ok");
		} elseif ( $objAction == $this->actAdd) {
			$this->pageCrud->editForm($server, new $className(), true);
			$this->pageCrud->add("add new object by html :ok");
		} else {
			$this->pageCrud->listForm( $server );
		}
		return $this->pageCrud;
	}
	
	public function jsonAction( objAction $objAction, $server, $get, $post ) {
		$this->addDebug(__CLASS__."::jsonAction $objAction");
		return json_encode( array("action not found" => "".$objAction) );
	}
	
	public function sessionAction( objAction $sessionAction, $server, $get, $post ) {
		$response = "";
		try {
			$jsonObj = objJson::readJsonObject($this->getClassName(), @json_decode($this->objValue->getValuePlain()));
			switch($sessionAction) {
			case "".$this->actList:
				$response = json_encode($this->jsonCache->getList());
				break;
			case "".$this->actAdd:
				$response = json_encode(array("add" => $this->jsonCache->add($jsonObj), "jsonObj" => $jsonObj));
				break;
			case "".$this->actEdit:
				//$response.="VALUE:".$this->objValue->getValuePlain()."<br/>\n";
				//$response.=print_r($jsonObj,true);
				$response = json_encode(array("edit" => $this->jsonCache->edit($jsonObj), "jsonObj" => $jsonObj));
				break;
			case "".$this->actDelete:
				$response = json_encode(array("delete" => $this->jsonCache->delete($jsonObj), "jsonObj" => $jsonObj));
				break;
			default:
				$response = json_encode(array("error ".__CLASS__."::sessionAction" => "unknow action :$sessionAction"));
				break;
			}
		}catch(Exception $exception) {
			$response.="ERROR:".$exception->getMessage();
		}
		return $response;
	}
	
	public function jsExecute( $server, $get, $post ) {
		$jsonUrl = new objUrl( $server );	
		$jsonUrl->changeType("json");
		$jsCode="
var {$this->getClassName()} = function () {
	var ctrlObj = dwController.create(\"{$jsonUrl}\");
		";
		foreach($this->crudActions as $actionDef) {
			$jsaction = $actionDef["jsaction"];
			$action = $actionDef["action"];
			if ( $jsaction == "list" )
				$jsCode.="
	this.{$jsaction} = function(handle) {
		//dwKernel.debug(\"{$this->getClassName()} {$action}\");
		ctrlObj( { {$this->valSessionId}:thedwSession.getId() ,{$this->action}: encode64(\"{$action}\") }, handle);
	};
				";
			else {
				if (isset($actionDef["jsprivate"]) && $actionDef["jsprivate"]=="private")
					$jsCode.="
	var {$jsaction} = function(obj{$this->getClassName()}, handle) {
					";
				else
					$jsCode.="
	this.{$jsaction} = function(obj{$this->getClassName()}, handle) {
					";
				
				$jsCode.="
		dwKernel.debug(\"{$this->getClassName()} {$jsaction}\",obj{$this->getClassName()});
		ctrlObj( { {$this->valSessionId}:thedwSession.getId() ,{$this->action}: encode64(\"{$action}\"), {$this->objValue}: encode64(dwJson.toJSON(obj{$this->getClassName()})) }, handle);
	};
				";
			}
		}
		$jsCode.="
};
the{$this->getClassName()} = new {$this->getClassName()}();
		";
		return $jsCode;
	}
}
?>
