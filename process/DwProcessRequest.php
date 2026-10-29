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
//use bakuba\kernel\process\DwProcessRequest as DwProcessRequest;

class DwProcessRequest extends \darkphp\serializable\aDwSerializable {
	protected $dwProcessOrder = null;
	public function getDwProcessOrder() : DwProcessOrder {
		return $this->dwProcessOrder;
	}
	public function __construct(
		DwProcessOrder $dwProcessOrder
		,\darkphp\DwObject $dwParent = null
	) {
		$this->dwProcessOrder = $dwProcessOrder;
		parent::__construct( $dwParent );
	}
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		$this->addtoSerialize("dwProcessOrder");
	}
	/*
	public function checkLog( $dataLogs ) {
		$return = null;
		if ( is_array( $dataLogs ) ) {
			$return = array();
			foreach($dataLogs as $index => $log )
				$return[$index] = $this->checkLog( $log );
		} else
			$return = preg_replace('/[\'"]/', '_', $dataLogs);
		//str_replace("\"","_",  );
		//preg_replace('/[^A-Za-z0-9\-\.\(\)]/', ' ', $dataLogs);
		return $return;
	}
	private $logs;
	public function setLogs( array $logs ) {
		$this->logs = $this->checkLog($logs);
	}
	public function getLogs() {
		return $this->logs;
	}
	//*/
	/*
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
