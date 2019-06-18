<?php
    class db{
        //Properties
        private $host = 'localhost';
        private $user = 'user';
        private $pass = 'password';
        private $dbname = 'database';


        // Connect
        public function connect(){
            $mysql_connect_str = "mysql:host=$this->host;dbname=$this->dbname";
            $dbConnection = new PDO($mysql_connect_str, $this->user, $this->pass);

            $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $dbConnection;
        }
    }