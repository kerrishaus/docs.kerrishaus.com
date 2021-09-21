<?php

    class Logger
    {
        static function info($string)
        {
            // nothing for now
        }
        
        function __destruct()
        {
            // group all logs and post to database
        }
        
        private $logs = array();
    }
    
?>