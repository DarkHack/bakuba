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

namespace bakuba\kernel\process\child\windows;
//use bakuba\kernel\process\child\windows\DwCmdCrypto as DwCmdCrypto;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\crypto\json\DwJsonCrypto as DwJsonCrypto;

class DwCmdCrypto extends DwCmdRandomizer implements \bakuba\kernel\crypto\iDwCrypto {
	private $cmdOpenSsl = "openssl";
	///*
	protected function createDwProcCmd() : DwProcCmd {
		$dwProcCmd = parent::createDwProcCmd();
		//$dwProcCmd->exec( "where openssl" );
		//$dwProcCmd->exec( "echo %PATH%" );
		return $dwProcCmd;
	}
	protected function opensslExec( string $paramsOpenSsl ,DwCallBack $dwCallBack ) : void {
		$cmdOpenSsl = "( FOR /F \"tokens=* USEBACKQ\" %A IN (`{$this->cmdOpenSsl} {$paramsOpenSsl}`) DO @echo %A )".\PHP_EOL;
		$this->getDwProcCmd()->execWithoutPrepare( $cmdOpenSsl ,$dwCallBack );
	}
	protected function opensslExecData( string $paramsOpenSsl ,string $pipeData ,DwCallBack $dwCallBack ,bool $isForEncode=true ) : void {
		$this->log->trace( array( "param" => $paramsOpenSsl ,"pipe" => $pipeData ) );
		
		$chunkSize = 2048; // Taille sûre pour Windows cmd
		$dataLen = strlen($pipeData);
		for ($i = 0; $i < $dataLen; $i += $chunkSize) {
			$chunk = substr($pipeData, $i, $chunkSize);
			// Premier segment : créer le fichier
			// Les suivants : append
			if ($i == 0) {
				$cmdOpenSsl .= "echo {$chunk} > crypto.b64" . \PHP_EOL; // overwrite
			} else {
				$cmdOpenSsl .= "echo {$chunk} >> crypto.b64" . \PHP_EOL; // append
			}
		}
		//$cmdOpenSsl ="echo {$pipeData} > crypto.b64".\PHP_EOL;
		if ( $isForEncode ) {
			$cmdOpenSsl .= "( FOR /F \"tokens=* USEBACKQ\" %A IN (`{$this->cmdOpenSsl} {$paramsOpenSsl} -in crypto.b64 -a`) DO @echo %A )".\PHP_EOL;
		} else {
			$cmdOpenSsl .="if exist output.bin del output.bin".\PHP_EOL;
			$cmdOpenSsl.="certutil -decode crypto.b64 output.bin".\PHP_EOL;
			$cmdOpenSsl .= "( FOR /F \"tokens=* USEBACKQ\" %A IN (`{$this->cmdOpenSsl} {$paramsOpenSsl} -in output.bin`) DO @echo %A )".\PHP_EOL;
			$cmdOpenSsl .="if exist output.bin del output.bin".\PHP_EOL;
			$cmdOpenSsl .="if exist crypto.b64 del crypto.b64".\PHP_EOL;
		}
		//$this->log->trace( $cmdOpenSsl );
		$this->getDwProcCmd()->execWithoutPrepare( $cmdOpenSsl ,$dwCallBack );
	}
	//*/
	public function listCiphers( DwCallBack $dwCallBack  ) : void {
		$this->log->trace( "." );
		$this->opensslExec( "enc -ciphers", new DwCallBack( array( &$this, "listTryCiphers" ) ,$dwCallBack ) );
	}
	public function listTryCiphers( DwCallBack $dwCallBack ,string $result ) : void {
		$result=trim($result);
		$this->log->trace( $result );
		if ( empty( $result ) )
			$this->opensslExec( "list -cipher-commands", new DwCallBack( array( &$this, "listTry2Ciphers" ) , $dwCallBack ) );
		else
			$dwCallBack->callback( $result );
	}
	public function listTry2Ciphers( DwCallBack $dwCallBack ,string $result ) : void {
		$result=trim($result);
		$this->log->trace( $result );
		if ( empty( $result ) )
			$this->opensslExec( "list-cipher-commands", $dwCallBack );
		else
			$dwCallBack->callback( $result );
	}
	
