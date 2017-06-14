<?php

include "test/test_ionic/www/php/loggin_basedatos.php";
include "test/test_ionic/www/php/funcion.php";

/**
 *
 */
function formulaires_dispositivos_charger_dist() {
    $valeurs = array();
    foreach($_POST as $key => $value) {
        $valeurs[$key] = $value;
    }
    return $valeurs;
}

/**
 * No se necesita validar nada
 */
function formulaires_dispositivos_verifier_dist() {
    $erreurs = array();
    return $erreurs;
}

/**
 *
 */
function formulaires_dispositivos_traiter_dist() {
    $conn = conectar();
    foreach ($_POST as $key => $value) {
        $id_usuario = comprobar_usuario($conn, $_COOKIE["token"]);
        if (strpos($key, 'disp_') !== false) {
            $comprobar = $conn->prepare("SELECT * FROM btz_perfil_dispo WHERE id_usuario = :id_usuario and id_dispositivo = :id_dispositivo");
            if ($comprobar->execute(array(":id_usuario" => $id_usuario['id_usuairo'], ":id_dispositivo" => $value))) {
                if ($comprobar->rowCount() == 0) {
                    $insert = $conn->prepare("INSERT INTO btz_perfil_dispo (id_usuario, id_dispositivo) VALUES (:id_usuario, :id_dispositivo)");
                    if ($insert->execute(array(":id_usuario" => $id_usuario['id_usuairo'], ":id_dispositivo" => $value))) {
                        if ($insert->rowCount() > 0) {
                        }
                    }
                }
            }
        }
    }
    return array(
        'message_ok' => 'update'
    );
}