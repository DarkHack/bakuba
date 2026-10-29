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

namespace bakuba\kernel\process;
//use bakuba\kernel\process\DwProcess as DwProcess;

use \Exception as Exception;
use \Error as Error;

//use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\objects\timer\DwTimer as DwTimer;
class DwMainProcess extends DwProcessManager {
	protected function __construct(	self $parentProcess ) {
		parent::__construct( $parentProcess );
		//$this->log->trace("end");
	}
	protected function init(): void {
		parent::init( );
		
		$processClassName =  str_replace ( '\\' ,'.' ,$this->getClassName() );
		$processTitle = "php-{$processClassName}";
		/*
		$firstPos = strpos( $processClassName ,'.' );
		$headName = substr( $processClassName ,0 ,$firstPos );
		if ( $headName == "bakuba" )
			$headName .= "-".substr(
				$processClassName
				,$firstPos+1
				,strpos( $processClassName ,'.' ,$firstPos+1 )
			);
		$footName = substr(
			$processClassName
			,strrpos( $processClassName ,'.' ) + 1
		);
		$processTitle = "php-{$headName}_{$footName}-".$this->cptProcRun;
		//*/
		$this->setProcessTitle( $processTitle );
		$this->setPid( \getmypid() );
		$this->log->trace(".");
	}
	
	
	protected $pid = null;
	public function getPid( ) : int {
		return $this->pid;
	}
	protected function setPid( int $myPid ) : void {
		$this->pid = $myPid;
	}
	private $dwCallBackCheckTitle = null;
	public function setProcessTitle( string $processTitle ) {
		if ( \function_exists( 'cli_set_process_title' ) ) {
			if ( \cli_set_process_title( $processTitle ) )
				$this->checkProcessTitle( $processTitle );
			else {
				if ( \is_object( $this->dwCallBackCheckTitle ) )
					$this->cancelCallBack( $this->dwCallBackCheckTitle );
				$this->dwCallBackCheckTitle = new DwCallBack(
					array( &$this ,"checkProcessTitle" )
					,$processTitle
				);
				$this->addTick( $this->dwCallBackCheckTitle );
			}
		}
	}
	public function checkProcessTitle( string $processTitle ) {
		if ( \function_exists( 'cli_get_process_title' ) ) {
			if ( \cli_get_process_title() != $processTitle ) 
				$this->setProcessTitle( $processTitle );
		}
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	//*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>
