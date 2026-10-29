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

namespace bakuba\kernel\database\dao;
// use bakuba\kernel\database\dao\aDwDao as aDwDao;

/**
 * object variable declaration name
 * @author chapuis
 *
 * @package bakuba
 * @subpackage bakuba.object
 *
 * @author $Author: chapuis $
 * @version $Revision: 136 $
 *
 * @abstract
 */
abstract class aDwDao extends \darkphp\serializable\aDwSerializable implements iDwCache {
	
	protected function __construct( \bakuba\kernel\database\DwDatabaseProcess $dwDatabaseProcess ) {
		parent::__construct( $dwDatabaseProcess );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>