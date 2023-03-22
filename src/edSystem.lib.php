<?php
    /**
     * (c) John Yusuf Habila <Senestro88@gmail.com>
     * 
     * Encrypting & Decrypting system library
     * 
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */
    namespace PHPMaster88;
	class edSystem{
		// PRIVATE PROPERTIES
		private $iv = "a7e0a4a8397efe40"; // Don't change value unless the Developer
		private $method = "aes-128-cbc"; // Don't change value unless the Developer
		// PUBLIC PROPERTIES
		public $encryptedData = null; // Can be false or null
		public $decryptedData = null;
		// PUBLIC METHODS
		function __construct(){}
		function __destruct(){}
		function __call(string $method, array $arguments = array()){
			if(!method_exists(__CLASS__, $method)){
				@set_error_handler(function($errno, $errstr, $errfile, $errline){echo "<div><b>Filename =></b> ".$errfile." <b>Line =></b> ".$errline." <b>Message =></b> ".$errstr."</div>";});
				try{trigger_error("Undefined method:  {$method}");}
	        	finally{restore_error_handler();}
			}
		}
		public function encrypt(string $data, string $key='', callable $callback = null){
			$data = trim($data); $key = trim($key);
			if(in_array($this->method, openssl_get_cipher_methods()) && !empty($data)){
				try {
					$this->encryptedData = @openssl_encrypt($data, $this->method, $key, $options = 0, $this->iv);
					if($this->encryptedData !== false){
						$data =  null;
						$this->encryptedData = @bin2hex($this->encryptedData);
						if($this->isCallable($callback)){call_user_func($callback, $this->encryptedData, $key);}
						return $this->encryptedData;
					}
				}catch (\Throwable $e) {}
			}
			$this->encryptedData = $data = $key = null;
			if($this->isCallable($callback)){call_user_func($callback, false, false);}
			return false;
		}
		public function decrypt(string $data, string $key='', callable $callback = null){
			$data = trim($data); $key = trim($key);
			if(in_array($this->method, openssl_get_cipher_methods()) && !empty($data)){
				try {
					$data = trim(@hex2bin($data));
					$this->decryptedData = @openssl_decrypt($data, $this->method, $key, $options = 0, $this->iv);
					if($this->decryptedData !== false){
						$data =  null;
						if($this->isCallable($callback)){call_user_func($callback, $this->decryptedData, $key);}
						return $this->decryptedData;
					}
				}catch (\Throwable $e) {}
			}
			$this->decryptedData = $data = $key = null;
			if($this->isCallable($callback)){call_user_func($callback, false, false);}
			return false;
		}
		// PRIVATE METHODS
		private function isCallable(mixed $callable){if(($callable instanceof Closure) && is_callable($callable)){return true;} return false;}
	}