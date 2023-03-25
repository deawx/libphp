<?php
    /**
     * (c) John Yusuf Habila <Senestro88@gmail.com>
     * 
     * File & Directory class library
     * 
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */
    namespace PHPMaster\lib;
	class fdClass{
		// PRIVATE PROPERTIES
		

		// PUBLIC PROPERTIES


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
		public function isExists(string $file) : bool {return @file_exists($file);} // Checks whether a file or directory exists
		public function isFile(string $file) : bool {return @is_file($file);} // Tells whether the filename is a regular file
		public function isDir(string $path) : bool {return @is_dir($path);} // Tells whether the filename is a directory
		public function isLink(string $file) : bool {return @is_link($file);} // Tells whether the filename is a symbolic link
		public function isReadable(string $file) : bool {return @is_readable($file);} // Tells whether a file exists and is readable
		public function isExecutable(string $file) : bool {return @is_executable($file);} // Tells whether the filename is executable
		public function isWritable(string $file) : mixed {return @is_writable($file);} // Tells whether the filename is writable
		// Gets file size
		public function size(string $file = '') : int {if($this->isFile($file) && $this->isReadable($file)){$size = @filesize($file); clearstatcache(false, $file); return $size;} return 0;}
		// Formats size to human readble
		public function readableSize(string $file = '', int $precision = 2) : string {
			$size = (int) $this->size($file);
			if($size > 360){$base = log($size, 1024); $suffixes = array('Bytes', 'KB', 'MB', 'GB', 'TB'); return round(pow(1024, ($base - floor($base))), $precision).' '.$suffixes[floor($base)];}
			return "0 Bytes";
		}
		// Gets last access time of file
		public function accessTime(string $file) : int {if($this->isFile($file)){return @fileatime($file);} return 0;}
		// Gets file modification time
		public function modTime(string $file) : int {if($this->isFile($file)){return @filemtime($file);} return 0;}
		// Gets inode change time of file
		public function changeTime(string $file) : int {if($this->isFile($file)){return @filectime($file);} return 0;}
		// Gets file owner
		public function getOwner(string $file) : string | int {
			if($this->isFile($file)){return (function_exists("posix_getpwuid") ? @posix_getpwuid(fileowner($file)) : fileowner($file));}
			return 0;
		}
		// Gets file group
		public function getGroup(string $file) : string | int  {
			if($this->isFile($file)){return (function_exists("posix_getgrgid") ? @posix_getgrgid(filegroup($file)) : filegroup($file));}
			return 0;
		}
		// A readable unix time
		public function readableUnix(string | int $time) : string {
			if(is_numeric($time) && $time > 0){return @date("l, F jS, Y g:i:s A", $time);}
			return "";
		}
		//  Create file with unique file name
		public function tempname(string $path, string $priefix='') : string {
			if($this->isDir($path)){return @tempnam($path, $priefix);}
			return "";
		}
		// Get the file mime content-type 
		public function getMime(string $file) : string {
			if($this->isFile($file)){return @mime_content_type($file);}
			return "";
		}
		// Create a hard link
		public function hardLink(string $target, string $link) : bool {
			if($this->isExists($target)){return @link($target, $link);}
			return false;
		}
		// Creates a symbolic link
		public function symLink(string $target, string $link) : bool {
			if($this->isExists($target)){return @symlink($target, $link);}
			return false;
		}
		// Returns the target of a symbolic link
		public function readLink(string $target) : string | bool {
			if($this->isExists($target)){return @readlink($target);}
			return "";
		}
		// Gets information about a link
		public function linkInfo(string $target) : int | bool | string {
			if($this->isExists($target)){return @linkinfo($target);}
			return "";
		}
		// Changes file group
		public function changeGroup(string $file, string | int $group='') : bool {
			if($this->isFile($file) && (is_numeric($group) || (is_string($group) && !empty($group)))){return @chgrp($file, $group);}
			return false;
		}
		// Changes file owner
		public function changeOwner(string $file, string | int $owner='') : bool {
			if($this->isFile($file) && (is_numeric($owner) || (is_string($owner) && !empty($owner)))){return @chown($file, $owner);}
			return false;
		}
		// Changes file mode
		public function changeMod(string $file, string | int $mode='') : bool {
			if($this->isFile($file) && is_numeric($mode)){return @chmod($file, (int) $mode);}
			return false;
		}
		// Gets file permissions
		public function perms(string $file) : string {
			if($this->isFile($file) && @fileperms(realpath($file)) !== false){return substr(sprintf('%o', @fileperms(realpath($file))), -4);}
			return "";
		}
		// Gets readable file permission
		public function readablePerms(string $file) : string {
			if($this->isFile($file) && @fileperms(realpath($file)) !== false){
				$perms = @fileperms(realpath($file));
				switch ($perms & 0xF000) {
				    case 0xC000: $info = 's'; break; // Socket
				    case 0xA000: $info = 'l'; break; // Symbolic link
				    case 0x8000: $info = 'r'; break; // Regular
				    case 0x6000: $info = 'b'; break; // Block special
				    case 0x4000: $info = 'd'; break; // Directory
				    case 0x2000: $info = 'c'; break; // Character special
				    case 0x1000: $info = 'p'; break; // FIFO pipe
				    default: $info = 'u'; // unknown
				}
				// Owner
				$info .= (($perms & 0x0100) ? 'r' : '-');
				$info .= (($perms & 0x0080) ? 'w' : '-');
				$info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));
				// Group
				$info .= (($perms & 0x0020) ? 'r' : '-');
				$info .= (($perms & 0x0010) ? 'w' : '-');
				$info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));
				// World
				$info .= (($perms & 0x0004) ? 'r' : '-');
				$info .= (($perms & 0x0002) ? 'w' : '-');
				$info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));
				return $info;
			}
			return "";
		}
		// Gets file type
		public function getType(string $file) : string | bool {
			if($this->isFile($file)){return @filetype(realpath($file));}
			return "unknown";
		}
		// Gives information about a file or symbolic link
		public function getStats(string $file, string $key='') : array {
			if($this->isFile($file)){
				$stat = @lstat($file); $stats = array();
				if($stat  !== false){
					foreach ($stat as $k => $v) {if(is_string($k)){$stats[$k] = $v;}}
					if(!empty($key) && isset($stats[$key])){return $stats[$key];}
					$stat = null;
					return $stats;
				}
			}
			return array();
		}
		// Append content to a file (binary-safe)
		public function appendContent(string $file, string $content='', bool $nl=false) : bool {
			$write = false; $handler = @fopen($file, "a");
			if($this->lockStream($handler) === true){
				$size = (int) $this->size($file);
				$writeContent = ($nl === true && $size > 0) ? "\n".$content : $content;
				$write = @fwrite($handler, $writeContent);
				@fclose($handler); unset($content); unset($writeContent);
			}else{if($this->isStream($handler)){@fclose($handler);}}
			return ($write !== false) ? true : false;
		}
		// Save or put content to a file (binary-safe)
		public function putContent(string $file, string $content='', bool $append=false) : bool {
			$write = false; $handler = @fopen($file, $append === true ? "a" : "w");
			if($this->lockStream($handler) === true){
				$write = @fwrite($handler, $content);
				@fclose($handler); unset($content);
			}else{if($this->isStream($handler)){@fclose($handler);}}
			return ($write !== false) ? true : false;
		}
		// Gets the content of a file
		public function getContent(string $file) : string {
			$content = ''; $handler = @fopen($file, "r");
			if($this->lockStream($handler) === true){
				$content = @stream_get_contents($handler); @fclose($handler);
			}else{if($this->isStream($handler)){@fclose($handler);}}
			return $content;
		}
		// Copies file
		public  function copyFile(string $source, string $destination, \resource $context = null) : bool {
			clearstatcache();
			if($this->isFile($source)){return @copy($source, $destination, $context);}
			return false;
		}
		// Moves a file or directory
		public function move(string $source, string $destination, \resource $context = null) : bool {
			clearstatcache();
			if($this->isFile($source)){
				$rename = @rename($source, $destination, $context);
				if($rename === true){if(($source !== $destination) === true && $this->isFile($source)){$this->deleteFile($source);} return true;}
			}
			return false;
		}
		// Touch a file (Sets access and modification time of file)
		public function touchFile(string $file, int $mtime=null, int $atime=null) : bool {return @touch($file, $mtime, $atime);}
		// Create a file
		public function createFile(string $file) : bool {
			if($this->isFile($file)){return true;}
			else{
				$handler = @fopen($file, "w");
				if($handler !== false){@fclose($handler); $this->changeMod(realpath($file), "0644"); return true;}
				return false;
			}
		}
		// Create a directory
		public function createDir(string $path) : bool {
			if($this->isDir($path)){$this->changeMod($path, "0755"); return true;}
			else{try{$mkdir = @mkdir($path, 0755, true); if($mkdir === true){$this->changeMod($path, "0755"); return true;}}catch (\Throwable $e){}}
			return false;
		}
		// Deletes a file
		public  function deleteFile(string $file, \resource $context = null) : bool {
			if($this->isFile($file)){return @unlink($file, $context);}
			return false;
		}
		// Deletes a directory
		public function deleteDir(string $path) : bool {return $this->emptyDirectory($path, true);}
		// Deletes a directory or a file
		public function delete(string $arg) : bool {if($this->isFile($arg)){ return $this->deleteFile($arg);} else if($this->isDir($arg)){return $this->deleteDir($arg);} return false;}
		// Empty a directory
		public function emptyDirectory(string $path, bool $selfDelete = false) : bool {
			if($this->isDir($path)){
				$i = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
				foreach($i as $list){$list = realpath($list->getRealPath()); if ($this->isFile($list)) {$this->deleteFile($list);} else if($this->isDir($list)){@rmdir($list);}}
				$i = null; unset($i);
				if($selfDelete === true){return @rmdir($path);} return true;
			}
			return false;
		}
		// Delete a file specified by it extension
		public function extDelete(string $path, array $extensions=array()) : void {
        	if($this->isDir($path)){
        		$path = realpath($path);
    			foreach ($extensions as $extension){
    				$i = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
    				foreach($i as $list){
    					$list = realpath($list->getRealPath()); $info = $this->getInfo($list);
    					$si = (isset($info['extension']) ? $info['extension'] : "");
    					if ($si == $extension) {$this->deleteFile($list);}
    				}
    			}
        	}
        }
        // Check if a file content contains the string provided
        public function containString(string $file, string $contained) : bool {
        	$content = $this->getContent($file);
        	if (strpos($content, $contained) !== false) {unset($content); return true;}
        	return false;
        }
        // Check if a file content starts with the string provided
        public function startsWith(string $file, string $start) : bool { 
        	$content = $this->getContent($file); $start = trim($start);
			if(substr($content, 0, strlen($start)) == $start){unset($content); return true;}
			return false;
		}
		// Downloads a file
		public function downloadFile(string $file) : bool {
			if($this->isFile($file) && $this->headers_sent() !== true){
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename='.basename($file));
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . (int) $this->size($file));
				flush();
				readfile($file);
				return true;
			}else{return false;}
		}
		// Gets file information 
		public function openHandler(string $file, string $mode = "r", bool $useIncludePath = false, \resource | null $context = null ) : null | SplFileObject {
			if($this->isFile($file)){
				$array = array(); $i = new \SplFileInfo($file);
				if($i->isWritable()){return $i->openFile($mode, $useIncludePath, $context);}
			}
			return null;
		}
		// Gets file information 
		public function getInfo(string $file) : array {
			if($this->isFile($file)){
				$array = array(); $i = new \SplFileInfo($file);
				$array['realpath'] = $i->getRealPath(); $array['dirname'] = $i->getPath(); $array['basename'] = $i->getBasename();
				$array['extension'] = $i->getExtension(); $array['filename'] = $i->getBasename(".".$i->getExtension());
				$array['size'] = array('raw'=>$i->getSize(), 'readable'=>$this->readableSize($file));
				$array['atime'] = array('raw'=>$i->getATime(), 'readable'=>$this->readableUnix($i->getATime()));
				$array['mtime'] = array('raw'=>$i->getMTime(), 'readable'=>$this->readableUnix($i->getMTime()));
				$array['ctime'] = array('raw'=>$i->getCTime(), 'readable'=>$this->readableUnix($i->getCTime()));
				$array['mime'] = $this->getMime($file); $array['type'] = $i->getType();
				$array['permission'] = array('raw'=>$this->perms($file), 'readable'=>$this->readablePerms($file));
				$array['owner'] = array('raw'=>$i->getOwner(), 'readable'=>(function_exists("posix_getpwuid") ? posix_getpwuid($i->getOwner()) : ""));
				$array['group'] = array('raw'=>$i->getGroup(), 'readable'=>(function_exists("posix_getgrgid") ? posix_getgrgid($i->getGroup()) : ""));
				if($i->isLink()){$array['target'] = $i->getLinkTarget();}
				$array['executable'] = ($i->isExecutable() === true ? "true" : "false");
				$array['readable'] = ($i->isReadable() === true ? "true" : "false");
				$array['writable'] = ($i->isWritable() === true ? "true" : "false");
				return array_filter($array);
			}
			return array();
		}
		// Gets the current working directory. 
		public function currentDir() : string | bool {return @getcwd();}
		// Changes PHP's current directory 
		public function changeDir(string $path) : bool {return $this->isDir($path) ? @chdir($path) : false;}
		// Scan a directory and list out the files
		public function openDir(string $path) : array {
			if($this->isDir($path) && $this->isReadable($path)){
				$i = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
				$array = array();
				foreach($i as $list){$array[] = realpath($list->getRealPath());}
				$i = null; unset($i);
				return $array;
			}
			return array();
		}
		// Gets the size of a directory
		public function dirSize(string $path) : int {
			$size = 0; $open = $this->openDir($path);
			foreach($open as $key => $value){if($this->isFile($value) && $this->isReadable($value)){$size +=@filesize($value); clearstatcache(false, $value);}}
			$open = null; unset($open);
			return $size;
		}
		// Gets the information  of a directory
		public function dirFilesInfo(string $path) : array {
			$array = array(); $open = $this->openDir($path);
			foreach($open as $key => $value){if($this->isFile($value)){$array[] = $this->getInfo($value);} else if ($this->isDir($value)){}}
			$open = null; unset($open);
			return $array;
		}
		// Search directory using extension or filename
		public function searchDir(string $path, array $matches = array(), $useExtension = false) : array {
			$array = array(); $open = $this->openDir($path);
			foreach($open as $key => $value){
				$info = $this->getInfo($value);
				foreach($matches as $match){
					$si = ($useExtension === true) ? (isset($info['extension']) ? $info['extension'] : "") : (isset($info['filename']) ? $info['filename'] : "");
					if (strpos($si, $match) !== false) {$array[] = $value;}
				}
			}
			$open = null; unset($open);
			return $array;
		}
		// PRIVATE METHODS
		// Get the file extension
		private function getExtension(string $arg) : string {if(!empty($arg)){$x=explode(".", $arg); return array_pop($x);} return "";}
		// Remove extension from filename
		private function extractExtension(string $arg) : string {if(!empty($arg)){$x=explode(".", $arg); array_pop($x); if(count($x) > 1){return implode(".", $x);} return implode("", $x); } return "";}
		// Check if it's a stream resource
		private function isStream($handler) : bool {return (!is_null($handler) && !is_bool($handler) && !is_array($handler) && get_resource_type($handler) == "stream") === true ? true : false;}
		// To lock stream resource
		private function lockStream($handler) : bool {if($this->isStream($handler)){if(!flock($handler,  LOCK_EX | LOCK_NB, $wouldBlock)){if($wouldBlock){return false;}else{return false;}}else{return true;}} return false; }
	}