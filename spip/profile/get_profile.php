<?php

include "../test/test_ionic/www/php/loggin_basedatos.php";
include "../test/test_ionic/www/php/funcion.php";



// token man -> 333abfa9f784f7cc44ba58307fefc921
// Token Url -> 7f00756a893e7c80932fb78ed7249380

/**
 * Compruebo que
 */
$return = array();
if (isset($_GET["tu"]) && isset($_GET["tpm"])) {
    $conn = conectar();
    // Compruebo que el usuario existe
    $usuario = comprobarUsuarioTokenUrl($conn, strip_tags($_GET["tu"]));
    // Compruebo que el manager existe
    $manager = comprobarUsuarioTokenUrlManager($conn, strip_tags($_GET["tpm"]));
    // Compruebo si ha habido algun error
    if ($usuario["error"] == false && $manager["error"] == false) {
        // Recojo los datos del usuairo
        $datos_usuario = comprobar_perfil_assoc($conn, $usuario["id_usuairo"]);
        $datos_dispo = comprobar_perfil_dispo_assoc($conn, $usuario["id_usuairo"]);
        // Devuelvo los datos
        $return["error"] = false;
        $return["profile"] = $datos_usuario;
        $return["devices"] = $datos_dispo;
        $return["tu"] = strip_tags($_GET["tu"]);
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