	public function listDigests( DwCallBack $dwCallBack  ) : void {
		$this->log->trace( "." );
			$this->opensslExec( "dgst -list", new DwCallBack( array( &$this, "listTryDigests" ) ,$dwCallBack ) );
	}
	public function listTryDigests( DwCallBack $dwCallBack ,string $result ) : void {
		$result=trim($result);
		$this->log->trace( $result );
		if ( empty( $result ) )
			$this->opensslExec( "list -digest-commands", new DwCallBack( array( &$this, "listTry2Digests" ) ,$dwCallBack ) );
		else
			$dwCallBack->callback( $result );
	}
	public function listTry2Digests( DwCallBack $dwCallBack ,string $result ) : void {
		$result=trim($result);
		$this->log->trace( $result );
		if ( empty( $result ) )
			$this->opensslExec( "list-message-digest-commands", $dwCallBack );
		else
			$dwCallBack->callback( $result );
	}
	public function generateSaltKeyIv( DwCallBack $dwCallBack ,string $cipher ,string $pass  ) : void {
		$this->log->trace( "." );
		$this->opensslExec(
			"enc -$cipher -P -pass pass:".$pass
			,new DwCallBack( array( &$this, "tryGenerateSaltKeyIv" ) ,$dwCallBack )
		);
	}
	public function tryGenerateSaltKeyIv( DwCallBack $dwCallBack ,string $result  ) : void {
		$result=trim($result);
		$this->log->trace( $result );
		$salt=null;$key=null;$iv=null;
		$pattern = '/salt=(\w+)\s+key=(\w+)\s+iv\s*=\s*(\w+)/';
		if ( \preg_match($pattern, $result, $matches) ) {
			$salt = $matches[1];
			$key  = $matches[2];
			$iv   = $matches[3];
			// Utilisation...
		}
		$dwCallBack->callback( $salt ,$key ,$iv );
	}
	
	public function initMessage( DwCallBack $dwDoneCallBack ,array $ciphers ,array $digests ) : void {
		$this->log->trace( "." );
		
		$dwJsonCrypto = DwJsonCrypto::fromJson( "{}" );
		$dwJsonCrypto->{"ciphers"}=implode( ':' ,$ciphers );
		$dwJsonCrypto->{"digests"}=implode( ':' ,$digests );
		
		$dwJsonCrypto->{"bundary"} = \bakuba\kernel\randomizer\DwRandomizer::randomize( 16 );
		$dwJsonCrypto->{"pass"} = \bakuba\kernel\randomizer\DwRandomizer::randomize( 16 );
		
		$dwJsonCrypto->{"cipher"} = \darkphp\DwArray::randomValueFromArray( $ciphers );
		$dwJsonCrypto->{"digest"} = \darkphp\DwArray::randomValueFromArray( $digests );
		
		$dwCallBack = new DwCallBack(
			array( &$this ,"initiedMessage" )
			,$dwDoneCallBack
			,$dwJsonCrypto
		);
		$this->generateSaltKeyIv( $dwCallBack ,$dwJsonCrypto->{"cipher"} ,$dwJsonCrypto->{"pass"} );
		
	}
	public function initiedMessage(
		DwCallBack $dwCallBack
		,DwJsonCrypto $dwJsonCrypto
		,string $salt = null
		,string $key = null
		,string $iv = null
	) {
		$salt=trim($salt);
		$key=trim($key);
		$iv=trim($iv);
		$this->log->trace(". salt:{$salt} key:{$key} iv:{$iv}" );
		if ( is_string( $salt ) ) 
			$dwJsonCrypto->{"salt"} = trim($salt);
		if ( is_string( $key ) ) 
			$dwJsonCrypto->{"key"}  = trim($key);
		if ( is_string( $iv ) ) 
			$dwJsonCrypto->{"iv"} = trim($iv);
		
		$dwCallBack->callback( $dwJsonCrypto );
	}
	//json_decode(json_encode(array( "cipher" => $cipher, "pass" => $pass, "data" => $clear )))
	/*
	public function encrypt(
		DwJsonCrypto $dwJsonCrypto
		,DwProcessShell $dwProcessShell= null
		,DwCallBack $dwDoneCallBack = null
	) : ?string 
	//*/
	
