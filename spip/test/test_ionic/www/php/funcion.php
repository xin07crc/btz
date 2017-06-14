<?php
/***********************************************************************************************************
 * FUNCIONES DE BETATESTER
 * Funciones comunes para betatester
 *
 *
 *
 *
 ************************************************************************************************************/

/**
 * Compruebo el usuairo por el token url
 * @param $conn
 * @param $token_url
 */
function comprobarUsuarioTokenUrl($conn, $token_url) {
    $return = array();
    $select = $conn->prepare("SELECT id_usuario FROM registroreto WHERE token_url = :token_url");
    if ($select->execute(array(":token_url" => $token_url))) {
        if ($select->rowCount() > 0) {
            $select = $select->fetchAll();
            $return["error"] = false;
            $return["id_usuairo"] = $select[0]["id_usuario"];
        }
        else {
            $return["error"] = true;
            $return["cod"] = 5;
        }
    }
    else {
        $return["error"] = true;
        $return["cod"] = 4;
    }
    return $return;
}

/**
 * Compruebo el usuairo por el token url
 * @param $conn
 * @param $token_url
 */
function comprobarUsuarioTokenUrlManager($conn, $token_url) {
    $return = array();
    $select = $conn->prepare("SELECT id_usuario FROM btz_managuer WHERE token_url = :token_url");
    if ($select->execute(array(":token_url" => $token_url))) {
        if ($select->rowCount() > 0) {
            $select = $select->fetchAll();
            $return["error"] = false;
            $return["id_usuairo"] = $select[0]["id_usuario"];
        }
        else {
            $return["error"] = true;
            $return["cod"] = 5;
        }
    }
    else {
        $return["error"] = true;
        $return["cod"] = 4;
    }
    return $return;
}

/**
 * COmprueba el usuario
 * @param $token
 * @return array
 *
 *
 *
 */
function comprobar_usuario($conn, $token) {
    $return = array();
    $select = $conn->prepare("SELECT id_usuario, puntos, email FROM registroreto WHERE token = :token");
    if ($select->execute(array(":token" => $token))) {
        if ($select->rowCount() > 0) {
            $select = $select->fetchAll();
            $return["error"] = false;
            $return["id_usuairo"] = $select[0]["id_usuario"];
            $return["puntos"] = $select[0]["puntos"];
            $return["mail"] = $select[0]["email"];
        }
        else {
            $return["error"] = true;
            $return["cod"] = 5;
        }
    }
    else {
        $return["error"] = true;
        $return["cod"] = 4;
    }
    return $return;
}
/**
 * COmprueba el usuario
 * @param $token
 * @return array
 *
 *
 *
 */
function comprobar_usuario_managuer($conn, $token) {
    $return = array();
    $select = $conn->prepare("SELECT id_usuario, email FROM btz_managuer WHERE token = :token");
    if ($select->execute(array(":token" => $token))) {
        if ($select->rowCount() > 0) {
            $select = $select->fetchAll();
            $return["error"] = false;
            $return["id_usuairo"] = $select[0]["id_usuario"];
            $return["mail"] = $select[0]["email"];
        }
        else {
            $return["error"] = true;
            $return["cod"] = 5;
        }
    }
    else {
        $return["error"] = true;
        $return["cod"] = 4;
    }
    return $return;
}
/**
 * Compruebo si ya ha completado un perfil de usuario
 * @param $conn
 * @param $token
 *
 *
 *
 * TRUE --> Puede continuar
 * FALSE --> No puede continuar
 */
function comprobar_perfil($conn, $id_usuario) {
    $return  = array();
    $select = $conn->prepare("SELECT * FROM btz_perfilsdc WHERE id_usuario = :id_usuario");
    if ($select->execute(array(":id_usuario" => $id_usuario))) {
        if ($select->rowCount() > 0) {
            $select = $select->fetchAll();
            $return["perfil"] = $select[0];
            $return["error"] = false;
        }
        else {
            $return["error"] = true;
        }
    }
    else {
        $return["error"] = false;
        $return["cod"] = 3;
    }
    return $return;
}



