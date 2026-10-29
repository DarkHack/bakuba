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

namespace bakuba\kernel\application\session;
//use bakuba\kernel\application\session\DwSessionRequest as DwSessionRequest;

class DwSessionRequest extends \darkphp\serializable\aDwSerializable {
	private $order=null;
	private $myobject = null;
	
	private $sessionKey = null;
	public function getSessionKey():string {
		return $this->sessionKey;
	}
	public function __construct(
		string $sessionKey
		,string $order
		,\darkphp\DwObject $dwParent = null
	) {
		$this->sessionKey = $sessionKey;
		$this->order = $order;
		parent::__construct( $dwParent );
	}
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		$this->addtoSerialize("order");
		$this->addtoSerialize("myobject");
		$this->addtoSerialize("ciphers");
	}
	public function getOrder( ) : string {
		return $this->order;
	}
	public function getDwSessionChannel( ) : DwSessionChannel{
		return $this->myobject;
	}
	public function setObject( $myobject ) {
		$this->myobject = $myobject;
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return __CLASS__;
	}
}
?>
