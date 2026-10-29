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
//use bakuba\kernel\win32\DwCmdScreenMainAppender as DwCmdScreenMainAppender;

/**
 * DwAppenderFile appends log events to a file.
 *
 * This appender uses a layout.
 * 
 * ## Configurable parameters: ##
 * 
 * - **file** - Path to the target file. Relative paths are resolved based on 
 *     the working directory.
 * - **append** - If set to true, the appender will append to the file, 
 *     otherwise the file contents will be overwritten.
 *
 * @version $Revision: 2008 $
 * @package logger
 * @subpackage appenders
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link http://logging.apache.org/log4php/docs/appenders/file.html Appender documentation
 */
class DwCmdScreenMainAppender extends \darkphp\logger\appenders\aDwAppender {
	/**
	 * If set to true, the file is locked before appending. This allows 
	 * concurrent access. However, appending without locking is faster so
	 * it should be used where appropriate.
	 * 
	 * TODO: make this a configurable parameter
	 * 
	 * @var boolean
	 */
	protected $locking = false;
	
	/**
	 * If set to true, appends to file. Otherwise overwrites it.
	 * @var boolean
	 */
	protected $append = true;
	
	/** 
	 * Helper function which can be easily overriden by daily file appender. 
	 */
	
	public function activateOptions() {
		if (empty($this->file)) {
			$this->warn("Required parameter 'file' not set. Closing appender.");
			$this->closed = true;
			return;
		} else
			$this->closed = false;
	}
	
	public function append( \darkphp\logger\DwLoggingEvent $event) {
		$this->write( $this->layout->format($event) );
	}
	private $cache = array();
	/**
	 * Writes a string to the target file. Opens file if not already open.
	 * @param string $string Data to write.
	 */
	protected function write( string $string ) {
		$this->cache[] = $string;
	}
	public function readAndPurge() : array {
		$toReturn = $this->cache;
		$this->cache = array();
		return $toReturn;
	}
	/**
	 * Returns the 'append' parameter.
	 * @return boolean
	 */
	public function getAppend() {
		return $this->append;
	}

	/**
	 * Sets the 'append' parameter.
	 * @param boolean $append
	 */
	public function setAppend($append) {
		$this->setBoolean('append', $append);
	}
}
?>
