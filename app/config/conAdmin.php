<?php

class Database {
    private $hostname = "74.208.120.207";
    private $database = "bd_admin_apu";
    private $username = "rci";
    private $password = "Rci2019*.*";

    function conectar()
    {
        try {
            $conexion = "mysql:host=".$this->hostname.";dbname=".$this->database;
            $option = [
                PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES=>false,            
            ];
            $pdo = new PDO($conexion,$this->username,$this->password,$option);
            return $pdo;
        } catch (PDOException $e) {
            echo 'Error de Conexion :'.$e->getMessage();
            exit;
        }
    }
}