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

namespace bakuba\kernel\application;
//use bakuba\kernel\application\aDwApplicationTimeout as aDwApplicationTimeout;

use bakuba\kernel\objects\DwCallBack as DwCallBack;
//use bakuba\kernel\objects\timer\DwTimer as DwTimer;

abstract class aDwApplicationTimeout extends aDwApplication implements iDwApplication {
	private $timeoutToCheck = 10;
	private $timeoutCpt = 0;
	
	private $dwCallBackTimeout = null;
	private $dwCallBackActivity = null;
	protected function __construct(
		aDwApplication $parent
	) {
		parent::__construct(
			$parent
			,$parent->getUrlApp()
		);
		$this->log->trace("[{$this->getClassName()}/{$this->getInstanceKey()}]");
	}
	//public function getUrlApp() : string {
	//	return $this->parent->getUrlApp();
	//}
	
	private $dwTimer=null;
	protected function init( ): void {
		parent::init();
		$this->dwCallBackTimeout = new DwCallBack( array( &$this ,"timeout" ) );
		$this->dwCallBackActivity = new DwCallBack( array( &$this ,"activity" ) );
		// default every 10s
		$this->dwTimer = $this->addPeriodicTimer( $this->timeoutToCheck, $this->dwCallBackTimeout );
		$this->ipcAddHandle( $this->dwCallBackActivity );
		$this->log->trace("[{$this->getClassName()}/{$this->getInstanceKey()}]");
	}
	public function setTimeout( $timeoutToCheck ): void {
		$this->timeoutToCheck = $timeoutToCheck;
		if ( \is_object( $this->dwTimer ) ) {
			$this->cancelTimer( $this->dwTimer );
			$this->dwTimer = $this->addPeriodicTimer( $this->timeoutToCheck, $this->dwCallBackTimeout );
		}
	}
	public function timeout( ): void {
		$this->log->trace( "[{$this->getClassName()}/{$this->getInstanceKey()}] timeout cpt:".$this->timeoutCpt );
		if ( ! $this->checkActivity() ) {
			$this->timeoutCpt++;
			if ( $this->timeoutCpt > 3 )
				$this->stop();
		}
	}
	private $msgActivity = false;
	private function checkActivity() : bool {
		$lastState = $this->msgActivity;
		$this->msgActivity=false;
		return $lastState;
	}
	public function activity( \bakuba\kernel\io\ipc\DwIpcMsg $dwIpcMsg ) : void {
		$this->log->info( "." );
		$this->msgActivity=true;
	}
	public function __destruct() {
		$this->log->trace("[{$this->getClassName()}/{$this->getInstanceKey()}]");
		if ( \is_object( $this->dwTimer ) ) {
			$this->cancelTimer( $this->dwTimer );
			unset( $this->dwTimer );
		}
		//unset( $this->dwTimer );
		if ( \is_object( $this->dwCallBackActivity ) ) {
			$this->ipcRemoveHandle( $this->dwCallBackActivity );
			unset( $this->dwCallBackActivity );
		}
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>
