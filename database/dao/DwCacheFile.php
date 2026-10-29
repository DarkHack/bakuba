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
// use bakuba\kernel\database\dao\DwCacheFile as DwCacheFile;

use bakuba\kernel\database\crud\DwCrud as DwCrud;

use bakuba\kernel\io\stream\file\DwFile as DwFile;
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
class DwCacheFile extends DwCache {
	protected $filePath = null;
	private $folderPath = null;
	public function __construct(
		\bakuba\kernel\database\DwDatabaseProcess $dwDatabaseProcess
		,string $tableName
		,string $fileExt = "dbphp"
		,string $folder = null
	) {
		if ( \is_null( $folder ) ) $folder=\bakuba\kernel\agartha\DwInstall::getBakubaDataDirectory()."/default";
		parent::__construct( $dwDatabaseProcess ,$tableName );
		$this->folderPath = \bakuba\kernel\agartha\DwInstall::getLocalDirectory( $folder );
		
		$this->filePath = "{$this->folderPath}/".\str_replace ( '\\', '-', $tableName );
		$this->filePath .=".{$fileExt}";
		
		//self::$sLog->trace(
		//		"[{$this->filePath}/{$this->getClassName()}]"
		//);
		$this->read();
	}
	/*
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		$this->addtoSerialize("filePath");
	}
	*/
	
	public function write( ) : bool {
		return DwFile::putContent( $this->filePath, $this->serialize() );
	}
	private function read( ) : void {
		//self::$sLog->trace( $this->filePath );
		$fileContent = DwFile::getContent( $this->filePath );
		
		if ( is_null( $fileContent ) )
			$this->write();
		else {
			$this->unserialize( $fileContent );
			$this->isNewFile = false;
		}
		
		//self::$sLog->trace("\r\n[{$this->tableName}] count:".count($this->cache)."\r\n[".$this->getClassName()."]\r\n");
	}
	
	private $isNewFile = true;
	public function isNew() : bool {
		return $this->isNewFile;
	}
	public function add( DwCrud $dwCrud ) : DwCrud {
		$return = parent::add( $dwCrud );
		$this->write();
		return $dwCrud;
	}
	
	public function remove( DwCrud $dwCrud ) : bool {
		$found = parent::remove( $dwCrud );
		$this->write();
		return $found;
	}
	
	public function edit( DwCrud $dwCrud ) : DwCrud {
		$return = parent::edit( $dwCrud );
		$this->write();
		return $return;
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>
