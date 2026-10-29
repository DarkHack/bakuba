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

namespace bakuba\kernel\objects;
//use bakuba\kernel\objects\DwHandles as DwHandles;

class DwHandles extends \darkphp\DwObject {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
//				,\darkphp\source\DwSource::classNameToFirstSectionName( $this->getClassName() )
				,\darkphp\source\DwSource::classNameToTopName( $this->getClassName() )
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	/*
	public function __construct(
		\darkphp\DwObject $parent
	) {
		parent::__construct( $parent );
	}
	*/
	
	private $listCallBack = array();
	public function addResponseHandle( string $msgId ,DwCallBack $dwCallBack ) {
		if ( isset( $this->listCallBack[ $msgId ] ) )
			throw new \Exception( "callback for msgId['{$msgId}'] ever defined." );
		$this->listCallBack[ $msgId ] = $dwCallBack;
	}
	public function getResponseHandle( string $msgId = null ) : ?DwCallBack {
		$dwCallBack=null;
		if ( isset( $this->listCallBack[ $msgId ] ) && \is_object( $this->listCallBack[ $msgId ] ) ) {
			$dwCallBack = $this->listCallBack[ $msgId ];
			unset( $this->listCallBack[ $msgId ] );
		}
		return $dwCallBack;
	}
	private $fqcnCallBackArray = null;
	public function registerHandle(
		string $fullQualifiedClassName
		,DwCallBack $dwCallBack
	) : void {
		if ( isset( $this->fqcnCallBackArray[ $fullQualifiedClassName ] ) )
			throw new \Exception( "callback for fqcn['{$fullQualifiedClassName}'] ever defined." );
		$this->fqcnCallBackArray[ $fullQualifiedClassName ] = $dwCallBack;
	}
	public function getClassHandle( string $fqcn = null ) : ?DwCallBack {
		$dwCallBack = null;
		if ( ! is_null( $fqcn ) && isset( $this->fqcnCallBackArray[ $fqcn ] ) && \is_object( $this->fqcnCallBackArray[ $fqcn ] ) ) 
			$dwCallBack = $this->fqcnCallBackArray[ $fqcn ];
		return $dwCallBack;
	}
	/*
	private $cbHandles = array();
	public function addHandle( DwCallBack $cbHandle ) {
		$this->cbHandles[]=$cbHandle;
	}
	public function removeHandle( DwCallBack $dwCallBack ) : bool {
		foreach( $this->cbHandles as $key => $anDwCallBack ) {
    		if ( $dwCallBack->getInstanceKey() === $anDwCallBack->getInstanceKey() ) {
    			unset( $this->cbHandle[ $key ] );
    			$found = true;
				break;
			}
		}
		$this->log->trace( $found ? "found" : "not found" );
		return $found;
	}
	//*/
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
	}
	//*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
