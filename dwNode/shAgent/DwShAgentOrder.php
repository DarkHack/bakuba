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

namespace bakuba\kernel\dwNode\shAgent;
//use bakuba\kernel\dwNode\shAgent\DwShAgentOrder as DwShAgentOrder;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwShAgentOrder extends \bakuba\kernel\objects\serialize\DwJson {
	
	public static function create( DwCallBack $dwCallBack ,string $order ,bool $useShAgentExecutePipe = true ) {
		return new self( $dwCallBack ,$order ,$useShAgentExecutePipe );
	}
	private $dwCallBack = null;
	private function __construct( DwCallBack $dwCallBack ,string $order ,bool $useShAgentExecutePipe = true ) {
		
		$this->__set("orderid" ,null);
		$this->__set("order" ,$order);
		$this->__set("shAgentExecutePipe" ,$useShAgentExecutePipe);
		
		$this->dwCallBack=$dwCallBack;
	}
	
	private function getDirLog() : string {
		//return LoggerAppenderFilePid::getDirLog()."/shAgent";
		return "/var/log/bakuba/shAgent";
	}
	
	public function execute( string $dataResponse ) : void {
		$dirLog = $this->getDirLog();
		if ( ! is_dir($dirLog) ) mkdir($dirLog,0777,true);
		file_put_contents( "$dirLog/".date("YmdGis").".log",
			date("F j, Y, g:i a")."\r\n========================\r\n".$this->{"order"}
			."\r\n========================\r\n".$dataResponse
			."\r\n========================\r\n"
		);
		if ( is_object( $this->dwCallBack ) )
			$this->dwCallBack->callback( $dataResponse );
		
	}
	public function useShAgentExecutePipe() : bool {
		return $this->{"shAgentExecutePipe"};
	}
	public function setOrderId(string $orderId) : void {
		$this->__set("orderid" ,$orderId);
	}
	public function getOrderId() : string {
		return $this->{"orderid"};
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>
