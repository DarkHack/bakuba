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

namespace bakuba\kernel\dwController\abstracts;

use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\web\DwWebAction as DwWebAction;
use darkphp\web\DwWebValue64 as DwWebValue64;

// ACtrlPage
abstract class ACtrlPage extends ACtrlLoginable {
	
	private $log;
	public function __construct( $className = null, $canonicalName = null ) {
		$this->log = DwLogger::getLogger(__CLASS__);
		try {
			$this->log->trace(".");
			parent::__construct( $className==null?__CLASS__:$className, $canonicalName==null?__CLASS__:__CLASS__."_".$canonicalName);
			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	public function initSession( $server, $get, $post ) {
		global $dwKernel;
		try {
			$this->log->trace(".");			
			parent::initSession( $server, $get, $post );
			$sessionData = $this->getSession();
			if ( ! isset ( $sessionData->authRequestId ) )
				$sessionData->authRequestId = $dwKernel->createAuthRequest( $this, $server )["id"];
			/*
			$dwWebUrl = $this->getDwWebUrl( $server );
			$dwWebUrl->setHost( "auth.darkweb.fr" );
			$dwWebUrl->setBaseUri( "/Welcome.json" );
			$dwWebValue = new DwWebValue64(	"session_key" );
			$dwWebValue->setValue( $this->valSessionId->getKey() );
			$dwWebUrl->set( $dwWebValue, $dwWebValue->getValueEncoded() );
			$dwWebValue = new DwWebValue64(	"session_id" );
			$dwWebValue->setValue( $this->valSessionId->getValue() );
			$dwWebUrl->set( $dwWebValue, $dwWebValue->getValueEncoded() );
			$sessionData->jsonAuthDarkWeb = $this->get_web_page( $dwWebUrl );
			//*/
			//$sessionData->jsonAuthDarkWeb = ;
			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	public function getAuthRequest() {
		global $dwKernel;
		$sessionData = $this->getSession();
		return $dwKernel->getAuthRequest( $sessionData->authRequestId );
	}
	/*
	public function execute( $server ,$get, $post ) {
		$response="";
		try {
			$this->log->info(".");
			$response = parent::execute( $server ,$get, $post );
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $response;
	}
	
	public function action( DwWebAction $dwWebAction, $server, $get, $post ) {
		$response="";
		try {
			$this->log->info(".");
			$response = parent::action( $dwWebAction, $server, $get, $post );
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $response;
	}
	//*/
}
?>
