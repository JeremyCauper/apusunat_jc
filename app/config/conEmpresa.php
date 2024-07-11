<?php
require 'conAdmin.php';

class DatabaseEmpresa {

    function conectar($id)
    {
        try {
            $dbAdmin = new Database();
            $conAdmin = $dbAdmin->conectar();

            $queryValid = $conAdmin->query("SELECT host_base_datos AS 'hsst', base_datos AS 'base', user_base_datos AS 'user', pass_base_datos AS 'pass' FROM tb_empresas WHERE id_empresas = $id");
            $val = $queryValid->fetch(PDO::FETCH_ASSOC);

            $hostname = $val['hsst'];
            $database = $val['base'];
            $username = $val['user'];
            $password = $val['pass'];

            $conexion = "mysql:host=".$hostname.";dbname=".$database;
            $option = [
                PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES=>false,            
            ];
            $pdo = new PDO($conexion,$username,$password,$option);
            return $pdo;
        } catch (PDOException $e) {
            echo 'Error de Conexion :'.$e->getMessage();
            exit;
        }
    }
}