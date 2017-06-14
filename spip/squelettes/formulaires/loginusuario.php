<?php

include "test/test_ionic/www/php/loggin_basedatos.php";
/**
 * @return array
 */
function formulaires_loginusuario_charger_dist($idioma, $ok, $err1, $err2, $err3, $err4){
    $valeurs = array();
    foreach($_POST as $key => $value) {
        $valeurs[$key] = $value;
    }
    return $valeurs;
}

/**
 * @return array
 */
function formulaires_loginusuario_verifier_dist($idioma, $ok, $err1, $err2, $err3, $err4){
    $erreurs = array();
    $texto_error = "";
    $conn =conectar();
    // Compruebo si los valores estan vacios
    $correo = strip_tags($_POST["email"]);

    // Compruebo si alguno esta sin completar
    if ($correo == "") {
        $texto_error = $err3;
    }
    // Compruebo si el correo es un mail
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $texto_error = $err4;
    }
    // Compruebo si ha habido errores
    if ($texto_error != "") {
        $erreurs['message_erreur'] = $texto_error;
    }
    return $erreurs;
}

/**
 *
 */
function formulaires_loginusuario_traiter_dist($idioma, $ok, $err1, $err2, $err3, $err4){
    $conn = conectar();
    $correo = ofuscar(strip_tags($_POST["email"]));
    $id_mail = strip_tags($_POST["id_email"]);

    $select = $conn->prepare("SELECT * from registroreto WHERE email = :email");
    if ($select->execute(array(":email" => $correo))) {
        if ($select->rowCount() > 0) {
            $select = $select->fetchAll();
            $token = "";
            $fecha_mili = $milliseconds = round(microtime(true) * 1000);
            foreach ($select as $item) {
                $token = $item["token"];
            }
            if ($_SERVER["SERVER_NAME"] == "localhost") {

                echo "http://localhost/typ/spip/?page=btz_realizadas&token=" . $token . "&fl=" . $fecha_mili;
                $mail = true;
            }
            else {
                //$mail = true;

                $mail_query = $conn->prepare("SELECT * FROM spip_articles WHERE id_article=:id_article");
                if ($mail_query->execute(array(":id_article" => $id_mail))) {
                    if ($mail_query->rowCount() > 0) {
                        $mail_query = $mail_query->fetchAll();
                        foreach ($mail_query as $item) {
                            $de = $item["soustitre"];
                            $titulo = $item["surtitre"];
                            $mensaje = $item["texte"];
                            $titulo_enlace = $item["chapo"];
                            $url_site = $item["url_site"];
                            $url_name = $item["nom_site"];
                            $mail = enviar_correo_registro($de, $titulo, $mensaje, $titulo_enlace, $url_site, $url_name, $token, $correo, $token_url, $idioma, $fecha_mili);
                        }
                    }
                    else {
                        $mail = false;
                    }
                }
                else {
                    $mail = false;
                }
            }
            if ($mail == true) {
                return array(
                    'message_ok' => $ok
                );
            }
            else {
                return array(
                    'message_erreur' => $err2
                );
            }
        }
        else {
            return array(
                'message_erreur' => $err1
            );
        }
    }
    else {
        return array(
            'message_erreur' => $err1
        );
    }
}



function ofuscar($dato) {
    return $dato;
}

/**
 * Creo un hash para el token a partir de la fecha
 * Compruebo que no hay otro usuario con ese hash
 * @param $conn
 * @param $date
 */
function crearTokenFecha($conn, $date, $debug) {
    // Paso la fecha a md5
    if ($debug == true) {
        $date = "de783f1f6de9beb06b380d53a12adb04";
    }
    else {
        $date = md5($date);
    }
    $select = $conn->prepare("SELECT * FROM registroreto WHERE token = :token");
    if ($select->execute(array(":token" => $date))) {
        if ($select->rowCount() > 0) {
            // Si hay resultados recalculo
            $new_date = date("Y-m-d H:i:s");
            $new_date = crearTokenFecha($conn, $new_date, false);
            return $new_date;
        }
        else {
            // Devuelvo la fecha en md5
            return $date;
        }
    }
    else {
        $new_date = date("Y-m-d H:i:s");
        $new_date = crearTokenFecha($conn, $new_date, false);
        return $new_date;
    }
}

/**
 * Envia el correo de registro
 */
function enviar_correo_registro($de, $titulo, $texto, $titulo_enlace, $url_site, $url_name, $token, $correo, $token_url, $idioma, $fecha_mili) {
    $para      = $correo;
    $mensaje   = $texto;
    $mensaje .= "\r\n";
    $mensaje .= "\r\n";
    $mensaje .= 'http://betacitizens.com/?page=btz_realizadas&token=' . $token . "&fl=" . $fecha_mili;
    $mensaje .= "\r\n";
    $mensaje .= "\r\n";
    $mensaje .= $url_name . " : " . $url_site;

    $cabeceras = 'From: ' . $de . "\r\n" .
        'Reply-To: ' . $de . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    $mail = mail($correo, $titulo, $mensaje, $cabeceras);
    return $mail;
}