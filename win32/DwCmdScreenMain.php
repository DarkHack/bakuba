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

namespace bakuba\kernel\win32;
//use bakuba\kernel\win32\DwCmdScreenMain as DwCmdScreenMain;

use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\DwObject as DwObject;

// DwCmdScreenMain
class DwCmdScreenMain extends DwSubScreen {
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
	
	// bakuba\kernel\win32\DwCmdScreenMain::singleton( $bakuba, $nbColonnes );
	private static $instance = null;
	public static function singleton( \bakuba\kernel\agartha\DwBakuba $dwBakuba, int $nbColonnes ,int $nbLignes ) {
		null === self::$instance and self::$instance = new self( $dwBakuba ,$nbColonnes ,$nbLignes );
		return self::$instance;
	}
	private $appenders = array();
	public function createAppender() : \darkphp\logger\appenders\aDwAppender {
		$dwCmdScreenMainAppender = new DwCmdScreenMainAppender();
		$loggerLayoutPattern = new \darkphp\logger\layouts\DwLayoutPattern();
		$loggerLayoutPattern->setConversionPattern( "%-5level %relative [%logger] %class->%method()  %msg%newline" );
		$loggerLayoutPattern->activateOptions();
		$dwCmdScreenMainAppender->setLayout( $loggerLayoutPattern );
		$this->appenders[] = $dwCmdScreenMainAppender;
		return $dwCmdScreenMainAppender;
	}
	private $nbLines = null;
	public function getLines() : int {
		return $this->nbLines;
	}
	private $screenCols = array();
	private $screens = array();
	private function __construct( \bakuba\kernel\agartha\DwBakuba $dwBakuba, int $nbColonnes ,int $nbLignes ) {
		$this->nbColonnes = $nbColonnes - 2;
		$this->nbLines = $nbLignes;
		DwObject::__construct( $dwBakuba );
		
		//$nbColScreens = 3;
		//$colonesByScreen = $this->getColonnes() / $nbColScreens;
		$firstScreenSize = 70;
		$this->screenCols[] = new DwSubScreen( $this ,$firstScreenSize ,27 );
		$this->screenCols[] = new DwSubScreen( $this ,$this->getColonnes() - $firstScreenSize  ,27 );
		$this->screenCols[] = new DwSubScreen( $this ,0 ,1 );
		
		$this->screens[ "loggers" ] = new DwScreen( $this->screenCols[0] ,3 );
		//$this->screens[ "loggers-obj" ] = new DwScreen( $this->screenCols[0] ,4 );
		$this->screens[ "objects" ] = new DwScreen( $this->screenCols[0] ,14 );
		$this->screens[ "null" ] = new DwScreen( $this->screenCols[1] ,17 );
		$this->screens[ "datalogs" ] = new DwScreen( $this->screenCols[2] ,5 ); 
		//$this->screenCols[] =$this->screens[ "datalogs" ];
		
		//echo "\r\n TEST STREAM : ".$this->getColonnes()."\r\n";
		$this->cbWriteStdIn = new DwCallBack( array( &$this ,'stdStreamWrite' ) );
		$this->cbSchWriteStdIn = new DwCallBack( array( &$this ,'lunchStdStreamWrite' ) );
		$this->lunchStdStreamWrite();
		$this->log->trace( "." );
	}
	private function refresh() {
		$this->refreshObjects();
		$this->refreshCallbacks();
		//$this->refreshLoggers();
		$logNbLine = $this->screens[ "datalogs" ]->getLines();
		foreach( $this->appenders as $dwCmdScreenMainAppender ) {
			$oldDataToPrint = $this->screens[ "datalogs" ]->getDataToPrint();
			if ( \is_null( $oldDataToPrint ) ) $oldDataToPrint = array();
			$toPrint = $dwCmdScreenMainAppender->readAndPurge();
			$nbToPrint=\count( $toPrint );
			$startLine = $logNbLine  - \count( $oldDataToPrint );
			if ( $nbToPrint > ( $logNbLine - $startLine ) )
				$startLine = 0;
			foreach( $toPrint as $toAddClear ) {
				$toAdd = \preg_replace( '/[^a-zA-Z0-9]/' ,' ' ,$toAddClear );
				$preStartSize = \intval( $this->getColonnes() / 2 ) - 2;
				$lenToAdd = \strlen( $toAdd ) + 1;
				if ( $lenToAdd > $this->getColonnes() )
					$toShow = "-".\substr( $toAdd ,0 ,$preStartSize )."...".\substr( $toAdd, $lenToAdd - ( $this->getColonnes() - ( $preStartSize + 3 ) ) );
				else
					$toShow = "-".$toAdd;
				
				$oldDataToPrint[ $startLine ] = $toShow;
				$startLine++;
			}
			$this->screens[ "datalogs" ]->setDataToPrint( $oldDataToPrint );
		}
		//$this->log->info( "." );
	}
	// Perplexity.ai production :
	private function refreshObjects() {
		// Récupérer toutes les instances
		$instances = DwObject::listInstancesCreated();
		//$this->log->trace( $instances );
		// Filtrer uniquement celles où il y a plus d'une instance
		$filtered = [];
		foreach ($instances as $objName => $objCount) {
			if ($objCount > 1) $filtered[$objName] = $objCount;
		}
		// Trier par $objCount décroissant
		arsort($filtered);
		
		$printObjects = array( 0 => "-Objects-" );
		$cpt=1;
		foreach ($filtered as $objName => $objCount) {
			$idx=$cpt++;
			$strLenRestant = $this->screens["objects"]->getColonnes();
			$strLenRestant -= \strlen($objCount) + 1;
			$strLenObjName = \strlen($objName);
			if ($strLenObjName >= $strLenRestant)
				$printObjects[$idx] = \substr($objName, $strLenObjName - ($strLenRestant - 1)) . ":" . $objCount;
			else
				$printObjects[$idx] = $objName . ":" . $objCount;
		}
		$this->screens["objects"]->setDataToPrint($printObjects);
	}
	private $cbCount = 0;
	// Perplexity.ai production :
	private function refreshCallbacks() {
		$instances = DwCallBack::listInstancesCreated();
		if ( ( ($this->cbCount++) % 15 ) == 0 )
			$this->log->trace( $instances );
		$filtered = [];
		foreach ($instances as $objName => $objCount) {
			if ($objCount >= 1) $filtered[$objName] = $objCount;
		}
		arsort($filtered);
		
		$printObjects = array( 0 => "-Callback-" );
		$cpt=1;
		foreach ($filtered as $objName => $objCount) {
			$idx=$cpt++;
			$strLenRestant = $this->screens["null"]->getColonnes();
			$strLenRestant -= \strlen($objCount) + 1;
			$strLenObjName = \strlen($objName);
			if ($strLenObjName >= $strLenRestant)
				$printObjects[$idx] = \substr($objName, $strLenObjName - ($strLenRestant - 1)) . ":" . $objCount;
			else
				$printObjects[$idx] = $objName . ":" . $objCount;
		}
		$this->screens["null"]->setDataToPrint($printObjects);
	}

