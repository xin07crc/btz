<?php

include 'test/test_ionic/www/php/funcion.php';
/**
 * Perfil socio demografico
 */
function formulaires_perfil_charger_dist() {
    $valeurs = array();
    foreach($_POST as $key => $value) {
        $valeurs[$key] = $value;
    }
    return $valeurs;
}

/**
 * Perfil socio demografico
 */
function formulaires_perfil_verifier_dist() {
    $texto_error = "";
    // Recorro los campos post y compruebo que no esten vacios
    foreach($_POST as $key => $value) {
        if ($value == "") {
            $campo = str_replace("_", " ", $key);
            $texto_error .= "El campo " . $campo . " es obligatorio <br>";
        }
    }
    if (!isset($_COOKIE["token"])) {
        $texto_error .= "No estas logeado <br>";
    }


    if ($texto_error != "") {
        $erreurs['message_erreur'] = $texto_error;
    }
    return $erreurs;
}

/**
 * Perfil socio demografico
 */
function formulaires_perfil_traiter_dist() {
    include 'test/test_ionic/www/php/loggin_basedatos.php';
    $conn = conectar();
    $token = $_COOKIE["token"];
    // Obtengo el id del usuario
    $id_usuario = comprobar_usuario($conn, $token);
    // Compruebo si el token tiene asociado un usuario
    if ($id_usuario["error"] == false) {
        $completado = comprobar_perfil($conn, $id_usuario["id_usuairo"]);
        // Si el error es igual a true es que puede continuar
        if ($completado["error"] == true) {
            // Inserto los datos
            $insertar = insertar_datos($conn, $id_usuario["id_usuairo"], $_POST);
            // Compruebo si se han insertado los datos
            if ($insertar["error"] == false) {
                return array(
                    'message_ok' => 'Profile Completed'
                );
            }
            else {
                return array(
                    'message_erreur' => 'An error has occurred: ' . $insertar["cod"]
                );
            }
        }
        else {
            return array(
                'message_erreur' => 'An error has occurred: ' . $completado["cod"]
            );
        }
    }
    else{
        return array(
            'message_erreur' => 'An error has occurred: ' . $id_usuario["cod"]
        );
    }
}

/**
 * Alamaceno los datos en la base de datos
 * @param $conn
 * @param $id_usuario
 * @param $POST
 * #return array
 */
function insertar_datos($conn, $id_usuario, $POST) {
    $return = array();
    // Datos que se van a insertar
    $valores = array(
        ":id_usuario" => $id_usuario,
        ":AgeRange" => strip_tags($POST["age"]),
        ":Gender" => strip_tags($POST["gender"]),
        ":LocationCountry" => strip_tags($POST["country"]),
        ":PostalZipCode" => strip_tags($POST["postal_code"]),
        ":LevelStudies" => strip_tags($POST["studies"]),
        ":WorkingStatus" => strip_tags($POST["status_work"]),
        ":MaritalStatus" => strip_tags($POST["status_marital"]),
        ":NumberofChildrens" => strip_tags($POST["children"]),
        ":MotherTongue" => strip_tags($POST["mother_language"]),
        ":SecondLanguage" => strip_tags($POST["second_language"]),
        ":FirstInterest" => strip_tags($POST["first_interes"]),
        ":SecondInterest" => strip_tags($POST["second_interes"]),
        ":ThirdInterest" => strip_tags($POST["third_interes"]),
        ":FourthInterest" => strip_tags($POST["fourt_interes"]),
        ":entry_date" => date('m/d/Y h:i:s a', time())
    );

    $insert = $conn->prepare("INSERT into btz_perfilsdc (id_usuario, AgeRange, Gender, LocationCountry, PostalZipCode, LevelStudies, WorkingStatus, MaritalStatus, NumberofChildrens, MotherTongue, SecondLanguage, FirstInterest, SecondInterest, ThirdInterest, FourthInterest, entry_date) VALUES(:id_usuario, :AgeRange, :Gender, :LocationCountry, :PostalZipCode, :LevelStudies, :WorkingStatus, :MaritalStatus, :NumberofChildrens, :MotherTongue, :SecondLanguage, :FirstInterest, :SecondInterest, :ThirdInterest, :FourthInterest, :entry_date)");
    if ($insert->execute($valores)) {
        if ($insert->rowCount() > 0) {
            $return["error"] = false;
        }
        else {
            $return["error"] = true;
            $return["cod"] = 6;
        }
    }
    else {
        $return["error"] = true;
        $return["cod"] = 5;
    }
    return $return;
}