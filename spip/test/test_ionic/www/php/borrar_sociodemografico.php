<?php
include "loggin_basedatos.php";
include "funcion.php";

$return = array();
// Compruebo que existe el id de dispositivo y esta logeado
if (isset($_POST["id_dispo"]) && isset($_COOKIE["token"])) {
    // Conecto con la Bade De Datos
    $conn = conectar();
    // Obtengo los datos del usuario
    $array_usuario = comprobar_usuario($conn, $_COOKIE["token"]);
    // Almaceno el id de usuario
    $id_usuario = $array_usuario["id_usuairo"];
    // Preparo la consulta
    $borrado = $conn->prepare("DELETE FROM btz_perfil_dispo WHERE id_perfil_dispo = :id_dispo and id_usuario = :id_usuario");
    // Ejecuto la consulta
    if ($borrado->execute(array(":id_dispo" => strip_tags($_POST["id_dispo"]), ":id_usuario" => $id_usuario))) {
        if ($borrado->rowCount() > 0) {
            $return["error"] = false;
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
// Devuelvo el JSON
echo json_encode($return);