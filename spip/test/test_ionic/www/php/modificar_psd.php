<?php
include 'loggin_basedatos.php';
include 'funcion.php';
$conn = conectar();
$return = array();

if (isset($_POST["id"]) && isset($_POST["name"]) && isset($_POST["valor"]) && isset($_COOKIE["token"])) {
    $token = $_COOKIE["token"];
    $id_usuario = comprobar_usuario($conn, $token);
    if ($id_usuario["error"] == false) {
        $completado = comprobar_perfil($conn, $id_usuario["id_usuairo"]);
        if ($completado["error"] == false) {
            $update = actualizar_perfil($conn, strip_tags($_POST["id"]), strip_tags($_POST["name"]), strip_tags($_POST["valor"]), $_COOKIE["token"], $id_usuario["id_usuairo"]);
            if ($update["error"] == false) {
                $return["error"] = false;
            }
            else {
                $return["error"] = true;
                $return["cod"] = $update["cod"];
            }
        }
        else {
            $return["error"] = true;
            $return["cod"] = 3;
        }
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

echo json_encode($return);

/**
 * Modifica los datos de la base de datos
 * @param $conn
 * @param $id
 * @param $name
 * @param $valor
 * @param $token
 * @param $id_usuario
 */
function actualizar_perfil($conn, $id, $name, $valor, $token, $id_usuario) {
    $return = array();
    $valores = array(
        ":valor" => $valor,
        ":id_usuario" => $id_usuario
    );
    $update = $conn->prepare("UPDATE btz_perfilsdc SET $name=:valor WHERE id_usuario=:id_usuario");
    if ($update->execute($valores)) {
        $return["error"] = false;
    }
    else {
        $return["error"] = true;
        $return["cod"] = 5;
    }
    return $return;
}
