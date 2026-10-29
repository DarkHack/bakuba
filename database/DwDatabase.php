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

namespace bakuba\kernel\database;
// use bakuba\kernel\database\DwDatabase as DwDatabase;

use darkphp\logger\DwLogger as DwLogger;
/**
 * object variable declaration name
 * @author chapuis
 *
 * @package bakuba
 * @subpackage bakuba.objects
 *
 * @author $Author: chapuis $
 * @version $Revision: 136 $
 *
 */
 
 use bakuba\kernel\database\dao\DwCache as DwCache;
 use bakuba\kernel\database\crud\DwCrud as DwCrud;
 
interface iDwDatabase {
	public function tableCreate ( string $tableName ) : bool;
	public function tableCrudCreate( string $tableName, DwCrud $dwCrud ) : bool;
	public function tableCrudRead( string $tableName, DwCrud $dwCrud ) : DwCrud;
	public function tableCrudUpdate( string $tableName, DwCrud $dwCrud ) : bool;
	public function tableCrudDelete( string $tableName, DwCrud $dwCrud ): bool;
	public function tableCrudSearch( string $tableName, string $key, string $value ) : DwCache;// or array
	public function tableDelete ( string $tableName ) : bool;
	public function tableGet ( string $tableName ) : DwCache;
}
class DwDatabase extends dao\aDwDao implements iDwDatabase {
	
	public function __construct( \bakuba\kernel\database\DwDatabaseProcess $dwDatabaseProcess ) {
		parent::__construct( $dwDatabaseProcess );
		$this->log->trace("[".$this->toString()."]");
	}
	private $tables = array();
	
	public function createTable( string $tableName ) : bool {
		$created = false;
		$this->log->trace("[".$this->toString()."]");
		if (! isset( $this->tables[$tableName] ) ) 
			$this->tables[$tableName] = new DwCache( $this->parent );
		return $created;
	}
	public function getTable( string $tableName ) : DwCache {
		return $this->tables[$tableName];
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>
