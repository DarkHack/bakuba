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

namespace bakuba\kernel\application\service\http\controller;
//use bakuba\kernel\application\service\http\controller\DwControllerIpc as DwControllerIpc;

//use bakuba\kernel\application\service\http\controller\DwControllerConnexion as DwControllerConnexion;
use bakuba\kernel\application\session\DwSessionControllerConnexion as DwSessionControllerConnexion;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

interface iDwControllerIpc {
//	public function actionLogged();
}

// DwControllerIpc
abstract class aDwControllerIpc extends aDwController implements iDwControllerIpc {
	
	public function connect( \bakuba\kernel\application\service\http\controller\DwApplicationControllerConnexion $dwApplicationControllerConnexion ) : DwControllerConnexion {
		return new DwControllerIpcConnexion( $dwApplicationControllerConnexion ,$this );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>
