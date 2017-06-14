<?php
/**
 * carga
 */
function formulaires_contactocustom_charger_dist(){
    $valeurs = array('email'=>$_POST["email"],'message'=>$_POST["message"]);

    return $valeurs;
}
/**
 * Verificar
 * @return array
 */
function formulaires_contactocustom_verifier_dist(){
    session_start();
    include_once './test/test_ionic/www/lib/securimage/securimage.php';
    $securimage = new Securimage();
    $erreurs = array();
    // verificar que los campos obligatorios están llenos :
    foreach(array('email','message') as $obligatoire)
        if (!_request($obligatoire)) $erreurs[$obligatoire] = 'Este campo es obligatorio';

    // verificar que el email ingresado es válido :
    include_spip('inc/filtres');
    if (_request('email') AND !email_valide(_request('email'))) {
        $erreurs['email'] = 'La dirección de correo ingresada no es válida';
    }
    if (count($erreurs)) {
        $erreurs['message_erreur'] = 'Los datos ingresados contienen errores!';
    }
    foreach($_POST as $key => $value) {
        if ($key == "captcha_code") {
            if ($securimage->check($_POST['captcha_code']) == false) {
                $erreurs['message_erreur'] = "Error en el captcha";
            }
        }
    }
    return $erreurs;
}

/**
 * Tratamiento
 */
function formulaires_contactocustom_traiter_dist(){
    if ($_SERVER['HTTP_HOST'] == "localhost") {
        return array('message_ok'=>'Correo enviado de localhost');
    }
    else {
        $enviar_mail = charger_fonction('envoyer_mail','inc');
        $email_para = "typ@aui.es";
        $email_de = $_POST["email"];
        $asunto = '[TYP] Formulario de contacto: ' . $_POST['asunto'];
        $mensaje = $_POST["email"];
        $mail = mail($email_para,$asunto,$mensaje,$email_de);
        if ($mail == 1) {
            return array(
                'message_ok' => 'Correo enviado de ' . $email_de . ''
                );
        }
        else {
            return array(
                'message_erreur' => 'No se ha podido enviar el correo'
            );
        }
    }
}