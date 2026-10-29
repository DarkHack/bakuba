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

namespace bakuba\kernel\apache2;

class HtAccess {
	private $cr = "\r\n";
	private $head = "";
	private $footer = "";
	public function __construct( ) {
		//parent::__construct( $ctrl, $title );
		
		$this->head = "# Do not edit, bakuba generate".$this->cr;
		$this->head .= "#<IfModule mod_rewrite.c>".$this->cr;
		$this->head .= "RewriteEngine On".$this->cr;
		
		// Welcome : . / index.html /index.html
		$this->add("^\$","index.php?type=html&className=Welcome");
		$this->add("^/\$","index.php?type=html&className=Welcome");
		$this->add("^index.html\$","index.php?type=html&className=Welcome");
		$this->add("^/index.html\$","index.php?type=html&className=Welcome");
		
		//^([^\.]*)$ - tous sauf un point
		$this->add("^([^\.]*)\$","index.php?type=-none&className=\$1");
		
		$this->add("^(.*)\\.xml\$","index.php?type=xml&className=\$1");
		$this->add("^(.*)\\.json\$","index.php?type=json&className=\$1");
		$this->add("^(.*)\\.html\$","index.php?type=html&className=\$1");
		$this->add("^(.*)\\.htm\$","index.php?type=htm&className=\$1");
		$this->add("^(.*)\\.js\$","index.php?type=js&className=\$1");
		$this->add("^(.*)\\.php\$","index.php?type=php&className=\$1");
		$this->add("^(.*)\\.sh\$","index.php?type=sh&className=\$1");
		
		$this->add("^(.*)/(.*)\\.css\$","index.php?type=css&className=\$1&file=\$2");
		
		$this->add("^(.*)\\.ico\$","index.php?type=ico&className=Image&file=\$1.ico");
		$this->add("^(.*)\\.png\$","index.php?type=png&className=Image&file=\$1.png");
		$this->add("^(.*)\\.jpg\$","index.php?type=jpg&className=Image&file=\$1.jpg");
		
		$this->footer = "#</IfModule>".$this->cr;
		$this->footer .= "".$this->cr;
		$this->footer .= "Options -Indexes ".$this->cr;
		$this->footer .= "php_flag session.use_cookies 0".$this->cr;
		$this->footer .= "".$this->cr;
	}
	private $rewriteRules = array();
	
	public function add($dest,$redirect) {
		try {
			$this->rewriteRules[] = array("dest" => $dest, "redirect" => $redirect);
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	
	public function toString() {
		$sourceClient = "";
		$sourceClient = $this->head;
		foreach($this->rewriteRules as $rewriteRule) 
			$sourceClient .= "RewriteRule \"".$rewriteRule["dest"]."\" \"".$rewriteRule["redirect"]."\" [L,QSA]".$this->cr;
		$sourceClient .= $this->footer;
		return $sourceClient;
	}
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	
}
