<?php

include "loggin_basedatos.php";
include "funcion.php";

$return = array();

if (isset($_POST["tipo"]) && isset($_POST["so"]) && isset($_POST["version"]) && isset($_POST["marca"]) && isset($_POST["modelo"])) {
    // Creo PDO
    $conn = conectar();
    // Obtengo el token del usuario
    $token = $_COOKIE["token"];
    // Obtengo los datos del usuario
    $array_usuario = comprobar_usuario($conn, $token);
    // Almaceno el id de usuario
    $id_usuario = $array_usuario["id_usuairo"];
    // Meto los datos en un array
    $valores = array(
        ":id_usuario" => $id_usuario,
        ":Type" => strip_tags($_POST["tipo"]),
        ":Brand" => strip_tags($_POST["marca"]),
        ":Model" => strip_tags($_POST["modelo"]),
        ":OperatingSystem" => strip_tags($_POST["so"]),
        ":OSVersion" => strip_tags($_POST["version"]),
    );
    // Preparo la insercion
    $insert = $conn->prepare("INSERT INTO btz_perfil_dispo (id_usuario, Type, Brand, Model, OperatingSystem, OSVersion) VALUES (:id_usuario, :Type, :Brand, :Model, :OperatingSystem, :OSVersion)");
    // Ejecuto la consulta
    if ($insert->execute($valores)) {
        if ($insert->rowCount() > 0) {
            $return["error"] = false;
            $return["id"] = $conn->lastInsertId();
        }
        else {
            $return["error"] = true;
            $return["cod"] = 2;
        }
    }
    else {
        $return["error"] = true;
        $return["cod"] = 1;
    }
}
else {
    $return["error"] = true;
    $return["cod"] = 0;
}

echo json_encode($return);

