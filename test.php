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

define( "logByEcho" ,1 );
require_once \dirname(__FILE__)."/autoload/standalone/startFile.php";
/*
if ( defined("logByEcho") ) {
	require_once "{$bakubaFolder}/kernel/logger/log4dw/test/standalone.php";
	echo ".\r\n----------------- start\r\n";
	//include \dirname(__FILE__)."/serializable/test/testStandalone.php";
}
//*/

if ( \bakuba\kernel\agartha\DwInstall::isWindows() )
	$folderLog="/var/log";
else
	$folderLog="./bakubaLog";
//$dwLogger = \bakuba\kernel\autoLoad\DwAutoLoad::getLogger( new class {} );
//$dwLogger = \bakuba\kernel\logger\standalone\DwLoggerEcho::getFirstLogger( new class {} , "main");
$autoLoadLogger = \bakuba\kernel\logger\standalone\DwLoggerFile::switchFirstLogger( new class {}  ,$folderLog ,"bakubaTest" )->upTo(
	new class{},"bakubaTest","booting"
);
\bakuba\kernel\autoLoad\DwAutoLoad::switchLogger( $autoLoadLogger );
$dwLogger = \bakuba\kernel\autoLoad\DwAutoLoad::getLogger( new class {} );
\bakuba\kernel\agartha\DwInstall::setLogger( $dwLogger );

//$dwLogger = $autoLoadLogger->upTo( new class {} ,"bakubaTest","booting" );
//$dwLogger = \darkphp\logger\DwLoggerFile::getFirstLogger( new class {} ,"/var/log" ,"bakubaTest.log" );
$dwLogger->info( \darkphp\DwObject::listInstancesCreated() );
$dwLogger->info( \darkphp\logger\aDwLogger::listInstancesCreated() );
if ( true ) {
	$dwLogger->trace( "create bakuba/kernel/testUnits()" );
	$timeTestUnits = new class( $dwLogger ) extends bakuba\kernel\testUnits {
		function __construct( $dwLogger ) {
			$this->setDebug( $dwLogger );//\darkphp\logger\DwLoggerFile::getFirstLogger( $this ,"/var/log" ,"bakubaTest.log" ) );
			parent::__construct( $this );
		}
	};
	$dwLogger->trace( "========= executesTests") ;
	$timeTestUnits->lunchTests();
}
//sleep( 10 );
$dwLogger->trace( "close test" );

$dwLogger->info( \darkphp\DwObject::listInstancesCreated() );
$dwLogger->info( \darkphp\logger\aDwLogger::listInstancesCreated() );
//*/
?>
