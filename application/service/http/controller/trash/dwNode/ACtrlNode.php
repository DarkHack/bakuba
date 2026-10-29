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


namespace bakuba\controller;



use darkphp\logger\DwLogger as DwLogger;

use bakuba\kernel\DwAutoLoad as DwAutoLoad;

use bakuba\kernel\objects\DwKernel as DwKernel;



use bakuba\kernel\dwController\abstracts\ACtrl as ACtrl;

use bakuba\kernel\objects\web\DwWebAction as DwWebAction;

use darkphp\web\DwWebValue as DwWebValue;



use bakuba\kernel\dwCrypto\DwCryptController as DwCryptController;



class ACtrlNode extends DwCryptController {

	

	private $log;

	public function __construct( ) {

		try {

			$this->log = DwLogger::getLogger(__CLASS__);

			$this->log->info(".");

			parent::__construct( __CLASS__ );

			

		} catch(Exception $err) {

			$this->log->error($err);

			throw $err;

		}

	}

	public function request( ) {

	}

	public function executeCrypt( $dwNode, $Json64message ) {

		$response = null;

		try {

			$this->log->info(".");

			$data = json_decode(  base64_decode( $Json64message ) );

			if (isset($data->status))

				$dwNode->setStatus($data->status);

			

			if (isset($data->action)) {

				switch ( "".$data->action ) {

				case "ping" :

					$response = "pong";

					break;

				case "shutdown" :

					$dwNode->shutdown();

					$response = "thanks!";

					break;

				default :

					$response = "unknow:".$data->action;

					break;

				}

			}

			else

				// $response = "no action";

		} catch(Exception $err) {

			$this->log->error($err);

			throw $err;

		}

		return $response;

	}

}
