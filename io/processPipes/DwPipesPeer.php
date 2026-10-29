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

namespace bakuba\kernel\io\processPipes;
//use bakuba\kernel\io\processPipes\DwPipesPeer as DwPipesPeer;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\process\DwProcess as DwProcess;
use bakuba\kernel\autoload\DwAutoLoad as DwAutoLoad;

use bakuba\kernel\process\DwProcessResources as DwProcessResources;

use bakuba\kernel\io\stream\DwStream as DwStream;

class DwPipesPeer extends \darkphp\DwObject {
	private $dwInPipeProcess = null;
	private $dwOutStdPipeProcess = null;
	private $dwOutErrPipeProcess = null;
	
	private function getDwProcPipeSelect() : \bakuba\kernel\io\stream\DwStreamSelect {
		//return $this->getDwResources()->getStreamSelect();
		return $this->myStreamSelect;
	}
	private function getDwResources() : \bakuba\kernel\process\DwResources {
		return $this->getDwProcPipe()->getDwResources();
	}
	private function getDwProcPipe() : DwProcPipe {
		return $this->parent;
	}
	private $myStreamSelect = null;
	public function __construct(
		DwProcPipe $dwProcPipe
		,DwPipeInStream $dwInPipeProcess
		,DwPipeOutStdStream $dwOutStdPipeProcess
		,DwPipeOutErrStream $dwOutErrPipeProcess = null
	) {
		parent::__construct( $dwProcPipe );
		$this->myStreamSelect = new DwProcPipeSelect( $this->getDwResources() ,$dwProcPipe );
		$this->myStreamSelect->setDebug( $this->log );
		$this->dwInPipeProcess=$dwInPipeProcess;
		$this->dwInPipeProcess->withoutClose();
		$this->dwInPipeProcess->setOnWrited( new DwCallBack( array( &$this, 'pipeInWrited' ) ) );
		
		$this->dwOutStdPipeProcess=$dwOutStdPipeProcess;
		$this->dwOutStdPipeProcess->withoutClose();
		$this->dwOutStdPipeProcess->setOnReaded( new DwCallBack( array( &$this, "pipeOutReaded" ) ) );
		
		if ( is_object( $dwOutErrPipeProcess ) ) {
			$this->dwOutErrPipeProcess=$dwOutErrPipeProcess;
			$this->dwOutErrPipeProcess->withoutClose();
			$this->dwOutErrPipeProcess->setOnReaded( new DwCallBack( array( &$this, "pipeErrReaded" ) ) );
		}
	}
	private $onPipeInWrited = array();
	public function setOnPipeInWrited( DwCallBack $dwCallBack ) {
		$this->onPipeInWrited[] = $dwCallBack;
	}
	// Perplexity.ai Production :
	public function pipeInWrited() : void {
		//$this->log->trace( "." );
		if ( $this->dwInPipeProcess->isResource() )
			\fflush( $this->dwInPipeProcess->getResource() );
		foreach( $this->onPipeInWrited as $dwCallBack )
			$this->getDwProcPipeSelect()->getProcDwResources()->addTick( $dwCallBack );
	}
	public function writeToStd( string $data ) : void {
		//$this->log->trace( $data );
		$this->dwInPipeProcess->write( $data );
	}
	// Perplexity production :
	public function lunchWrite() : void {
		$this->log->trace( "." );
		if ( $this->stdWritting() )
			$this->log->error("ever wrtting");
		elseif ( ! $this->dwInPipeProcess->isResource() )
			$this->log->warn( "Std In resource invalide" );
		else
			$this->getDwProcPipeSelect()->streamWrite( $this->dwInPipeProcess );
	}
	public function stdWritting() : bool {
		return $this->getDwProcPipeSelect()->resourceIsWriting( $this->dwInPipeProcess );
	}
	
