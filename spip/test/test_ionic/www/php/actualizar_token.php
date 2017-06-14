<?php
/**
 * Created by PhpStorm.
 * User: carlo
 * Date: 31/05/2017
 * Time: 16:28
 */
include "loggin_basedatos.php";
include "funcion.php";

$return = array();
$conn = conectar();

if (isset($_COOKIE["token"])) {
    $token = $_COOKIE["token"];
    $update = update_token_url($conn, $token);
    if ($update["error"] == false) {
        $return["error"] = false;
        $return["token_url"] = $update["token_url"];
    }
    else {
        $return["error"] = true;
    }
}
else {
    $return["error"] = true;
    $return["cod"] = 0;
}



echo json_encode($return);
/**
 * Cambio el token del usuairo
 * @param $conn
 * @param $token
 */
function update_token_url($conn, $token) {
    $return = array();

    $date = new DateTime();
    $date = $date->getTimestamp();

    $token_url = crearTokenUrl2($conn, $date);
    // Actualizo la url
    $update = $conn->prepare("UPDATE registroreto SET token_url = :token_url WHERE token = :token");

    if ($update->execute(array(":token_url" => $token_url, ":token" => $token))) {
        if ($update->rowCount() > 0) {
            $return["error"] = false;
            $return["token_url"] = $token_url;
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

    return $return;
}


/**
 * @param $conn
 * @param $correo
 * @param $debug
 * @return bool|string
 */
function crearTokenUrl2($conn, $correo) {
    $token_sin = $correo . rand(0, 900);
    // echo $token_sin . "<br>";
    $token = md5($token_sin);
    // echo $token . "<br>";
    $select = $conn->prepare("SELECT * FROM registroreto WHERE token_url = :token");
    if ($select->execute(array(":token" => $token))) {
        if ($select->rowCount() > 0) {
            // Si hay resultados recalculo
            $token_sin = $correo . rand(0, 900);
            $new_token = crearTokenFecha($conn, $correo, false);
            return $new_token;
        }
        else {
            // Devuelvo el token en md5
            return $token;
        }
    }
    else {
        // Si hay resultados recalculo
        $token_sin = $correo . rand(0, 900);
        $new_token = crearTokenFecha($conn, $correo, false);
        return $new_token;
    }
}