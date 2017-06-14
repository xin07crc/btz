<?php

include "test/test_ionic/www/php/loggin_basedatos.php";
/**
 * @return array
 */
function formulaires_registrousuario_charger_dist($idioma, $ok, $err1, $err2, $err3, $err4, $err5, $error6){
    $valeurs = array();
    foreach($_POST as $key => $value) {
        $valeurs[$key] = $value;
    }
    return $valeurs;
}

/**
 * @return array
 */
function formulaires_registrousuario_verifier_dist($idioma, $ok, $err1, $err2, $err3, $err4, $err5, $error6){
    session_start();
    include_once './test/test_ionic/www/lib/securimage/securimage.php';
    $securimage = new Securimage();

    $erreurs = array();
    $texto_error = "";
    $conn =conectar();
    // Compruebo si los valores estan vacios
    $correo = strip_tags($_POST["email"]);
    $nombre = strip_tags($_POST["nombre"]);
    $apellido = strip_tags($_POST["apellido"]);
    $tyc = strip_tags($_POST["tyc"]);
    $catpcha = $_POST["captcha_code"];
    //
    if ($securimage->check($catpcha) == false) {
        $texto_error = $error6;
    }
    if ($tyc != "on") {
        $texto_error = $err5;
    }
    // Compruebo si alguno esta sin completar
    if ($correo == "" || $nombre == "" || $apellido == "") {
        $texto_error = $err4;
    }
    // Compruebo si el correo es un mail
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $texto_error = $err3 . $correo;
    }
    // Compruebo si el correo esta en uso
    // Ofusco el correo (para comprobarlo)
    $correo = ofuscar($correo);

    $select = $conn->prepare("SELECT * from registroreto WHERE email = :email");
    if ($select->execute(array(":email" => $correo))) {
        if ($select->rowCount() > 0) {
            $texto_error = $err2;
        }
    }
    else {
        $texto_error = $err2;
    }
    //
    if ($texto_error != "") {
        $erreurs['message_erreur'] = $texto_error;
    }
    return $erreurs;
}

/**
 *
 */
function formulaires_registrousuario_traiter_dist($idioma, $ok, $err1, $err2, $err3, $err4, $err5, $error6){
    $conn =conectar();
    $correo = ofuscar(strip_tags($_POST["email"]));
    $nombre = ofuscar(strip_tags($_POST["nombre"]));
    $apellido = ofuscar(strip_tags($_POST["apellido"]));
    $date = date("Y-m-d H:i:s");
    $id_mail = strip_tags($_POST["id_email"]);
    // Creo el token para acceder
    $token = crearTokenFecha($conn, strip_tags($_POST["email"]), false);
    // Creo el token para la url
    $token_url = crearTokenUrl($conn, strip_tags($_POST["email"]), true);
    // Inserto los datos en la base de datos
    $insert = $conn->prepare("INSERT INTO registroreto (nombre, apellido, email, fecha, token, token_url, ip_registro) VALUES(:nombre, :apellidos, :email, :fecha, :token, :token_url, :ip_registro)");
    $valores = array(
        ":nombre" => $nombre,
        ":apellidos" => $apellido,
        ":email" => $correo,
        ":fecha" => $date,
        ":token" => $token,
        ":token_url" => $token_url,
        ":ip_registro" => $_SERVER['REMOTE_ADDR']
    );
    if ($insert->execute($valores)) {
        if ($insert->rowCount() > 0) {
            if ($_SERVER["SERVER_NAME"] == "localhost") {
                echo 'http://localhost/typ/spip/validar/validar.php?token=' . $token . "|" . $token_url . "&lang=" . $idioma;
                $mail = true;
            }
            else {
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
                            $mail = enviar_correo_registro($de, $titulo, $mensaje, $titulo_enlace, $url_site, $url_name, $token, $correo, $token_url, $idioma);
                            // echo "MAIL: " . $mail;
                        }
                    }
                    else {
                        $mail = false;
                        //echo $conn->errorInfo();
                    }
                }
                else {
                    $mail = false;
                    //echo $conn->errorInfo();
                }
            }
            if ($mail == true) {
                return array(
                    'message_ok' => $ok
                );
            }
            else {
                // var_dump($conn->errorInfo());
                return array(
                    'message_erreur' => $err1 . " 3"
                );
            }
        }
        else {
            return array(
                'message_erreur' => $err1 . " 2"
            );
        }
    }
    else {
        return array(
            'message_erreur' => $err1 . " 1"
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
function crearTokenFecha($conn, $correo, $debug) {
    // echo $correo . "<br>";
    // Paso la fecha a md5
    if ($debug == true) {
        $token = "539dc8842afaeb585519291fdd13725e";
    }
    else {
        $token_sin = $correo . rand(0, 900);
        // echo $token_sin . "<br>";
        $token = md5($token_sin);
    }
    // echo $token . "<br>";
    $select = $conn->prepare("SELECT * FROM registroreto WHERE token = :token");
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


function crearTokenUrl($conn, $correo, $debug) {
    // echo $correo . "<br>";
    // Paso la fecha a md5
    if ($debug == true) {
        $token = "0d5b0b0d51f8775b78ab6ca0826041b0";
    }
    else {
        $token_sin = $correo . rand(0, 900);
        // echo $token_sin . "<br>";
        $token = md5($token_sin);
    }
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

/**
 * Envia el correo de registro
 */
function enviar_correo_registro($de, $titulo, $texto, $titulo_enlace, $url_site, $url_name, $token, $correo, $token_url, $idioma) {
    /*$para = $correo;
    $titulo = $titulo;
    $mensaje = $texto;
    $mensaje .= $titulo_enlace;
    $mensaje .= 'http://testdeprivacidad.com/?page=betatester&token=' . $token;
    $mensaje .= '<a href="' . $url_site . '">' . $url_name . '</a>';
    // To send HTML mail, the Content-type header must be set
    $cabeceras  = 'MIME-Version: 1.0' . "\r\n";
    $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

    $cabeceras .= 'From: '. $de . "\r\n" .
        'Reply-To: ' . $de . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    $mail = mail($para, $titulo, $mensaje, $cabeceras);
    */
    /*
    $para      = 'nobody@example.com';
    $titulo    = 'El título';
    $mensaje   = 'Hola';
    $cabeceras = 'From: webmaster@example.com' . "\r\n" .
        'Reply-To: webmaster@example.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    $mail = mail($correo, $titulo, $mensaje, $cabeceras);
    */

    $para      = $correo;
    $mensaje   = $texto;
    $mensaje .= "\r\n";
    $mensaje .= "\r\n";
    $mensaje .= 'http://betacitizens.com/validar/validar.php?token=' . $token . "|" . $token_url . "&lang=" . $idioma;
    $mensaje .= "\r\n";
    $mensaje .= "\r\n";
    $mensaje .= $url_name . " : " . $url_site;

    $cabeceras = 'From: ' . $de . "\r\n" .
        'Reply-To: ' . $de . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    $mail = mail($correo, $titulo, $mensaje, $cabeceras);
    return $mail;
}