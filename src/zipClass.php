<?php
	/**
	 * (c) John Yusuf Habila <Senestro88@gmail.com>
	 * 
     * Zip class library (For creating archives and extracting archives)
     * 
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
	 */
    namespace PHPMaster88\libphp;
	class zipClass{
		// PRIVATE PROPERTIES
        private $version = "2.1.5";
        private $name = "";
        private $options = array('password'=>null, 'comment'=>null);
        private $addedFiles = array();
        private $addedPaths = array();
        private $addedContents = array();
        private $extensionLoaded = false;
		// PUBLIC PROPERTIES
        public $messages = array();
        public $savePath = null; // Can be an array or a string representing where to save the zip file. Default to null
        public $extractPath = null; // Can be an array or a string representing where to extract the zip file. Default to null
        // PUBLIC METHODS
		function __construct(string $name='', array $options = array()) {
            if($this->loadExtension('zip')){
                $this->extensionLoaded = true;
                $this->validateFilename($name);
                $this->validateOptions($options);
            }else{$this->message("The extension 'zip' couldn't be found. Please make sure your version of PHP was built with 'zip' support!");}
        }
		function __destruct(){}
		function __call(string $method, array $arguments = array()){
			if(!method_exists(__CLASS__, $method)){
				@set_error_handler(function($errno, $errstr, $errfile, $errline){echo "<div><b>Filename =></b> ".$errfile." <b>Line =></b> ".$errline." <b>Message =></b> ".$errstr."</div>";});
				try{trigger_error("Undefined method:  {$method}");}
	        	finally{restore_error_handler();}
			}
		}
        public function version() : string {return $this->version;}
        public function addFile(string | array $arg) : void {
            if($this->isString($arg) && @$this->validFile($arg)){$this->addedFiles[] = $this->arrangePath(realpath($arg));}
            else if($this->isArray($arg)){foreach ($arg as  $list){if($this->isString($list) && @$this->validFile($list)){$this->addedFiles[] = $this->arrangePath(realpath($list));}}}
        }
        public function addPath(string | array $arg) : void {
            if($this->isString($arg) && @$this->validDir($arg)){$this->addedPaths[]= $this->rtrimPathSlashit($this->arrangePath(realpath($arg)));}
            elseif($this->isArray($arg)){foreach ($arg as $list) {if($this->isString($list) && @$this->validDir($list)){$this->addedPaths[]= $this->rtrimPathSlashit($this->arrangePath(realpath($list)));}}}
        }
        public function addContent(string | array $name, string $content='') : void {
            if(!$this->emptyString($name) && !$this->emptyString($content)){$this->addedContents[$name] = $content;}
            else if($this->isArray($name)){foreach ($name as $index => $value){if(!$this->emptyString($index) && !$this->emptyString($value)){$this->addedContents[$index] = $value;}}}
        }
        public function setOptions(array $options) : void {$this->validateOptions($options);}
        public function saveZip() : bool | array {
            if ($this->loadExtension('zip')){
                if ($this->savePath === null) {$this->message("The save path is null, please define where to save the 'zip' archive!");}
                else if($this->validatePath("save") !== true){$this->message("The path's to save the 'zip' archive isn't found or isn't a valid path's!");}
                else{
                    $results = array();
                    @ini_set("memory_limit", "-1"); @ini_set("max_execution_time", "0"); @set_time_limit(0);
                    foreach ($this->savePath as $pathIndex =>  $pathLocation){
                        $startTime = microtime(true);
                        try{
                            $pathLocation = $this->rtrimPathSlashit($pathLocation);
                            $completeFilename = $pathLocation.''.$this->name;
                            $zip = new \ZipArchive(); clearstatcache();
                            $handle = $this->validFile($completeFilename) ? \ZipArchive::OVERWRITE : \ZipArchive::CREATE;
                            $open = $zip->open($completeFilename, $handle);
                            if($open !== true){$this->message('Unable to open the zip archive: '.$completeFilename.'');}
                            else{
                                if($this->options['comment'] !== null){$zip->setArchiveComment($this->options['comment']);}
                                if($this->options['password'] !== null){$zip->setPassword($this->options['password']);}
                                $CFiles = $EFiles = array(); // Compression and encryption files array
                                if($this->arrayEmpty($this->addedFiles) && $this->arrayEmpty($this->addedContents) && $this->arrayEmpty($this->addedPaths)){
                                    $readmeFilename = "readme.txt";
                                    @$zip->addFromString($readmeFilename, "THE ZIP ARCHIVE IS EMPTY", \ZipArchive::FL_OVERWRITE);
                                    array_push($CFiles, $readmeFilename); array_push($EFiles, $readmeFilename);
                                }else{
                                    // $this->addedFiles
                                    foreach ($this->addedFiles as $list) {
                                        $Filebasename = basename($list);
                                        if(@$zip->addFile($list, $Filebasename, 0, 0, \ZipArchive::FL_OVERWRITE)){array_push($CFiles, $Filebasename); array_push($EFiles, $Filebasename);}
                                    }
                                    // $this->addedContents
                                    foreach ($this->addedContents as $name => $content) {
                                        $Filebasename = basename($name);
                                        if(@$zip->addFromString($Filebasename, $content, \ZipArchive::FL_OVERWRITE)){array_push($CFiles, $Filebasename); array_push($EFiles, $Filebasename);}
                                    }
                                    // $this->addedPaths
                                    foreach ($this->addedPaths as $list) {
                                        $list = $this->rtrimPathSlashit($list);
                                        $i = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($list, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
                                        foreach ($i as $listFile) {
                                            clearstatcache();
                                            if($listFile->isFile()){
                                                $listFile = $this->arrangePath($listFile->getPathname());
                                                if(@$this->validFile($listFile)){
                                                    $Filename = $this->arrangePath(str_replace($list, $this->rtrimPathSlashit(basename($list)), $listFile));
                                                    if(@$zip->addFile($listFile, $Filename, 0, 0,  \ZipArchive::FL_OVERWRITE)){array_push($CFiles, $Filename); array_push($EFiles, $Filename);}
                                                }
                                            }
                                        }
                                    }
                                    foreach ($CFiles as $index => $list) {if($zip->isCompressionMethodSupported(\ZipArchive::CM_DEFLATE, true)){@$zip->setCompressionName($list, \ZipArchive::CM_DEFLATE);}}
                                    if($this->options['password'] !== null){ foreach ($EFiles as $index => $list) {if($zip->isEncryptionMethodSupported(\ZipArchive::EM_AES_256, true)){@$zip->setEncryptionName($list, \ZipArchive::EM_AES_256, $this->options['password']);}}}
                                    $close = @$zip->close();
                                    if ($close === false){$this->message("Unable to close the zip archive: ".$completeFilename." >>  ".$zip->getStatusString());}
                                    else{
                                        clearstatcache();
                                        $archiveSize = @filesize($completeFilename);
                                        $results[$pathIndex] = array(
                                            'sizes'=> array('bytes'=> $archiveSize, 'kilobytes'=> round($archiveSize/1024,1), 'megabytes'=> round(($archiveSize/1024)/1024,1), 'gigabytes'=> round((($archiveSize/1024)/1024)/1024,1)),
                                            'saved-location'=>$pathLocation, 'name'=>$this->name
                                        );
                                    }
                                }
                            }
                        }catch (\Throwable $e){$this->message($e->getMessage());}
                        $endTime = microtime(true);
                        if(isset($results[$pathIndex])){$results[$pathIndex]['generatedTime'] = number_format(($endTime - $startTime), 2)."s";}
                    }
                    return $this->arrayEmpty($results) ? false : $results;
                }
            }
            return false;
        }
        public function extractZip() : bool | array {
            if ($this->loadExtension('zip')){
                if ($this->extractPath === null) {$this->message("The extract path is null, please define where to extract the 'zip' archive!");}
                else if($this->validatePath("extract") !== true){$this->message("The path's to extract the 'zip' archive isn't found or isn't a valid path's!");}
                else{
                    $results = array();
                    @ini_set("memory_limit", "-1"); @ini_set("max_execution_time", "0"); @set_time_limit(0);
                    foreach ($this->extractPath as $pathIndex =>  $pathLocation){
                        $startTime = microtime(true);
                        try{
                            $pathLocation = $this->rtrimPathSlashit($pathLocation);
                            $zip = new \ZipArchive(); clearstatcache();
                            $open = $zip->open($this->name);
                            if($open !== true){$this->message('Unable to open the zip archive: '.$this->name.'');}
                            else{
                                if($this->options['password'] !== null){$zip->setPassword($this->options['password']);}
                                $zip->extractTo($pathLocation);
                                $close = @$zip->close();
                                if ($close === false){$this->message("Unable to close the zip archive: ".$this->name." >>  ".$zip->getStatusString());}
                                else{
                                    clearstatcache();
                                    $archiveSize = @filesize($this->name);
                                    $results[$pathIndex] = array(
                                        'sizes'=> array('bytes'=> $archiveSize, 'kilobytes'=> round($archiveSize/1024,1), 'megabytes'=> round(($archiveSize/1024)/1024,1), 'gigabytes'=> round((($archiveSize/1024)/1024)/1024,1)),
                                        'extracted-location'=>$pathLocation, 'name'=>$this->name
                                    );
                                }
                            }
                        }catch (\Throwable $e){$this->message($e->getMessage());}
                        $endTime = microtime(true);
                        if(isset($results[$pathIndex])){$results[$pathIndex]['generatedTime'] = number_format(($endTime - $startTime), 2)."s";}
                    }
                    return $this->arrayEmpty($results) ? false : $results;
                }
            }
            return false;
        }
        // PRIVATE METHODS
        private function loadExtension($extension) : bool {
            if(extension_loaded($extension)){return true;}
            if (function_exists('dl') === false || ini_get('enable_dl') != 1){return false;}
            if(strtoupper(substr(PHP_OS, 0, 3)) === "WIN"){$extensionSuffix = ".dll";}
            else if(PHP_OS == 'HP-UX'){$extensionSuffix = ".sl";}
            else if(PHP_OS == 'AIX'){$extensionSuffix = ".a";}
            else if(PHP_OS == 'OSX'){$extensionSuffix = ".bundle";}
            else{$extensionSuffix = '.so';}
            return @dl('php_'.$extension.''.$extensionSuffix) || @dl($extension.''.$extensionSuffix);
        }
        private function createPath(string $arg) : bool {if($this->validDir($arg)){return true;} else{try {return @mkdir($this->rtrimPathSlashit($arg), 0755, true);}catch (\Throwable $e) {return false;}}}
        private function validatePath(string $which) : bool {
            if($which == "save" || $which == "extract"){
                $whichPath = ($which == "save") ? $this->savePath : $this->extractPath; $array = array();
                if(!$this->emptyString($whichPath) && @$this->createPath($whichPath)){$array[]= $this->rtrimPathSlashit(realpath($whichPath));}
                elseif($this->isArray($whichPath)){foreach ($whichPath as $list) {if(!$this->emptyString($list) && @$this->createPath($list)){$array[]= $this->rtrimPathSlashit(realpath($list));}}}
                if($this->arrayEmpty($array)){return false;}
                else{if($which == "save"){$this->savePath = $array;}else{$this->extractPath = $array;} return true;}
            }
            return false;
        }
        private function validateFilename(string $name) : void {
            if($this->emptyString($name)){$name = $this->generateFilename();}
            $getExtension = $this->getExtension($name);
            $name = (strtolower($getExtension) !== 'zip') ? $name.'.zip' : $name;
            $this->name = str_replace(array('\\', '/', ':', '*', '?', '<', '>', '|'), '',  $name);
        }
        private function validateOptions(array $options) : void {
            if(isset($options['password']) && strlen($options['password']) > 0){$this->options['password'] = $options['password'];}
            if(isset($options['comment']) && strlen($options['comment']) > 0){$this->options['comment'] = $options['comment'];}
        }
        private function message(string $msg) : void {if(!$this->emptyString($msg)){if(gettype($this->messages) !== 'array'){$this->messages = array();} $this->messages[] = $msg;}}
        private function arrangePath(string $path="/", bool $edgeClose = false) : string {
            $path = str_replace("\\", "/", $path); $x = explode("/", $path); $a = array();
            foreach($x as $ps){if(!empty($ps)){$a[] = $ps;}}
            return ($edgeClose === true ? "/" : "").implode("/", $a).($edgeClose === true ? "/" : "");
        }
        private function rtrimPath(string $arg){return rtrim((str_replace(array("/", "\\"), "/", $arg)), "/\\");}
        private function rtrimPathSlashit(string $arg) {return $this->rtrimPath($arg)."/";}
        private function generateFilename() {return md5(time());}
        private function getExtension(string $name){$array = explode(".", $name); return array_pop($array);}
        private function isInt($arg){return gettype($arg) == 'integer' ? true : false;}
        private function isArray($arg){return is_array($arg) ? true : false;}
        private function arrayEmpty($a) {return $this->isArray($a) && count($a) > 0 ? false : true;} 
        private function validDir($arg){try {return (file_exists($arg) && is_dir($arg) && $this->isReadable($arg)) ? true : false;} catch (\Throwable $e) {return false;}}
        private function isReadable($arg){return is_readable($arg) ? true : false;}
        private function validFile($arg){try {return (file_exists($arg) && is_file($arg) && $this->isReadable($arg)) ? true : false;} catch (\Throwable $e) {return false;}}
        private function emptyString($arg){return ($this->isString($arg) && empty($arg)) ? true : false;}
        private function isString($arg){return is_string($arg) ? true : false;}
	}