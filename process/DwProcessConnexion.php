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
//use bakuba\kernel\process\DwProcessConnexion as DwProcessConnexion;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwProcessConnexion extends \darkphp\DwObject implements iDwProcessConnexion {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,\darkphp\source\DwSource::classNameToTopName( $this->getClassName() )
//				,time()."-".$this->getInstanceKey()
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	private $floatMicroSecondeStart = null;
	private $dwIpcConnexion = null;
	
	public function __construct(
		iDwProcessIpc $dwProcessIpc
		,\bakuba\kernel\io\ipc\DwIpcConnexion $dwIpcConnexion
	) {
		$this->floatMicroSecondeStart = \microtime( true );
		$this->dwIpcConnexion = $dwIpcConnexion;
		parent::__construct( $dwProcessIpc );
		$this->getDwProcessIpc()->addTick( new DwCallBack( array( &$this ,'connect' ) ) );
	}
	public function getDwProcess() : DwProcess {
		return $this->getDwProcessIpc();
	}
	public function getDwProcessIpc() : iDwProcessIpc {
		return $this->parent;
	}
	public function getDwIpcMsg() : \bakuba\kernel\io\ipc\DwIpcMsg {
		return $this->dwIpcConnexion->getDwIpcMsg();
	}
	public function getSrcIpcKey() : \bakuba\kernel\io\ipc\DwIpcKey {
		return $this->dwIpcConnexion->getSrcIpcKey();
	}
	public function connect() : void {
		$dwIpcHandle = $this->getDwProcessIpc()->getDwHandles();
		$dwIpcMsg = $this->getDwIpcMsg();
		$data = $dwIpcMsg->read();
		$mid = $dwIpcMsg->getId();
		$rid = $dwIpcMsg->getRid();
		$debugKey ="mid {$mid}";
		if ( ! empty( $rid ) )
			$debugKey.="/rid {$rid}";
		
		$dstKey = $dwIpcMsg->getDstKey();
		$srcKey = $dwIpcMsg->getSrcKey();
		$dstClassName = $dstKey->getClassNameProcess();
		$srcClassName = $srcKey->getClassNameProcess();
		$debugKey.="/{$srcClassName} => {$dstClassName}";
		//$dstPid = $dstKey->getProcessId();
		//$srcPid = $srcKey->getProcessId();
		$dstKey = $dstKey->getKey();
		$srcKey = $srcKey->getKey();
		
		$dwCallBack = $dwIpcHandle->getResponseHandle( $rid );
		
		$objectUnserialized = null;
		/*
		//$this->log->trace( "[{$debugKey}]\r\n $data" );
		try {
			$objectUnserialized = unserialize($data);
		} catch(\Exception $exception_unserialize) {
			$this->log->throwable($exception_unserialize);
			$objectUnserialized = false;
			//throw $exception_unserialize;
		} catch(\Error $error_unserialize) {
			$this->log->throwable($error_unserialize);
			$objectUnserialized = false;
			//throw $error_unserialize;
		}
		//*/
		$objectUnserialized = $data;
		
		$isAnswer = false;
		if ( is_object( $dwCallBack ) )
			$isAnswer = true;
		
		$fqcn = \gettype($objectUnserialized);
		if ( !$isAnswer && \is_object($objectUnserialized) ) {
			$fqcn = \get_class($objectUnserialized);
			$debugKey.="/fqcn {$fqcn}";
			$dwCallBack = $dwIpcHandle->getClassHandle( $fqcn );
		}
		/*
		$this->log->trace(
			"[{$debugKey}]\r\n"
			."{$srcClassName}($srcKey) =>"
			." {$dstClassName}($dstKey)\r\n"
			." requestClass:$fqcn\r\n"
		);
		//*/
		try {
			if ( \is_null( $dwCallBack ) )
				$this->log->error(
					"[{$debugKey}] no callback for :{$fqcn}\r\n"
				);
			else{
				//$this->log->trace( "\r\n\t $srcClassName => $dstClassName [{$debugKey}]\r\n{$dwCallBack->printHandle()}\r\n" );
				if ( $isAnswer )
					$dwCallBack->callback( $objectUnserialized );
				else
					$dwCallBack->callback(
						$this
						,$objectUnserialized
					);
			}
			//$this->log->trace( "\r\n\t$srcClassName => $dstClassName \r\n\t[{$debugKey}] end \r\n" );
		} catch(\Exception $exception_cb) {
			$this->throwError($exception_cb);
			//throw $exception_cb;
		} catch(\Error $error_cb) {
			$this->throwError($error_cb);
			//throw $error_cb;
		}
	}
	
	public function throwError( $errorHandle ) : void {
		$this->log->throwable( $errorHandle );
		$msgError = null;
		if ( \method_exists( $this->log, "getFullPath") )
			$msgError = $this->getClassName()." ERROR, see filelog:".\PHP_EOL
				.$this->log->getFullPath();
		else
			$msgError = $this->getClassName()." ERROR, see filelog:".\PHP_EOL
				.\darkphp\error\DwException::exceptionToString( $errorHandle );
		
		$this->ipcResponse( new \bakuba\kernel\io\ipc\DwIpcMsgError(
			$this->getDwIpcMsg()
			,$msgError
		) );
	}
	public function ipcResponse(
		$objectToSerialize
		,DwCallBack $onWrited = null
	) : void {
		$this->dwIpcConnexion->msgResponse(
			$objectToSerialize
			,$onWrited
		);
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
