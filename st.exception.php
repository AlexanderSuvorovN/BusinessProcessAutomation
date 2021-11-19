<?php
    /*
    *
    * https://stackoverflow.com/questions/22113541/using-additional-data-in-php-exceptions
    *
    */
    class stException extends Exception 
    {
        private $_data = '';
        public function __construct($message, $data = null) 
        {
            $this->_data = $data;
            parent::__construct($message);
        }
        public function getData()
        {
            return $this->_data;
        }
    }