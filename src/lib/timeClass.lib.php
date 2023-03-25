<?php
    /**
     * (c) John Yusuf Habila <Senestro88@gmail.com>
     * 
     * Time class library
     * 
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */
    namespace PHPMaster\lib;
	class timeClass{
		// PRIVATE PROPERTIES
        private $unix = 0;
		// PUBLIC PROPERTIES

        // PUBLIC METHODS
		function __construct(int | string $unix='') {if((int) $unix >= 360){$this->unix = (int) $unix;}}
		function __destruct(){}
		function __call(string $method, array $arguments = array()){
			if(!method_exists(__CLASS__, $method)){
				@set_error_handler(function($errno, $errstr, $errfile, $errline){echo "<div><b>Filename =></b> ".$errfile." <b>Line =></b> ".$errline." <b>Message =></b> ".$errstr."</div>";});
				try{trigger_error("Undefined method:  {$method}");}
	        	finally{restore_error_handler();}
			}
		}
        public function timeAgo(int | string $unix = '', int | string $level = 2) : string {
            $unix = ((int) $unix >= 360) ? (int) $unix : ($this->unix >= 360 ? $this->unix : 360);
            if ((int) $unix >= 360) {
                $date = new \DateTime(); $date->setTimestamp((int) $unix); $date = $date->diff(new \DateTime());
                $array = array_filter(array_combine(array('year', 'month', 'day', 'hour', 'minute', 'second'), explode(',', $date->format('%y,%m,%d,%h,%i,%s')))); // remove empty date values and biuld array
                $array = array_slice($array, 0, (int) $level); // Output only the first x date values
                $lastKey = key(array_slice($array, -1, 1, true)); // Get the last array key
                $timeString = '';
                foreach ($array as $value => $int) {if ($timeString) {$timeString .= $value != $lastKey ? ', ' : ' and ';} /* The 'and' separator */ $sTxt = $int > 1 ? 's' : ''; /* The 's' plural */ $timeString .= $int.' '.$value.''.$sTxt; /* The 'date' value */}
                return $timeString.' ago';
            }
            return "...";
        }
        public function timeOrganized(int | string $unix = '') : string {
            $unix = ((int) $unix >= 360) ? (int) $unix : ($this->unix >= 360 ? $this->unix : 360); $timeString = "";
            $mDate = array("mday"=> date('j'), "mon"=> date('n'), "year"=> date('Y'));
            $uDate = array("mday"=> date('j', $unix), "mon"=> date('n', $unix), "year"=> date('Y', $unix));
            $isYear=", Y";
            $timeString= date("F j".(($uDate['year'] !== $mDate['year']) ? $isYear : '')."", $unix);
            if ($uDate['mday'] == $mDate['mday'] && $uDate['mon'] == $mDate['mon'] && $uDate['year'] == $mDate['year']) {$timeString= date("h:i A", $unix);}
            else if (($uDate['mday'] + 1) == $mDate['mday'] && $uDate['mon'] == $mDate['mon'] && $uDate['year'] == $mDate['year']) {$timeString= "Yesterday";}
            return ucfirst($timeString);
        }
        // PRIVATE METHODS
	}