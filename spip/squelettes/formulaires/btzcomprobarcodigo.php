<?php

/**
 * Created by PhpStorm.
 * User: carlo
 * Date: 23/05/2017
 * Time: 12:21
 */
include "test/test_ionic/www/php/loggin_basedatos.php";
include "test/test_ionic/www/php/funcion.php";

function formulaires_btzcomprobarcodigo_charger_dist($token, $token_url) {
    $valeurs = array();
    foreach($_POST as $key => $value) {
        $valeurs[$key] = $value;
    }
    return $valeurs;
}

function formulaires_btzcomprobarcodigo_verifier_dist($token, $token_url) {
    $texto_error = "";
    if (!$_POST["codigo"]) {
        $texto_error = "El codigo es obligatorio";
    }
    if ($texto_error != "") {
        $erreurs['message_erreur'] = $texto_error;
    }
    return $erreurs;
}

function formulaires_btzcomprobarcodigo_traiter_dist($token, $token_url) {
    session_start();
    $conn = conectar();
    // Obtengo los datos del usuario
    $datos_usu = comprobar_usuario($conn, $token);
    // Si no existe muestra un mensaje de error
    if ($datos_usu["error"] == true) {
        return array(
            'message_erreur' =>"Error200"
        );
    }
    else {
        $id_usuario = $datos_usu["id_usuairo"];
    }

    $codigo = strip_tags($_POST["codigo"]);
    // $select = $conn->prepare("SELECT * FROM btz_enviosms WHERE id_usuario = :id_usuario and codigo = :codigo ORDER BY fecha DESC limit 1");
    $select = $conn->prepare("SELECT * FROM btz_enviosms WHERE id_usuario = :id_usuario ORDER BY fecha DESC limit 1");
    //if ($select->execute(array(":id_usuario" => $id_usuario, ":codigo" => $codigo))) {
    if ($select->execute(array(":id_usuario" => $id_usuario))) {

        if ($select->rowCount() > 0) {
            $select = $select->fetchAll();
            foreach ($select as $item) {
                $codigo_bbdd = $item["codigo"];
            }
            if ($codigo_bbdd == $codigo) {
                $update = $conn->prepare("UPDATE registroreto SET status = 1 WHERE id_usuario = :id_usuario");
                if ($update->execute(array(":id_usuario" => $id_usuario))) {
                    // Sumo puntos al remitente de la invitacion (en el caso de que la haya)
                    sumar_puntos_remitente($conn, $id_usuario);
                    $fecha_mili = $milliseconds = round(microtime(true) * 1000);
                    return array(
                        'message_ok' => 'User activated, go to <a href="?page=btz_realizadas&token=' . $token . '&fl=' . $fecha_mili . '">Betatester</a>'
                    );
                }
            }
            else {
                return array(
                    'message_erreur' =>"The codes do not match"
                );
            }
        }
        else {
            return array(
                'message_erreur' =>"Error3"
            );
        }
    }
    else {
        return array(
            'message_erreur' =>"Error2"
        );
    }
}

/**
 * @param $conn
 * @param $id_usuario_invitado
 */
function sumar_puntos_remitente($conn, $id_usuario_invitado) {
    $select_remitente = $conn->prepare("SELECT * FROM btz_invitaciones WHERE id_invitado = :id_invitado");
    if ($select_remitente->execute(array(":id_invitado" => $id_usuario_invitado))) {
        if ($select_remitente->rowCount() > 0) {
            $select_remitente = $select_remitente->fetchAll();
            // Obtengo el id del remitente
            $id_remitente = $select_remitente[0]["id_remitente"];
            // Obtengo los puntos actuales
            $puntos = obtener_datos_usuario($conn, $id_remitente);
            //  Sumo 20 puntos al remitente
            $puntos_final_remitente = $puntos["datos"][0]["puntos"] + 20;
            // Inserto los puntos en la base de datos
            $insertarPuntos = $conn->prepare("UPDATE registroreto SET puntos = :puntos WHERE id_usuario = :id_usuario");
            if ($insertarPuntos->execute(array(":puntos" => $puntos_final_remitente, ":id_usuario" => $id_remitente))) {
                return true;
            }
        }
    }
    return false;
}