/**
 * Compruebo si ya ha completado un perfil sociodemogradico
 * Devuelvo los resultados en un array asociativo
 * @param $conn
 * @param $token
 *
 *
 *
 * TRUE --> Puede continuar
 * FALSE --> No puede continuar
 */
function comprobar_perfil_assoc($conn, $id_usuario) {
    $return  = array();
    $select = $conn->prepare("SELECT AgeRange, Gender, LocationCountry, PostalZipCode, LevelStudies, WorkingStatus, MaritalStatus, NumberofChildrens, MotherTongue, SecondLanguage, FirstInterest, SecondInterest, ThirdInterest, FourthInterest, entry_date FROM btz_perfilsdc WHERE id_usuario = :id_usuario");
    if ($select->execute(array(":id_usuario" => $id_usuario))) {
        if ($select->rowCount() > 0) {
            $select = $select->fetch(PDO::FETCH_ASSOC);
            $return["profile"] = $select;
            $return["error"] = false;
        }
        else {
            $return["error"] = true;
        }
    }
    else {
        $return["error"] = false;
        $return["cod"] = 3;
    }
    return $return;
}
/**
 * Compruebo si ya ha completado un perfil de dispositivos
 * Devuelvo los resultados en un array asociativo
 * @param $conn
 * @param $token
 *
 *
 *
 * TRUE --> Puede continuar
 * FALSE --> No puede continuar
 */
function comprobar_perfil_dispo_assoc($conn, $id_usuario) {
    $return  = array();
    $select = $conn->prepare("SELECT Type, Brand, Model, OperatingSystem, OSVersion FROM btz_perfil_dispo WHERE id_usuario = :id_usuario");
    if ($select->execute(array(":id_usuario" => $id_usuario))) {
        if ($select->rowCount() > 0) {
            // Creo array para almacenar los dispositivos
            $dispositivos = array();
            // Recorro el resultado asociando los index
            while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
                // Metro el resultado en el array de dispositivos
                array_push($dispositivos, $row);
            }
            $return["device"] = $dispositivos;
            $return["error"] = false;
        }
        else {
            $return["error"] = true;
        }
    }
    else {
        $return["error"] = false;
        $return["cod"] = 3;
    }
    return $return;
}

/**
 *
 */