	private function refreshLoggers() {
		$printObjects = array( 0 => "-Loggers-" );
		$cpt=1;
		foreach( \darkphp\logger\aDwLogger::listInstancesCreated() as $objName => $objCount ) {
			$idx=$cpt++;
			$strLenRestant = $this->screens[ "loggers" ]->getColonnes();
			$strLenRestant-=\strlen( $objCount ) + 1;
			$strLenObjName = \strlen( $objName );
			if ( $strLenObjName >= $strLenRestant )
				$printObjects[ $idx ]=\substr( $objName, $strLenObjName - ( $strLenRestant - 1 ) ).":".$objCount;
			else
				$printObjects[ $idx ]=$objName.":".$objCount;
		}
		$this->screens[ "loggers" ]->setDataToPrint( $printObjects );
		$printObjects = array( 0 => "-Loggers Obj-" );
		
		//$this->log->info( \darkphp\logger\aDwLogger::getLastKeys( 10 ) );
		foreach(
			\darkphp\logger\aDwLogger::getLastKeys( 10 )
			as $keyToPrint
		) {
			$strLenRestant = $this->screens[ "loggers-obj" ]->getColonnes();
			$idx=$cpt++;
			$strLen = \strlen( $keyToPrint );
			if ( $strLen >= $strLenRestant )
				$printObjects[ $idx ]=\substr( $keyToPrint, $strLen - ( $strLenRestant - 1 ) );
			else
				$printObjects[ $idx ]=$keyToPrint;
		}
		$this->screens[ "loggers-obj" ]->setDataToPrint( $printObjects );
		
	}
	public function lunchStdStreamWrite( ) {
		//$dwTimerWriteStdIn =
		$this->parent->addTimer( 3 , $this->cbWriteStdIn );
	}
	private $cbWriteStdIn = null;
	private $cbSchWriteStdIn = null;
	private $toPrint = null;
	private $screenCpt = 0;
	public function stdStreamWrite() {
		$fp = \fopen( "php://stdout", "w");
		if ( $fp === false )
			throw new \Exception(" echec on fopen:php://stdout");
		else {
			$this->refresh();
			//
			$this->screenCpt++;
			$dataToPrint = array(
				0 =>" ========= BAKUBA Tick {$this->screenCpt} :".\date("H:i:s u")." ========== " 
				,$this->getLines()-1 => " Press Enter if figate! "
			);
			//$arrStr = explode( \PHP_EOL, DwObject::print_r(  ) );
			$first=true;
			foreach( $this->screenCols as $screenCol ) {
				$cpt=1;
				foreach( $screenCol->print() as $toPrint ) {
					$idx = $cpt++;
					if ( ( $idx + 1 ) >= $this->getLines() )
						break;
					if ( isset( $dataToPrint[ $idx ] ) )
						$dataToPrint[ $idx ].=$toPrint;
					else
						$dataToPrint[ $idx ]=$toPrint;
				}
				$first=false;
			}
			/*
			$cpt=20;
			foreach( $this->screens[ "datalogs" ]->print() as $toPrint ) {
				$idx = $cpt++;
				if ( ( $idx + 1 ) >= $this->getLines() )
					break;
				$dataToPrint[ $idx ]=$toPrint;
			}
			//*/
			
			$dwResources = $this->parent->getDwResources();
			$streamSelect = $dwResources->getStreamSelect();
			$dwStream = new \bakuba\kernel\io\stream\DwStream( $this ,$fp );
			$totalData="";
			for ( $idx=0 ;$idx<$this->getLines() ;$idx++ ) {
				$restant = $this->getColonnes() + 1;
				//$strLayout="_";
				$strLayout="z" && $restant--;
				if ( isset( $dataToPrint[$idx] ) ) {
					$strToShow = $dataToPrint[$idx];
					$subStrLen = \strlen( $strToShow );
					if ( $subStrLen >= $restant )
						$subStr = \substr( $strToShow ,0 ,$restant );
					else
						$subStr = $strToShow;
					
					if ( $idx==0 || ( $idx + 1 )==$this->getLines() ) {
						if ( \strlen( $subStr ) % 2 > 0 )
							$subStr .="_";
						$restant-=\strlen( $subStr );
						$toLoad=\intval( $restant / 2 );
						if ( $toLoad > 0 )
							$strLayout.= \str_repeat( 'c', $toLoad ).$subStr. \str_repeat( 'c', $toLoad );
						else
							$strLayout.=$subStr;
					} else 
						$strLayout.=$subStr;
				}
				
				$restant = ( ( $this->getColonnes() + 1 ) - \strlen( $strLayout ) );
				if ( $restant>=1 )
					$strLayout.=\str_repeat( $idx==0 || $idx==$this->getLines() ?'a' : 'b', $restant );
				
				$strLayout.="T";
				$totalData.=$strLayout;
			}
			//$this->log->info( $totalData );
			//$totalData.=\str_repeat( '-', $this->getColonnes() );
			$dwStream->write( "{$totalData}\r" );
			
			//$dwStream->withoutClose();
			$dwStream->setOnWrited( $this->cbSchWriteStdIn );//,$dwCallBack->getClone( $dwCallBack )  );
			$streamSelect->streamWrite( $dwStream );
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
		return $fqcn ?
			parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ 
			: __CLASS__
		;
	}
}
?>
