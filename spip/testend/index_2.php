<?
// Compruebo que viene desde betaciticens
//if (isset($_SERVER['HTTP_REFERER'])) {
	// Compruebo que los parametros existen
	if (isset($_GET["tu"]) && isset($_GET["ia"]) && isset($_GET["cf"])) {
		$token_url = strip_tags($_GET["tu"]);
		$url_ref = strip_tags($_GET["ia"]);
		$cod = strip_tags($_GET["cf"]);
		$respuesta = almacenar_datos($token_url, $url_ref, $cod);
		if ($respuesta == true) {
			echo "Ok";
		}
		else {
			error_log_func("ER-02");
		}
	}
	else {
		error_log_func("ER-01");
	}
//}
//else {
//	echo "No";
	//gohome();
//}
/**
 * Almacena los datos
 * @param $token_url
 * @param $url_ref
 * @param $cod
 */
function almacenar_datos($token_url, $url_ref, $cod) {
	// Nombre del fichero
	$fichero = 'btz-testend.txt';
	// Escribe el contenido del fichero
	if (isset($_SERVER['HTTP_REFERER'])) {
		$donde = $_SERVER['HTTP_REFERER'];
	}
	else {
		$donde = "null";
	}
	$contenido = "|BTZ-testend";
	$contenido .= "|" . date("Y-m-d H:i:s") . "";
	$contenido .= "|" . $_SERVER['REMOTE_ADDR'] . "";
	$contenido .= "|" . $donde . "";
	$contenido .= "|" . $token_url . "";
	$contenido .= "|" . $url_ref . "";
	$contenido .= "|" . $cod . "|";
	// Escribe el contenido al fichero
	$ok = file_put_contents($fichero, $contenido . "\n", FILE_APPEND | LOCK_EX);
	// Retorno lo que pasa
	return $ok;
}
/**
 * LLeva a testdeprivivacidad
 */
function gohome() {
	header("Location: http://testdeprivacidad.com?page=betatester");
	die();
}

/**
 *
 */
function error_log_func($error) {
	$fichero = 'btz-error.txt';
	$contenido = "BTZ-testend|";
	$contenido .= $error;
	$contenido .= "|" . date("Y-m-d H:i:s") . "";
	$contenido .= "|" . $_SERVER['REMOTE_ADDR'] . "";
	file_put_contents($fichero, $contenido . "\n", FILE_APPEND | LOCK_EX);
	die();
}