function comprobar_usuario_tokens($conn, $token, $token_url) {
    $return = array();
    $select = $conn->prepare("SELECT * FROM registroreto WHERE token = :token and token_url = :token_url and status = 0");
    if ($select->execute(array(":token" => $token, ":token_url" => $token_url))) {
        if ($select->rowCount() > 0) {
            $return["error"] = false;
            $select = $select->fetchAll();
            foreach ($select as $item) {
                $return["email"] = $item["email"];
                $return["telefono"] = $item["telefono"];
                $return["id_usuario"] = $item["id_usuario"];
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
    return $return;
}
/**
 *
 */
function comprobar_managuer_tokens($conn, $token, $token_url) {
    $return = array();
    $select = $conn->prepare("SELECT * FROM btz_managuer WHERE token = :token and token_url = :token_url and status = 0");
    if ($select->execute(array(":token" => $token, ":token_url" => $token_url))) {
        if ($select->rowCount() > 0) {
            $return["error"] = false;
            $select = $select->fetchAll();
            foreach ($select as $item) {
                $return["email"] = $item["email"];
                $return["telefono"] = $item["telefono"];
                $return["id_usuario"] = $item["id_usuario"];
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
    return $return;
}


/**
 * Obtiene los datos del usuario
 */
function obtener_datos_usuario($conn, $id_usuario) {
    $return = array();
    $select = $conn->prepare("SELECT * FROM registroreto WHERE id_usuario = :id_usuario");
    if ($select->execute(array(":id_usuario" => $id_usuario))) {
        if ($select->rowCount() > 0) {
            $return["error"] = false;
            $return["datos"] = array();
            $return["datos"] = $select->fetchAll();
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
 * Envio de correos custom
 */
function enviar_correo_custom($de, $correo, $mensaje, $titulo) {
    if ($_SERVER["SERVER_NAME"] == "localhost") {
        return true;
    }
    else {
        $cabeceras = 'From: ' . $de . "\r\n" .
            'Reply-To: ' . $de . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        $mail = mail($correo, $titulo, $mensaje, $cabeceras);
        return $mail;
    }
}

/**
 * Redirecciona
 */
function redireccionar($t_usu, $t_url) {
    if ($_SERVER["SERVER_NAME"] == "localhost") {
        echo "http://localhost/typ/spip/?page=btz_comprobarcodigo&token=" . $t_usu . "&token_url=" . $t_url;
    }
    else {
        header("Location: https://betacitizens.com?page=btz_comprobarcodigo&token=" . $t_usu . "&token_url=" . $t_url);
    }
    die();
}
/**
 * Redirecciona
 */
function redireccionar_manager($t_usu, $t_url) {
    if ($_SERVER["SERVER_NAME"] == "localhost") {
        echo "http://localhost/typ/spip/?page=btz_comprobarcodigomanaguer&token=" . $t_usu . "&token_url=" . $t_url;
    }
    else {
        header("Location: http://betacitizens.com?page=btz_comprobarcodigomanaguer&token=" . $t_usu . "&token_url=" . $t_url);
    }
    die();
}
/**
 * LLeva a testdeprivivacidad
 */
function gohome($titulo, $mensaje_espacios) {
    $mensaje = preg_replace("/\s/", "_", $mensaje_espacios);
    header("Location: http://betacitizens.com?page=betatester&smsterror=" . $titulo . "&smsmerror=" . $mensaje);
    die();
}
/**
 * Almaceno el codigo del SMS en la base de datos junto al usuario
 * @param $conn
 * @param $codigo
 */
function almaceno_sms($conn, $codigo, $id_usuario) {
    $return = array();
    $insert = $conn->prepare("INSERT INTO btz_enviosms (id_usuario, codigo, fecha) VALUES (:id_usuario, :codigo, :fecha)");

    if ($insert->execute(array(":id_usuario" => $id_usuario, ":codigo" => $codigo, ":fecha" => date("Y-m-d H:i:s")))) {
        if ($insert->rowCount() > 0) {
            $return["error"] = false;
        }
        else {
            $return["error"] = true;
        }
    }
    else {
        $return["error"] = true;
    }

    return $return;
}
/**
 * Almaceno el codigo del SMS en la base de datos junto al usuario
 * @param $conn
 * @param $codigo
 */
function almaceno_sms_manager($conn, $codigo, $id_usuario) {
    $return = array();
    $insert = $conn->prepare("INSERT INTO btz_enviosms (id_managuer, codigo, fecha) VALUES (:id_usuario, :codigo, :fecha)");

    if ($insert->execute(array(":id_usuario" => $id_usuario, ":codigo" => $codigo, ":fecha" => date("Y-m-d H:i:s")))) {
        if ($insert->rowCount() > 0) {
            $return["error"] = false;
        }
        else {
            $return["error"] = true;
        }
    }
    else {
        $return["error"] = true;
    }

    return $return;
}
/**
 * Compruebo si se ha enviado un SMS al usuario en menos del tiempo de espera
 * @param $conn
 * @param $usuario
 */
function comprobar_sms($conn, $usuario, $espera) {
    $return = array();
    $select = $conn->prepare("SELECT * FROM btz_enviosms WHERE id_usuario = :id_usuario ORDER BY fecha DESC limit 1"); //ORDER BY `btz_enviosms`.`fecha` DESC
    if ($select->execute(array(":id_usuario" => $usuario))) {
        if ($select->rowCount() > 0) {
            $select = $select->fetchAll();
            foreach ($select as $item) {
                $fecha_envio = $item["fecha"];
            }
            $fecha_ahora = date("Y-m-d H:i:s");
            $fecha_envio = strtotime ($espera, strtotime($fecha_envio ));
            $fecha_envio = date ('Y-m-d H:i:s', $fecha_envio);
            if ($fecha_envio >= $fecha_ahora) {
                $return["enviado"] = true;
            }
            else {
                $return["enviado"] = false;
            }
        }
        else {
            $return["enviado"] = false;
        }
    }
    else {
        $return["enviado"] = false;
    }
    return $return;
}
/**
 * Compruebo si se ha enviado un SMS al usuario en menos del tiempo de espera
 * @param $conn
 * @param $usuario
 */
function comprobar_sms_betatester($conn, $usuario, $espera) {
    $return = array();
    $select = $conn->prepare("SELECT * FROM btz_enviosms WHERE id_managuer = :id_usuario ORDER BY fecha DESC limit 1"); //ORDER BY `btz_enviosms`.`fecha` DESC
    if ($select->execute(array(":id_usuario" => $usuario))) {
        if ($select->rowCount() > 0) {
            $select = $select->fetchAll();
            foreach ($select as $item) {
                $fecha_envio = $item["fecha"];
            }
            $fecha_ahora = date("Y-m-d H:i:s");
            $fecha_envio = strtotime ($espera, strtotime($fecha_envio ));
            $fecha_envio = date ('Y-m-d H:i:s', $fecha_envio);
            if ($fecha_envio >= $fecha_ahora) {
                $return["enviado"] = true;
            }
            else {
                $return["enviado"] = false;
            }
        }
        else {
            $return["enviado"] = false;
        }
    }
    else {
        $return["enviado"] = false;
    }
    return $return;
}
/**
 * Envio un SMS via CURL
 * Añado el Esto a la base de datos
 * @param $conn
 * @param $id_usuario
 */
function enviar_sms($telefono, $codigo) {
    $return = array();
    $varray = explode('.', phpversion());
    if(intval($varray[0]) >= 5){
        // PHP5 or Newer
        include_once('xmlize-php5.inc');
    }
    else{
        // PHP4 or Older
        include_once('xmlize-php4.inc');
    }
    // Envio la peticion
    $mensaje = "betaCiticens acces code:" . $codigo;
    if ($_SERVER["SERVER_NAME"] != "localhost") {
        $ok = peticionCURL($telefono, $mensaje);
    }
    else {
        $ok = 1;
    }
    // Comprueba si se ha enviado
    if ($ok == -1) {
        $return["error"] = true;
    }
    if ($ok == 1) {
        $return["error"] = false;
    }
    //Devuelvo
    return $return;
}






//////////////////////////////////// FUNCIONES DE ENVIO DE SMS ///////////////////////////////////////////////////////
//-----------------------------------------------------------------------------
// Metodos
//-----------------------------------------------------------------------------

// Selecciona las opciones que te interesen
// Puede ser cadena vacia
function selectOptions(){
    $opt = '';

// ATENCION!!
// ============================================================================

    // Configuracion de los deliver
    //-------------------------------------------------------------------------
    //$opt = $opt.'<delivery_receipt>'.EMAIL.'</delivery_receipt>';
    //$opt = $opt.'<delivery_receipt lang="'.LANG.'" cert_type="'.TIPO.'">'.EMAIL.'</delivery_receipt>';
    //$opt = $opt.'<delivery_receipt/>';


    // Configuracion permitir respuesta
    //-------------------------------------------------------------------------
    $opt = $opt.'<allow_answer/>';


    // Generar un mt_id
    //-------------------------------------------------------------------------
    //$mt_id = substr(md5(rand(0,999)), 0, 12);
    //guardarMTID($mt_id);
    //$opt = $opt.'<mt_id>'.$mt_id.'</mt_id>';


    // Programar SMS
    //-------------------------------------------------------------------------
    //$time = 200712011350;
    //$opt = $opt.'<schedule>'.$time.'</schedule>';

// ============================================================================
    return $opt;
}

// Guadar el MT_ID generado
function guardarMTID($mt_id=''){
// ATENCION!!
// ============================================================================
// Lo dejo para ti!
// ============================================================================
}

// Si empieza por +34 se lo quito
function quitarMas($n){
    if(substr($n, 0, strlen(CCODE) + 1) == "+".CCODE){
        return substr($n, strlen(CCODE) + 1);
    }
    else{
        return $n;
    }
}

// Si no empieza por +34 se lo pongo
function ponerMas($n){
    if (substr($n, 0, strlen(CCODE) + 1) != "+".CCODE){
        return "+".CCODE.$n;
    }
    else{
        return $n;
    }
}

// Metodo formdate toma el timestamp (int) y lo transforma en una fecha
// en formato AAAA-MM-DD HH:mm:ss (A = a�o, M = mes, D = dia,
// H = hora, m = minuto, s = segundo)
function formdate($timestamp) {
    $ret = date("Y-m-d G:i:s", $timestamp);
    return $ret;
}

//Convierte fecha de mysql a normal
function getDataNormal($fecha){
    ereg( "([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})", $fecha, $mifecha);
    $lafecha = $mifecha[3]."/".$mifecha[2]."/".$mifecha[1];
    return $lafecha;
}

//Convierte fecha de normal a mysql
function getDataMySQL($fecha){
    ereg( "([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})", $fecha, $mifecha);
    $lafecha = $mifecha[3]."-".$mifecha[2]."-".$mifecha[1];
    return $lafecha;
}

// Si no tiene la directiva magic_quotes_gpc a ON usar estas funciones...
// Poner los slashes
function __adds($text){
    if(!get_magic_quotes_gpc()){
        $slashed = addslashes(trim($text));
    }
    else{
        $slashed = trim($text);
    }
    return $slashed;
}

// Quitar los slashes
function __strip($text){
    if(!get_magic_quotes_gpc()){
        $stripped = stripslashes(trim($text));
    }
    else{
        $stripped = trim($text);
    }
    return $stripped;
}

// Metodo que retorna cierto si es valido, el xml de otro modo
function isValidXML($xml){
    // Aqui sabemos que a generado un XML.
    // Tengo que mirar si es
    // <result status="ERROR"> o bien
    // <result status="OK" ...>
    $data = xmlize($xml);

    if($data["result"]["#"]["status"][0]["#"] == '100'){
        // Es un OK al submit
        return 1;
    }
    else{
        return $xml;
    }
}

// Se genera el formato en XML
// El parametreo $dst puede ser un array de numeros
function makeSendXML($dst, $txt = ''){
    if($dst == '' || $txt == '' || PASSW == 'pass' || LOGIN == 'user'){
        return -1;
    }

    $xml = '<?xml version="1.0" encoding="iso-8859-1"?><sms><user>'.LOGIN.'</user><password>'.PASSW.'</password><dst>';

    if(is_array($dst)){
        // Bucle de los numeros
        $max = count($dst);
        for($i = 0; $i < $max; $i++){
            $xml = $xml.'<num>'.ponerMas($dst[$i]).'</num>';
        }
    }
    else{
        $xml = $xml.'<num>'.ponerMas($dst).'</num>';
    }

    $xml = $xml.'</dst><txt>'.$txt.'</txt>'.selectOptions().'</sms>';
    return $xml;
}

// Si hay algun problema devuelve -1
function peticionCURL($dst, $txt = ''){
    $url = 'http://sms.lleida.net/xmlapi/smsgw.cgi';

    $xml = makeSendXML($dst, $txt);
    if($xml == -1) return -1;

    $xml = 'xml='.$xml;

    // Proceso en backgorund
    $sh = curl_init($url);
    curl_setopt($sh, CURLOPT_POST, 1);
    curl_setopt($sh, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($sh, CURLOPT_HEADER, 0);
    curl_setopt($sh, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($sh, CURLOPT_RETURNTRANSFER, 1);

    // Aprovecho la variable $xml!
    $xml = curl_exec($sh);
    curl_close($sh);

    // Si hay algun problema...
    if (!is_string($xml) || !strlen($xml)){
        return -1;
    }
    else{
        return isValidXML($xml);
    }
}

//////////////////////////////////// FIN FUNCIONES DE ENVIO DE SMS ///////////////////////////////////////////////////////