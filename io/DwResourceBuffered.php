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

namespace bakuba\kernel\io;
//use bakuba\kernel\io\DwResourceBuffered as DwResourceBuffered;

use darkphp\DwObject as DwObject;
//use bakuba\kernel\time\DwChrono as DwChrono;

class DwResourceBuffered extends DwResource implements iDwResourceBuffered {
	//protected $packetSize = 1024;
	protected $packetRcvSize = 0;
	public function getRcvSize() : int {
		return $this->packetRcvSize;
	}
	protected $packetSndSize = 0;
	
	private $dwChronoResource = null;
	public function getDwChronoResource() : DwChronoResource {
		return $this->dwChronoResource;
	}
	public function __construct( DwObject $parent ,$resource = null ) {
		$this->dwChronoResource = new DwChronoResource();
		parent::__construct( $parent ,$resource );
	}
	
	public function forward(  ) : void {
		$this->write( $this->recvBuf );
		$this->recvBuf = null;
	}
	protected $recvBuf = null;
	protected $recvCpt = null;
	protected $recvEnded = false;
	protected $recvInactivity = 0;
	
	public function clearRecvBuf() : void {
		$this->recvBuf = "";
		$this->recvCpt = 0;
		$this->dwChronoResource->receiveStart();
		$this->recvEnded = false;
		$this->recvInactivity = 0;
	}
	public function receive( string $data = null ) : int {
		$this->recvInactivity++;
		$deltaRecv=0;
		if ( is_string( $data ) ) {
			if ( is_null( $this->recvBuf ) )
				$this->clearRecvBuf();
			$deltaRecv = \strlen( $data );
			
			//$this->log->trace( " {$deltaRecv} + ".strlen( $this->recvBuf ) );
			if ( $deltaRecv > 0 ) {
				//$this->log->trace( " {$deltaRecv} + ".strlen( $this->recvBuf ).":\r\n{$data}" );
				$this->flagActivity = true;
				$this->recvCpt+=$deltaRecv;
				$this->recvBuf.=$data;
				$this->recvInactivity=0;
			}
		}
		$this->dwChronoResource->receive( $deltaRecv );
		return $deltaRecv;
	}
	public function getRecvCpt() :int {
		return $this->recvCpt;
	}
	public function getRecvInactivity() : int {
		return $this->recvInactivity;
	}
	public function read( ) : ?string {
		return $this->recvBuf;
	}
	public function readReady() : bool {
		return ! is_null( $this->recvBuf ) && strlen( $this->recvBuf ) > 0;
	}
	public function readEnd() : bool {
		return $this->recvEnded;
	}
	protected function readClose( ) : void {
		$this->dwChronoResource->receiveStop();
		$this->recvEnded = true;
		//$receivedSize = strlen ( $this->recvBuf );
		//floatval(); //intval(); 
		$dataLength = $this->recvCpt;
			
		//$this->log->info( $this->logStats( $dataLength ,$this->dwChronoResource->receiveTimeSpend() ) );
		//$this->log->trace($this->recvBuf);
		
	}
	
	protected $sendBuf = null;
	protected $sendCpt = 0;
	protected $sendEnded = false;
	protected $sendCptToSend = -1;
	
	public function write( string $response ) :void {
		//$this->log->trace("[{$debugKey}]");
		$this->sendBuf = $response;
		$this->sendEnded = false;
		$this->sendCptToSend = strlen($this->sendBuf);
		$this->sendCpt = 0;
		$this->dwChronoResource->emetStart();
		
		//$this->log->trace( "buf size:{$this->sendCptToSend} o " );
	}
	public function writeEnd() : bool {
		return $this->sendEnded;
	}
	protected function writeClose( ) {
		$this->dwChronoResource->emetStop();
		$this->sendEnded = true;
		unset($this->sendBuf);
		$this->sendBuf = null;
		//floatval(); //intval();
		
		$dataLength = $this->sendCpt;
		
		//$this->log->info( $this->logStats( $dataLength ,$this->dwChronoResource->emetTimeSpend() ) );
		
	}
	protected function sended( int $nbSended ) {
		//$this->log->trace( "{$nbSended} + {$this->sendCpt} / {$this->sendCptToSend}" );
			
		$this->sendCpt += $nbSended;
		$this->flagActivity = true;
		
		if ( $this->sendCpt >= $this->sendCptToSend )
			$this->writeClose();
	}
	private $flagActivity = false;
	public function checkActivity() :bool {
		$activity = $this->flagActivity;$this->flagActivity=false;
		return $activity;
	}
	
	protected function logStats( int $dataLength ,float $timeSpend ) : string {
		$speedRatio = 0;
		if ( $timeSpend > 0 )
			$speedRatio = $dataLength / $timeSpend;
		
		$dataDem="o";
		if ( $dataLength > ( 1000 * 1000 ) ) {
			$dataLength = $dataLength / ( 1000 * 1000 );
			$dataDem="Mo";
		} elseif ( $dataLength > 1000 ) {
			$dataLength = $dataLength / ( 1000 );
			$dataDem="Ko";
		}
		$msgStat = "size:$dataLength $dataDem |";
		
		$timeDem="s";
		if ( $timeSpend < 0.001 ) {
			$timeSpend = $timeSpend * ( 1000 * 1000 );
			$timeDem="us";
		}elseif ( $timeSpend < 0.01 ) {
			$timeSpend = $timeSpend * ( 1000 );
			$timeDem="ms";
		}
		$msgStat .= sprintf('%f', $timeSpend)." $timeDem\r\n";
		
		$speedDem="o";
		if ( $speedRatio > ( 1000 * 1000 ) ) {
			$speedRatio = $speedRatio / ( 1000 * 1000 );
			$speedDem="Mo";
		} elseif ( $speedRatio > 1000 ) {
			$speedRatio = $speedRatio / 1000;
			$speedDem="Ko";
		}
		$msgStat .= sprintf('%.3f', $speedRatio)." $speedDem/s";
		//$msgStat .= "";
		return $msgStat;
	}
	///*
	public function __destruct( ) {
		if ( isset( $this->log ) && \is_object( $this->log ) ) {
			$dwChronoResource = $this->getDwChronoResource();
			if ( \is_object( $dwChronoResource ) ) {
				if ( $this->recvCpt > 0 )
					$this->log->info( $this->logStats( $this->recvCpt ,$dwChronoResource->receiveTimeSpend() ) );
				if ( $this->sendCpt > 0 )
					$this->log->info( $this->logStats( $this->sendCpt ,$dwChronoResource->emetTimeSpend() ) );
			}
		}
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
