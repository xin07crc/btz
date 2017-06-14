<?php

include "../test/test_ionic/www/php/loggin_basedatos.php";
include "../test/test_ionic/www/php/funcion.php";

define('LOGIN','aui'); // Usuario SMS
define('PASSW','cprbxy'); // Clave del usuario SMS
define('CCODE','34');   // Contry Code
define('ESPERA', '+1 hour'); // 1 Hora

if (isset($_GET["token"])) {
    $conn = conectar();
    $tokens = explode("|", $_GET["token"]);
    $idioma = $_GET["lang"];
    $t_usu = $tokens[0];
    $t_url = $tokens[1];
    // Compruebo que el usuario existe
    $usuario = comprobar_managuer_tokens($conn, $t_usu, $t_url);
    if ($usuario["error"] == false) {
        // Compruebo si se ha enviado el SMS al usuario
        $comprobar_sms = comprobar_sms_betatester($conn, $usuario["id_usuario"], ESPERA);
        // Si el resultado es false se reenvia el SMS
        if ($comprobar_sms["enviado"] == false) {
            // Envio el SMS
            $codigo = rand(1111, 9999);
            $enviar_sms = enviar_sms($usuario["telefono"], $codigo);
            // Compruebo si ha habido un error al enviar el SMS
            if ($enviar_sms["error"] == false) {
                // Almaceno Los datos en la base de datos
                $almaceno_sms = almaceno_sms_manager($conn, $codigo, $usuario["id_usuario"]);
                // Compruebo si se ha almacenado correctamente
                if ($almaceno_sms["error"] == false) {
                    session_start();
                    $_SESSION["id_usuario"] = $usuario["id_usuario"];
                    redireccionar_manager($t_usu, $t_url);
                }
                else {
                    //echo "No5";
                    gohome("Error", "Ha ocurrido un error, pongase en contacto con secretaria@aui.es");
                }
            }
            else {
                //echo "No4";
                gohome("Error", "No se ha podido enviar el SMS, pongase en contacto con secretaria@aui.es");
            }
        }
        else {
            session_start();
            $_SESSION["id_usuario"] = $usuario["id_usuario"];
            $_SESSION["token"] = $t_usu;
           redireccionar($t_usu, $t_url);
        }
    }
    else {
        //echo "No2";
        gohome("Error", "El usuario no existe");
    }
}
else {
    // echo "No1";
    gohome("Error", "Error al procesar el codigo");
}


/**
 * @param $conn
 * @param $t_usu
 */
function cambiar_status($conn, $t_usu, $t_url) {
    $update = $conn->prepare("UPDATE registroreto SET fecha_validacion = :fecha_validacion, ip_validacion = :ip_validacion, status = 1");

    if ($update->execute(array(":fecha_validacion" => date("Y-m-d H:i:s"), ":ip_validacion" => $_SERVER['REMOTE_ADDR']))) {
        if ($update->rowCount() > 0) {
            return true;
        }
        else {
            return false;
        }
    }
    else {
        return false;
    }
}
/**
 * Envio de correo
 */
function enviar_correo($conn, $t_usu, $t_url, $idioma, $correo) {

    if ($_SERVER["SERVER_NAME"] == "localhost") {
        echo 'http://testdeprivacidad.com/?page=betatester&token=' . $t_usu;
        return true;
    }

    switch($idioma) {
        case "es":
            $id_article = 177;
            break;
        default:
            $id_article = 177;
            break;
    }

    $select = $conn->prepare("SELECT * FROM spip_articles WHERE id_article = :id_article");

    if ($select->execute(array(":id_article" => $id_article))) {
        if ($select->rowCount() > 0) {
            $select = $select->fetchAll();
            foreach ($select as $item) {
                $para = $correo;
                $texto = $item["surtitre"];
                $texto2 = $item["soustitre"];
                $de = $item["descriptif"];
                $titulo = $item["chapo"];
                $mensaje   = $texto;
                $mensaje .= "\r\n";
                $mensaje .= "\r\n";
                $mensaje .= $texto2 . ": ";
                $mensaje .= 'http://testdeprivacidad.com/?page=betatester&token=' . $t_usu;
                $mensaje .= "\r\n";
                $mensaje .= "\r\n";

                $cabeceras = 'From: ' . $de . "\r\n" .
                    'Reply-To: ' . $de . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                $mail = mail($correo, $titulo, $mensaje, $cabeceras);

                return $mail;
            }
        }
    }
    return false;
}


