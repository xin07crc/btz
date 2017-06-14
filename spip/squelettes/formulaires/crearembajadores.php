<?php
/**
 * Carga los datos del formulario
 * @return array
 */
function formulaires_crearembajadores_charger_dist() {
    $valeurs = array();
    foreach($_POST as $key => $value) {
        $valeurs[$key] = $value;
    }
    return $valeurs;
}

/**
 * verfifica que todos los campos estan correctos
 */
function formulaires_crearembajadores_verifier_dist() {
    session_start();
    include_once './test/test_ionic/www/lib/securimage/securimage.php';
    $securimage = new Securimage();
    $erreurs = array();
    $texto_error = "";
    foreach($_POST as $key => $value) {
        if ($key != "page" && $key != "formulaire_action" && $key != "formulaire_action_args" && $key != "acronimo" && $key != "telefono") {
            if ($value == "") {
                $texto_error .= "El campo " . $key . " no puede estar vacio<br>";
            }
        }
        if ($key == "mail") {
            if (strpos($value, '@') != true) {
                $texto_error .= "El mail debe de tener la siginete estructura nombre@servidorCorreo.dominio";
            }
        }
        if ($key == "captcha_code") {
            if ($securimage->check($_POST['captcha_code']) == false) {
                $texto_error .= "Error en el captcha";
            }
        }
    }
    if (empty($_FILES["imagen"])) {
        $texto_error .= "La imagen es obligatoria<br>";
    }
    if ($texto_error != "") {
        $erreurs['message_erreur'] = $texto_error;
    }
    return $erreurs;
}

/**
 * Trata los datos
 */
function formulaires_crearembajadores_traiter_dist() {
    $valores = array(
        "soustitre" => strip_tags($_POST["acronimo"]),
        "id_rubrique" => 27,
        "titre" => strip_tags($_POST["nombre"]),
        "texte" => strip_tags($_POST["nombre_corporativo"]),
        "descriptif" => strip_tags($_POST["telefono"]),
        "chapo" => strip_tags($_POST["mail"]),
        "surtitre" => strip_tags($_POST["enlace"]),
        "statut" => "prop"
    );
    $id_article = sql_insertq("spip_articles", $valores);
    //print_r($_FILES['imagen']);
    if ($id_article > 0) {
        $nombre_completo = explode(".", $_FILES['imagen']['name']);
        $ext = $nombre_completo[1];

        $rename = rename($_FILES['imagen']['tmp_name'], "IMG/tmp" . $id_article . "." . $ext);
        // Cambio los permisos de la imagen
        chmod("IMG/tmp" . $id_article . "." . $ext, 0666);
        // Include the upload class
        include('class.upload.php');

        // Initiate the upload object based on the uploaded file field
        $handle = new upload("IMG/tmp" . $id_article . "." . $ext);

        // Only proceed if the file has been uploaded
        if($handle->uploaded) {
            // Get size of image
            $width = $handle->image_src_x;
            $height = $handle->image_src_y;
            // Set the width of the image
            if ($width > $height) {
                $handle->image_x = 150;
                // Ensure the height of the image is calculated based on ratio
                $handle->image_ratio_y = true;
            }
            if ($width == $height) {
                $handle->image_x = 75;
                // Ensure the height of the image is calculated based on ratio
                $handle->image_ratio_y = true;
            }
            if ($width < $height) {
                $handle->image_y = 75;
                // Ensure the height of the image is calculated based on ratio
                $handle->image_ratio_x = true;
            }

            // Set the new filename of the uploaded image
            $handle->file_new_name_body   = "arton" . $id_article;
            // Make sure the image is resized
            $handle->image_resize         = true;
            // Process the image resize and save the uploaded file to the directory
            $handle->process('IMG');
            // Proceed if image processing completed sucessfully
            if($handle->processed) {
                // Your image has been resized and saved
                $img_subida = 1;
                // Reset the properties of the upload object
                $handle->clean();
            }else{
                // Write the error to the screen
                // echo 'error : ' . $handle->error;
                $img_subida = 0;
            }
        }
        if ($img_subida == 1) {
            enviar_correo_confirmacion($id_article);
            return array(
                'message_ok' => 'Solicitud enviada pendiente de validacion'
            );
        }
        else {
            return array(
                'message_erreur' => 'Ha habido un problema con la carga de la imagen'
            );
        }
    }
    else {
        return array(
            'message_erreur' => 'Error al completar el emabjador'
        );
    }

}


function enviar_correo_confirmacion($id_article) {
    if ($_SERVER['HTTP_HOST'] == "localhost") {

    }
    else {
        $enviar_mail = charger_fonction('envoyer_mail','inc');
        $email_para = "organizacion@aui.es";
        $email_de = $_POST["mail"];
        $asunto = '[TYP] Nuevo embajador';
        $mensaje = "Se ha añadido un embajador, sige este enlace para validarlo o rechazarlo http://testdeprivacidad.com/index.php?page=validar_embajador&id_article=" . $id_article;
        $mail = mail($email_para,$asunto,$mensaje,$email_de);
    }
}

function iniciales($palabra) {
    $iniciales = "";
    $nombre_completo = explode(" ", $palabra);
    foreach ($nombre_completo as $item) {
        $iniciales .= substr($item, 0, 1);
    }
    return $iniciales;
}