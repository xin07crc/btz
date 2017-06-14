<?php
include "loggin_basedatos.php";
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
if (isset($postdata)) {
    $mbd = conectar();
    $return = array();
    $mail = $request->correo;
    $pass = $request->passowod;
    $validado = $request->validado;
    $id = $request->articulo;
    if (isset($request->passowod) && $request->passowod == "AUIbebe01") {
        if ($validado == true) {
            $select = $mbd->prepare("SELECT * from spip_articles where id_article = :articulo");
            if ($select->execute(array(':articulo' => $id))) {
                if ($select->rowCount() > 0) {
                    $update_beta = $mbd->prepare("UPDATE spip_articles set statut = 'publie' where id_article = :articulo");
                    $update_beta->execute(array(
                        "articulo" => $id,
                    ));
                    $correo = enviar_correo($mail, $id);
                    $return["error"] = false;
                    $return["validado"] = true;
                    $return["correo"] = $correo;
                }
                else {
                    $return["error"] = true;
                    $return["cod"] = 1;
                }
            }
        }
        if ($validado == false) {
            $select = $mbd->prepare("SELECT * from spip_articles where id_article = :articulo");
            if ($select->execute(array(':articulo' => $id))) {
                if ($select->rowCount() > 0) {
                    $update_beta = $mbd->prepare("UPDATE spip_articles set statut = 'poubelle' where id_article = :articulo");
                    $update_beta->execute(array(
                        "articulo" => $id,
                    ));
                    $return["error"] = false;
                    $return["validado"] = false;
                }
                else {
                    $return["error"] = true;
                    $return["cod"] = 1;
                }
            }
        }
    }
    else {
        $return["error"] = true;
        $return["cod"] = 0;
    }
    echo json_encode($return);
}


function enviar_correo($correo, $id) {
    if ($_SERVER['HTTP_HOST'] == "localhost") {

    }
    else {
        // organizacion@aui.es
        $mensaje = "Ya es embajador de test de privacidad, para acceder http://testdeprivacidad.com/?embajadores=" . $id;
        $to = strip_tags($correo);
        $de = "type@aui.es";
        // $to = "carlos.ruiz.cues@gmail.com";
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['SCRIPT_URI'];
        $subject = "[TYP] Solicitud de embajador de testdeprivacidad.com";
        $header = "Content-type: text/html\n";
        $header .= "From: mps@aui.es<noreply@$host>\n";
        $mail = mail ($to , $subject , $mensaje, $header, $de);
        return $mail;
    }
}
