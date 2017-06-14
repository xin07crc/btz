<?php
/**
 * Codigos de error
 *
 * 0 => No estan todos los datos
 * 1 => No existe la cookie
 * 2 => No se han extraido correctamente los datos del usuario o regalo
 * 3 => No tiene suficientes puntos para adquirir el producto
 * 4 => No se ha guardado la informacion del envio en la base de datos
 * 5 => Fallo al actualizar la informacion del usuario
 * 6 => No se han enviado los correos
 */
/**
 * Created by PhpStorm.
 * User: carlo
 * Date: 31/05/2017
 * Time: 10:26
 */
include 'loggin_basedatos.php';
include 'funcion.php';
$return = array();
$conn = conectar();
// SOLO ABIERTO PARA POSTMAN
// $_COOKIE["token"] = "25d5ae635b4ba54cfcf74bee81c3b1e7";
if (isset($_POST["id_regalo"]) && isset($_POST["calle"]) && isset($_POST["postal"]) && isset($_POST["ciudad"])) {
    // Almaceno las variables
    $id_articulo = strip_tags($_POST["id_regalo"]);
    $calle = strip_tags($_POST["calle"]);
    $cod_postal = strip_tags($_POST["postal"]);
    $ciudad = strip_tags($_POST["ciudad"]);
    if (isset($_COOKIE["token"])) {
        $token = strip_tags($_COOKIE["token"]);
        $datos_usuario = comprobar_usuario($conn, $token);
        $datos_regalo = valor_regalo($conn, $id_articulo);

        $puntos_usuario = intval($datos_usuario["puntos"]);
        $puntos_regalo = intval($datos_regalo["puntos"]);


        // Compruebo que no exista un error
        if ($datos_usuario["error"] == false && $datos_regalo["error"] == false) {
            // Compruebo que tiene mas puntos que lo que vale el regalo





            $return["mis_puntos"] = $puntos_usuario;
            $return["mis_puntos_type"] = gettype($puntos_usuario);
            $return["reg_puntos"] = $puntos_regalo;
            $return["reg_puntos_type"] = gettype($puntos_regalo);
            $return["wth"] = $puntos_usuario >= $puntos_regalo;


            if ($puntos_usuario >= $puntos_regalo) {
                $envio_sql = guardar_bdd($conn, $datos_usuario["id_usuairo"], $id_articulo, $calle, $cod_postal, $ciudad);
                if ($envio_sql["error"] == false) {
                    $datos_usuario_final = $datos_usuario["puntos"] - $datos_regalo["puntos"];
                    $update_usuario = actualizar_puntos($conn, $datos_usuario["id_usuairo"], $datos_usuario_final);
                    if ($update_usuario == true) {
                        // Datos mensaje 1
                        $mensaje_1 = "Ha adquirido un producto";
                        $titulo_1 = "[BTZ] - Aquirir Regalo";
                        // Datos mensaje 2
                        $mensaje_2 = "Se ha adquirido un regalo";
                        $titulo_2 = "[BTZ] - Aquirir Regalo";
                        // Envio los correos
                        $mail = enviar_correo_custom("programacion@aui.es", $datos_usuario["mail"], $mensaje_1, $titulo_1);
                        $mail2 = enviar_correo_custom("programacion@aui.es", "programacion@aui.es", $mensaje_2, $titulo_2);
                        // Comprueno que se han enviado los e-mail
                        if ($mail == true && $mail2 == true) {
                            $return["error"] = false;
                        } else {
                            $return["error"] = true;
                            $return["cod"] = 6;
                        }
                    } else {
                        $return["error"] = true;
                        $return["cod"] = 5;
                    }
                } else {
                    $return["error"] = true;
                    $return["cod"] = 4;
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
}
else {
    $return["error"] = true;
    $return["cod"] = 0;

}



echo json_encode($return);


/**
 * @param $conn
 * @param $id_articulo
 */
function valor_regalo($conn, $id_articulo) {
    $return = array();
    $select = $conn->prepare("SELECT * FROM spip_articles WHERE id_article = :id_article");
    if ($select->execute(array(":id_article" => $id_articulo))) {
        if ($select->rowCount() > 0) {
            $return["error"] = false;
            $select = $select->fetchAll();
            $return["puntos"] = $select[0]["soustitre"];
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
 * @param $datos_usuario
 */
function guardar_bdd($conn, $id_usuario, $id_articulo, $calle, $cod_postal, $ciudad) {
    $return = array();
    $values = array(
        ":calle" => $calle,
        ":ciudad" => $ciudad,
        ":cod_postal" => $cod_postal,
        ":id_usuario" => $id_usuario,
        ":id_articulo" => $id_articulo,
    );
    $insert = $conn->prepare("INSERT INTO btz_regalos (calle, ciudad, cod_postal, id_usuario, id_articulo) VALUES (:calle, :ciudad, :cod_postal, :id_usuario, :id_articulo)");
    if ($insert->execute($values)) {
        $return["error"] = false;
    }
    else {
        $return["error"] = true;
    }
    return $return;
}

/**
 * Actualoza los puntos del usuario
 * @param $id_usuario
 * @param $datos_usuario_final
 */
function actualizar_puntos($conn, $id_usuario, $datos_usuario_final) {
    $insert = $conn->prepare("UPDATE registroreto SET puntos = :puntos WHERE id_usuario = :id_usuario");
    if ($insert->execute(array(":puntos" => $datos_usuario_final, ":id_usuario" => $id_usuario))) {
        return true;
    }
    else {
        return false;
    }
}