	public function encrypt( DwCallBack $dwCallBack ,DwJsonCrypto $dwJsonCrypto ) : void
	{
		$result = null; 
		$this->log->info( "".$dwJsonCrypto );
		/*
		foreach(array( "cipher", "pass", "salt", "iv", "key", "data" ) as $key ) {
			if (isset($dwJsonCrypto->{$key}))
				$this->log->info("$key:".$dwJsonCrypto->{$key} );
		}
		//*/
		$mdOption="-md {$dwJsonCrypto->digest}";
		///*
		$saltOption="";
		if ( ! isset( $dwJsonCrypto->salt ) || empty( $dwJsonCrypto->salt ) )
			$saltOption="-nosalt";
		else
			$saltOption="-salt -S ".$dwJsonCrypto->salt;
		//*/
		
		if ( isset( $dwJsonCrypto->iv ) && ! empty( $dwJsonCrypto->iv ) && isset( $dwJsonCrypto->key ) && ! empty( $dwJsonCrypto->key ) )
			$ivKeyOption="-K ".$dwJsonCrypto->key." -iv ".$dwJsonCrypto->iv;
		else
			$ivKeyOption="-pass pass:".$dwJsonCrypto->pass;
		
		$encrypt_cmd="enc -{$dwJsonCrypto->cipher} $ivKeyOption $saltOption $mdOption";
		//$this->log->info( $encrypt_cmd );
		$bundary=$dwJsonCrypto->{"bundary"};
		$this->opensslExecData(
			$encrypt_cmd
			,\bakuba\kernel\randomizer\DwRandomizer::randomize( 30 ).$bundary.base64_encode( 
				$dwJsonCrypto->data
			).$bundary.\bakuba\kernel\randomizer\DwRandomizer::randomize( 30 )
			,new DwCallBack( array( &$this, 'encrypted' ) ,$dwCallBack )
		);
	}
	///*
	public function encrypted(
		DwCallBack $dwCallBack
		,string $strEncrypted
	) {
		$cleanEncrypted = \darkphp\string\DwString::cleanBase64( $strEncrypted );
		$this->log->trace( $cleanEncrypted );
		$dwCallBack->callback( $cleanEncrypted );
	}
	//*/
	public function decrypt( DwCallBack $dwCallBack ,DwJsonCrypto $dwJsonCrypto ) : void
	{
		$result = null; 
		$this->log->info( "".$dwJsonCrypto );
		/*
		foreach(array( "cipher", "pass", "salt", "iv", "key", "data" ) as $key ) {
			if (isset($dwJsonCrypto->{$key}))
				$this->log->info("$key:".$dwJsonCrypto->{$key} );
		}
		//*/
		$mdOption="-md {$dwJsonCrypto->digest}";
		///*
		$saltOption="";
		if ( ! isset( $dwJsonCrypto->salt ) || empty( $dwJsonCrypto->salt ) )
			$saltOption="-nosalt";
		else
			$saltOption="-salt -S ".$dwJsonCrypto->salt;
		//*/
		
		if ( isset( $dwJsonCrypto->iv ) && ! empty( $dwJsonCrypto->iv ) && isset( $dwJsonCrypto->key ) && ! empty( $dwJsonCrypto->key ) )
			$ivKeyOption="-K ".$dwJsonCrypto->key." -iv ".$dwJsonCrypto->iv;
		else
			$ivKeyOption="-pass pass:".$dwJsonCrypto->pass;
		
		$decrypt_cmd="enc -{$dwJsonCrypto->cipher} -d $ivKeyOption $saltOption $mdOption";
		//$this->log->info( $decrypt_cmd );
		//$cmdPrintf $jsonMessage | jsonInObjectRead data | $cmdBase64 | shString64Filter | { $cmdPrintf "$(shRandomizeASCCI 30)${bundary}";$cmdCat <&0 ; $cmdPrintf "${bundary}$(shRandomizeASCCI 30)"; } | eval $encrypt_cmd | $cmdBase64 | shString64Filter
		
		$this->opensslExecData(
			$decrypt_cmd
			,$dwJsonCrypto->data
			,new DwCallBack( array( &$this, 'decrypted' ) ,$dwCallBack ,$dwJsonCrypto->{"bundary"} )
			,false
		);
	}
	public function decrypted(
		DwCallBack $dwCallBack
		,string $strBundary
		,string $strDecrypted
	) {
		$cleanData = \darkphp\string\DwString::cleanBase64(
			$this->extractBoundary( $strBundary ,$strDecrypted )
		);
		if ( empty( $cleanData ) )
			$dwCallBack->callback( null );
		else
			$dwCallBack->callback( \base64_decode( $cleanData ) );
	}
	function extractBoundary( string $bundary ,string $buffer ) :?string {
		$data = null;
		$parts = explode( $bundary ,$buffer);
		if (count($parts) >= 3)
			$data=$parts[1];
		else
			$this->log->error( "echec on extract '$bundary' inside '$buffer'" );
		
		return $data;
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
	}
	//*/
	/*
	public function toString( $withParent = false ) : string {
		if ( \is_null( $this->procPid ) )
			$objStr="";
		else
			$objStr="procPid:{$this->procPid}/";
		$objStr.= parent::toString();
		return $objStr;
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
