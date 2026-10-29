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
// use bakuba\kernel\database\dao\iDwCache as iDwCache;

use bakuba\kernel\database\crud\DwCrud as DwCrud;

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
interface iDwCache {
	
	public function add( DwCrud $dwCrud ) : DwCrud;
	public function remove( DwCrud $dwCrud ) : bool;
	public function edit( DwCrud $dwCrud ) : DwCrud;
	
	//public function getObject( int $_id ) : DwCrud;
	public function getObjects( string $key, string $value ) : array;
	
	//public function getNewId( ) : int;
	//public function check_newId( int $_id ) : bool;
	
	public function isNew() : bool;
	public function crudLink( string $keyLinked ,DwCache $tableSrcLink ) : bool;
}
