<?php

class Conexao {

    public static $instance;

    private function __construct() {
        //
    }

    public static function getInstance() {
        if (!isset(self::$instance)) {
            
            $servername = "localhost";
            $username = "root";
            $password = "";
            $database = "adm_eletronicos";
            
            self::$instance = new PDO("mysql:host=$servername;dbname=$database", $username, '', 
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        }

        return self::$instance;
    }

}

