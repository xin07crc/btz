<?php


$respuesta = array();
// Consigo la ip
$ip =  getRealIP();
// Verifico que la ip no es local
if ($ip != "::1") {
    // Envio los datos para recibir la informacion de geolocalizacion
    $meta = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip));
    $ipaddress = $ip;
}
// Si la ip es local se asigno uno por defecto (para que calcule)
else {
    $meta = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=213.37.131.194'));
    $ipaddress = "162.243.163.141";
    // $ipaddress = "83.59.49.28";
}
// Almaceno la ip
$respuesta["ip"] = $ipaddress;
// Almaceno todos los metas recibidos
$respuesta["meta"] = $meta;
// Peticion Curl

$url = "http://api.hackertarget.com/whois/?q=" . $ipaddress;
$result = file_get_contents($url);
$respuesta["curl"] = $result;

$json = file_get_contents('http://rest.db.ripe.net/search.json?query-string=' . $ipaddress . '&flags=no-filtering');
$obj = json_decode($json);
$respuesta["whois"] = $obj;

// Envio los datos al cliente
echo json_encode($respuesta);


/**
 * Obtiene la IP real de un visitante
 * @return mixed
 */
function getRealIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
        return $_SERVER['HTTP_CLIENT_IP'];

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        return $_SERVER['HTTP_X_FORWARDED_FOR'];

    return $_SERVER['REMOTE_ADDR'];
}