<?php
/** @TODO: esto debe ir en un archivo unico */
define('LOGIN','aui'); // Usuario SMS
define('PASSW','cprbxy'); // Clave del usuario SMS
define('CCODE','34');   // Contry Code
define('ESPERA', '+1 hour'); // 1 Hora

include "test/test_ionic/www/php/loggin_basedatos.php";
include "test/test_ionic/www/php/funcion.php";

/**
 * @return array
 */
function formulaires_loginusuariosms_charger_dist($idioma, $ok, $err1, $err2, $err3, $err4){
    $valeurs = array();
    foreach($_POST as $key => $value) {
        $valeurs[$key] = $value;
    }
    return $valeurs;
}

/**
 * @return array
 */
function formulaires_loginusuariosms_verifier_dist($idioma, $ok, $err1, $err2, $err3, $err4){
    $erreurs = array();
    $texto_error = "";
    $conn =conectar();
    // Compruebo si los valores estan vacios
    $correo = strip_tags($_POST["email"]);

    // Compruebo si alguno esta sin completar
    if ($correo == "") {
        $texto_error = $err3;
    }
    // Compruebo si es numero de telefono o correo
    if (is_numeric($correo)) {
        //@TODO: Comprobar si es un telefono
    }
    else {
        // Compruebo si es un correo valido
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $texto_error = $err4;
        }
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
function formulaires_loginusuariosms_traiter_dist($idioma, $ok, $err1, $err2, $err3, $err4){
    $conn = conectar();
    $correo = ofuscar(strip_tags($_POST["email"]));
    $id_mail = strip_tags($_POST["id_email"]);
    $correo = strip_tags($_POST["email"]);


    if (is_numeric($correo)) {
        $select = $conn->prepare("SELECT * FROM registroreto WHERE telefono = :telefono");
        if ($select->execute(array(":telefono" => $correo))) {
            if ($select->rowCount() > 0) {
                $select = $select->fetchAll();
                foreach ($select as $item) {
                    $id_usuario = $item["id_usuario"];
                    $t_usu = $item["token"];
                    $t_url = $item["token_url"];
                }
                // Compruebo si se ha enviado el SMS al usuario
                $comprobar_sms = comprobar_sms($conn, $id_usuario, ESPERA);
                // Si el resultado es false se reenvia el SMS
                if ($comprobar_sms["enviado"] == false) {
                    // Envio el SMS
                    $codigo = rand(1111, 9999);
                    $enviar_sms = enviar_sms($correo, $codigo);
                    // Compruebo si ha habido un error al enviar el SMS
                    if ($enviar_sms["error"] == false) {
                        // Almaceno Los datos en la base de datos
                        $almaceno_sms = almaceno_sms($conn, $codigo, $id_usuario);
                        // Compruebo si se ha almacenado correctamente
                        if ($almaceno_sms["error"] == false) {
                            session_start();
                            $_SESSION["id_usuario"] = $id_usuario;
                            redireccionar($t_usu, $t_url);
                        } else {
                            //echo "No5";
                            gohome("Error", "Ha ocurrido un error, pongase en contacto con secretaria@aui.es");
                        }
                    } else {
                        //echo "No4";
                        gohome("Error", "No se ha podido enviar el SMS, pongase en contacto con secretaria@aui.es");
                    }
                }
                else {
                    echo "Si";
                    session_start();
                    $_SESSION["id_usuario"] = $id_usuario;
                    $_SESSION["token"] = $t_usu;
                    redireccionar($t_usu, $t_url);
                }
            }
            else {
                return array(
                    'message_erreur' => "El usuario no existe1"
                );
            }
        }
        else {
            return array(
                'message_erreur' => "El usuario no existe"
            );
        }
    }
    if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
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
                    echo "http://localhost/typ/spip/?page=betatester&token=" . $token . "&fl=" . $fecha_mili;
                    $mail = true;
                } else {
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
                        } else {
                            $mail = false;
                        }
                    } else {
                        $mail = false;
                    }
                }
                if ($mail == true) {
                    return array(
                        'message_ok' => $ok
                    );
                } else {
                    return array(
                        'message_erreur' => $err2
                    );
                }
            } else {
                return array(
                    'message_erreur' => $err1
                );
            }
        } else {
            return array(
                'message_erreur' => $err1
            );
        }
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