	private $onPipeOutReaded = array();
	public function setOnPipeOutReaded( DwCallBack $dwCallBack ) {
		$this->onPipeOutReaded[] = $dwCallBack;
	}
	private $cptEmpty=0;
	public function lunchRead() : void {
		$this->log->trace( "." );
		if ( $this->stdReading() )
			$this->log->warn( "ever reading" );
		elseif ( ! $this->dwOutStdPipeProcess->isResource() ) 
			$this->log->warn( "Std Out resource invalide" );
		else {
			$this->dwOutStdPipeProcess->clearRecvBuf();
			$this->getDwProcPipeSelect()->streamRead( $this->dwOutStdPipeProcess );
		}
		$this->log->trace( ". ended" );
	}
	protected function stdReading() : bool {
		return $this->getDwProcPipeSelect()->resourceIsReading( $this->dwOutStdPipeProcess );
	}
	public function pipeOutReaded() : void {
		$this->log->trace( "." );
		$data = $this->dwOutStdPipeProcess->read();
		if ( empty( $data ) ) {
			$this->cptEmpty++;
			$this->log->warn("Empty read");
		} else {
			$this->cptEmpty=0;
			if ( $this->dwOutStdPipeProcess->isResource() )
				\fflush( $this->dwOutStdPipeProcess->getResource() );
			$this->log->warn( strlen( $data) );
			$this->log->trace( $data );
		}
		foreach( $this->onPipeOutReaded as $dwCallBack )
			$this->getDwProcPipeSelect()->getProcDwResources()->addTick( $dwCallBack->getClone( $data ) );
	}
	
	private $onPipeErrReaded = array();
	public function setOnPipeErrReaded( DwCallBack $dwCallBack ) {
		$this->onPipeErrReaded[] = $dwCallBack;
	}
	public function lunchReadErr() : void {
		if ( is_null( $this->dwOutErrPipeProcess ) ) 
			$this->log->warn("no pipe read err.");
		else {
			if (  $this->stdErrReading() )
				$this->log->warn( "ever reading" );
			elseif ( ! $this->dwOutStdPipeProcess->isResource() ) 
				$this->log->warn( "Std Out Err resource invalide" );
			else {
				$this->log->trace("lunch read err.");
				$this->dwOutErrPipeProcess->clearRecvBuf();
				$this->getDwProcPipeSelect()->streamRead( $this->dwOutErrPipeProcess );
			}
		}
	}
	protected function stdErrReading() : bool {
		return is_object( $this->dwOutErrPipeProcess ) && $this->getDwProcPipeSelect()->resourceIsReading( $this->dwOutErrPipeProcess );
	}
	public function pipeErrReaded() : void {
		//$this->log->trace( "." );
		$data = $this->dwOutErrPipeProcess->read();
		foreach( $this->onPipeErrReaded as $dwCallBack )
			$this->getDwProcPipeSelect()->getProcDwResources()->addTick( $dwCallBack->getClone( $data ) );
	}
	public function stop( ) {
		$this->log->trace(".");
		if ( $this->stdWritting() )
			$this->dwInPipeProcess->writeCancel( $this->getDwProcPipeSelect() );
		if ( $this->stdReading() )
			$this->dwOutStdPipeProcess->readCancel( $this->getDwProcPipeSelect() );
		
		$this->dwInPipeProcess->disconnect();
		$this->dwOutStdPipeProcess->disconnect();
		if ( isset( $this->dwOutErrPipeProcess ) && \is_object( $this->dwOutErrPipeProcess ) ) {
			if ( $this->stdErrReading() )
				$this->dwOutErrPipeProcess->readCancel( $this->getDwProcPipeSelect() );
			$this->dwOutErrPipeProcess->disconnect();
		}
	}
	public function shutdown() : void {
		$this->dwInPipeProcess->withFlush();
		$this->dwOutStdPipeProcess->withFlush();
		
		if ( $this->dwInPipeProcess->isResource() )
			\fflush( $this->dwInPipeProcess->getResource() );
		if ( $this->dwOutStdPipeProcess->isResource() )
			\fflush( $this->dwOutStdPipeProcess->getResource() );
		
		$this->dwInPipeProcess->withClose();
		$this->dwOutStdPipeProcess->withClose();
		
		if ( \is_object( $this->dwOutErrPipeProcess ) ) {
			$this->dwOutErrPipeProcess->withFlush();
			if ( $this->dwOutErrPipeProcess->isResource() )
				\fflush( $this->dwOutErrPipeProcess->getResource() );
			$this->dwOutErrPipeProcess->withClose();
		}
	}
	public function onExec( DwCallBack $dwCallBack ) : void {
			$this->dwCallBack = $dwCallBack;
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
