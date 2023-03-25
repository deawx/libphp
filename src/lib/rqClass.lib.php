<?php
    /**
     * (c) John Yusuf Habila <Senestro88@gmail.com>
     * 
     * HTTP/HTTPS request class library
     * 
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */
    namespace PHPMaster88\lib;
	class rqClass{
		// PRIVATE PROPERTIES
		private $requestMethods = array('GET', 'POST', 'HEAD');
        private $reponse = array('status'=>false);
        private $ua = "Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36";
		// PUBLIC PROPERTIES
		public $options = array(1 =>'referer', 2 =>'connect-only', 3 =>'crlf', 4 => 'file-time', 5 =>'follow-location', 6 =>'forbit-reuse', 7 =>'fresh-connect', 8 =>'header', 9 =>'nobody', 10 =>'no-progress', 11 =>'return-transfer', 12 =>'connect-timeout', 13 =>'max-redirs', 14 => 'time-out', 15 =>'cookie', 16 =>'http-header');
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
		public function request(string $method, string $url = "", array $params = array(), array $options = array(), bool $returnInfo = false) : string | bool {
            $method = strtoupper(trim($method)); $params = http_build_query($params);
            if(!in_array($method, $this->requestMethods)){$this->reponse['message'] = "The request method msut be one the following ".implode(", ", $this->requestMethods);}
            else{
                $handle =  curl_init();
                if($handle === false){$this->reponse['message']  = "Can't initialize the Handler";}
                else{
                    curl_setopt_array($handle, array(
                        CURLOPT_SSLVERSION => 0, // Default SSL Version
                        CURLOPT_SSL_VERIFYPEER => false, // Stop cURL from verifying the peer's certificate
                        CURLOPT_SSL_VERIFYSTATUS => false, // Do not verify the certificate's status. 
                        CURLOPT_PROXY_SSL_VERIFYPEER => false, // Stop cURL from verifying the peer's certificate
                        CURLOPT_USERAGENT => $this->ua,
                        CURLOPT_HEADER => false, // Include the header in the output
                        CURLOPT_RETURNTRANSFER => true, // To return the transfer as a string of the return value of curl_exec() instead of outputting it directly
                        CURLOPT_FOLLOWLOCATION => true, // Follow any "Location: " header that the server sends as part of the HTTP header
                        CURLOPT_MAXREDIRS => 3, // The maximum amount of HTTP redirections to follow
                        CURLOPT_CONNECTTIMEOUT => 60, // The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
                        CURLOPT_TIMEOUT => 60, // The maximum number of seconds to allow cURL functions to execute. 
                        CURLOPT_HTTPHEADER=> array("Cache-Control: no-cache"), // An array of HTTP header fields to set
                        CURLOPT_FORBID_REUSE => true, // To force the connection to explicitly close when it has finished processing, and not be pooled for reuse
                        CURLOPT_FRESH_CONNECT => true, // To force the use of a new connection instead of a cached one. 
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    ));

                    foreach ($options as $key => $value) {
                        if($key == $this->options[1]){curl_setopt($handle, CURLOPT_AUTOREFERER, (is_bool($value) ? $value : false));} // To automatically set the Referer: field in requests where it follows a Location: redirect. 
                        else if($key == $this->options[2]){@curl_setopt($handle, CURLOPT_CONNECT_ONLY, (is_bool($value) ? $value : false));} // Tells the library to perform all the required proxy authentication and connection setup, but no data transfer. This option is implemented for HTTP, SMTP and POP3.
                        else if($key == $this->options[3]){curl_setopt($handle, CURLOPT_CRLF, (is_bool($value) ? $value : false));} // To convert Unix newlines to CRLF newlines on transfers
                        else if($key == $this->options[4]){curl_setopt($handle, CURLOPT_FILETIME, (is_bool($value) ? $value : false));} // To attempt to retrieve the modification date of the remote document. This value can be retrieved using the CURLINFO_FILETIME option with curl_getinfo(). 
                        else if($key == $this->options[5]){curl_setopt($handle, CURLOPT_FOLLOWLOCATION, (is_bool($value) ? $value : false));} // // Follow any "Location: " header that the server sends as part of the HTTP header
                        else if($key == $this->options[6]){curl_setopt($handle, CURLOPT_FORBID_REUSE, (is_bool($value) ? $value : true));} // To force the connection to explicitly close when it has finished processing, and not be pooled for reuse
                        else if($key == $this->options[7]){curl_setopt($handle, CURLOPT_FRESH_CONNECT, (is_bool($value) ? $value : true));} // To force the use of a new connection instead of a cached one. 
                        else if($key == $this->options[8]){curl_setopt($handle, CURLOPT_HEADER, (is_bool($value) ? $value : false));} // Include the header in the output
                        else if($key == $this->options[9]){curl_setopt($handle, CURLOPT_NOBODY, (is_bool($value) ? $value : true));} // To exclude the body from the output. Request method is then set to HEAD. Changing this to false does not change it to GET. 
                        else if($key == $this->options[10]){curl_setopt($handle, CURLOPT_NOPROGRESS, (is_bool($value) ? $value : true));} // To disable the progress meter for cURL transfers. Note: PHP automatically sets this option to true, this should only be changed for debugging purposes. 
                        else if($key == $this->options[11]){curl_setopt($handle, CURLOPT_RETURNTRANSFER, (is_bool($value) ? $value : true));} // // To return the transfer as a string of the return value of curl_exec() instead of outputting it directly
                        else if($key == $this->options[12]){curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, (is_int($value) ? $value : 60));} // The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
                        else if($key == $this->options[13]){curl_setopt($handle, CURLOPT_MAXREDIRS, (is_int($value) ? $value : 5));} // The maximum amount of HTTP redirections to follow
                        else if($key == $this->options[14]){curl_setopt($handle, CURLOPT_TIMEOUT, (is_int($value) ? $value : 60));} // The maximum number of seconds to allow cURL functions to execute. 
                        else if($key == $this->options[15]){curl_setopt($handle, CURLOPT_COOKIE, (is_string($value) ? $value : ""));} // The contents of the "Cookie: " header to be used in the HTTP request
                        else if($key == $this->options[16]){curl_setopt($handle, CURLOPT_HTTPHEADER, (is_array($value) ? $value : array()));} // An array of HTTP header fields to set
                    }

                    if($method == "POST"){curl_setopt_array($handle, array(CURLOPT_URL => $url, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $params));}
                    else if($method == "GET"){curl_setopt_array($handle, array(CURLOPT_URL => $url.'?'.$params, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPGET => true));}
                    else if($method == "HEAD"){curl_setopt($handle, CURLOPT_URL, $url); curl_setopt($handle, CURLOPT_NOBODY, true);}

                    $handleExec = @curl_exec($handle);
                    if($handleExec === false){$this->reponse['message'] = 'Curl error: ' . curl_error($handle); return json_encode($this->reponse, JSON_FORCE_OBJECT);}
                    else{
                        $result = null;
                        if($returnInfo === true){$result = curl_getinfo($handle);}
                        else{$result = $handleExec;}
                        @curl_close($handle);
                        return $result;
                    }
                }
            }
        }
	}