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
//use  bakuba\kernel\io\processPipes\DwPipeStream as DwPipeStream;

class DwPipeStream extends \bakuba\kernel\io\stream\DwStream {
	public function getDwProcPipe() : DwProcPipe {
		return $this->parent;
	}
	///*
	public function __construct( DwProcPipe $dwProcPipe ,$resource = null ) {
		$this->enableFullTrace();
		parent::__construct( $dwProcPipe ,$resource  );
		$this->log->trace( "." );
	}
	//*/
	public function writeEnd() : bool {
		$isEnd = parent::writeEnd();
		$this->isFullTrace() and $this->log->trace( $isEnd ? "sended ended" : "sended not ended" );
		return $isEnd;
	}
	public function readEnd() : bool {
		$isEnd = parent::readEnd();
		$this->isFullTrace() and $this->log->trace( $isEnd ? "received ended" : "received not ended" );
		return $isEnd;
	}
	protected function readClose() : void {
		parent::readClose();
		$this->firstReceive=true;
	}
	private $isSafe = false;
	public function enableSafe() : void {
		$this->isSafe=true;
	}
	// With perplexity.ai :
	protected function readAction( \bakuba\kernel\io\DwResourceSelect $dwResourceSelect ) : void {
		$this->log->trace( $this->isSafe ? "safe" : "no-safe" );
		if ($this->isSafe) {
			if ( ! $this->isResource() )
				$this->log->warn("read action on 'not an resource'");
			elseif (\feof( $this->getResource() ) ) {
				$this->log->warn("pipe closed, no more read.");
				$this->readClose();
			} else
				$dwResourceSelect->resourceRead($this);
		} else 
			parent::readAction( $dwResourceSelect );
	}
	private $firstReceive = true;
	public function receive( string $data = null ) : int {
		//$this->isFullTrace() and $this->log->trace( "." );
		$received=-1;
		if ( ! $this->isResource() )
			$this->log->warn("'not an resource'");
		elseif ( ! $this->isSafe )
			$received=parent::receive( $data );
		else {
			if ( $this->firstReceive ) {
				$this->firstReceive=false;
				$stream = $this->getResource();
				\stream_set_blocking( $stream ,false );
				$meta = \stream_get_meta_data( $stream );
				$this->isFullTrace() and $this->log->trace( \print_r( $meta ,true ) );
				if ( $meta[ 'blocked' ] == 1 && $meta[ 'eof' ] == '' && $meta[ 'unread_bytes' ] == 0 ) {
					$this->readClose();
					$dwProcPipe = $this->getDwProcPipe();
					$dwProcPipe->getDwResources()->addTimer( 1/10 ,new \bakuba\kernel\objects\DwCallBack( array( &$dwProcPipe ,'asyncLunchRead' ) ) );
					$this->log->warn( "no data received" );
					$received=0;
				} else
					$received=parent::receive( $data );
				
			} else
				$received=parent::receive( $data );
		}
		
		$this->isFullTrace() and $this->log->trace( $received );
		
		return $received;
	}
	/*
	protected $packetSize = 4096;
	protected function readClose( ) {
		$this->dwChronoResource->receiveStop();
		$this->recvEnded = true;
		//$receivedSize = strlen ( $this->recvBuf );
		//floatval(); //intval(); 
		$dataLength = $this->recvCpt;
			
		$this->log->info( $this->logStats( $dataLength ,$this->dwChronoResource->receiveTimeSpend() ) );
		//$this->log->trace($this->recvBuf);
		
	}
	
	public function receive( string $data = null ) : int {
		$this->log->trace( "." );
		$stream = $this->getResource();
		$receiveCpt = \bakuba\kernel\io\DwResource::receive( \fread( $stream, $this->packetSize ) );
		if ( $receiveCpt > 0 ) {
			$this->recvEnded = false;
			if ( \feof( $stream )  ) // || $receiveCpt < $this->packetSize )
				$this->readClose();
			
		} elseif ( $this->recvInactivity > 10 )
			$this->readClose();
		return $receiveCpt;
		
	}
	//*/
	/*
	public function connect( $resource = null ) : bool {
		return parent::connect( $resource );
	}
	public function receive( string $data = null ) : int {
		$this->log->trace( "." );
		$stream = $this->getResource();
		$receiveCpt = parent::receive( \fread( $stream, self::$PACKET_SIZE ) );
		if ( $receiveCpt > 0 ) {
			$this->recvEnded = false;
			if ( \feof( $stream ) || $nbByte < self::$PACKET_SIZE )
				$this->readClose();
			
		} elseif ( $this->recvInactivity > 10 )
			$this->readClose();
		return $receiveCpt;
		
	}
	public function send( ) : bool {
		$this->log->trace( "." );
		$sended=false;
		
		$buf = substr( $this->sendBuf, $this->sendCpt, self::$PACKET_SIZE );
		$len = strlen($buf);
		$nbSended = \fwrite( $this->getResource(), $buf, $len );
		
		//self::$sLog->trace("$nbSended/$len [".$this->getClassName()."/".$this->getInstanceKey()."]\r\n$buf");
		if ( $nbSended === FALSE ) {
			$this->writeClose();
			throw new \Exception( "echec on fwrite" );
		} else {
			$this->sended($nbSended);
			if ( $nbSended < $len )
				throw new \Exception( "echec on fwrite_send $nbSended < $len" );
			$sended=true;
		}
		
		return $sended;
	}
	//*/
	
	///*
	public function __destruct() {
		$this->log->trace( "." );
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
		return $fqcn ?
			parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ 
			: __CLASS__
		;
	}
}
?>
