<?php
/*
 * Peso de idioma
 * 1-->Parametro GET
 * 2-->Cookie de idioma
 * 3-->Dominio desde el que accedes
 * */
// Esqueleto por defecto para arrancar
if (!isset($_GET['page'])) {
	$fond = "home";
}
else {
	$fond = $_GET['page'];
}

// Array de idiomas disponibles
$idiomas_disponibles = array(
	"es",
	"en"
);

$get = comprobar_get();
if (!isset($_COOKIE["spip_lang"])) {
	// No existe la cookie
	if ($get == false) {
		// No existe el parametro get
		$dominio = $_SERVER['HTTP_HOST'];
		// Compruebo el dominio
		if ($dominio == "localhost") {
			// Si estas en local pongo el idioma en español
			$idioma = "es";
			setcookie("spip_lang", $idioma, time()+31556926 );
			$GLOBALS["idioma"] = $idioma;
		}
		else {
			$dominio_array = explode(".", $dominio);
			$num_dominio = count($dominio_array) - 2;

			if ($dominio_array[$num_dominio] === "testyourprivacy") {
				// Si el dominio es testoyuprivacy
				$idioma = "en";
				setcookie("spip_lang", $idioma, time()+31556926 );
				$GLOBALS["idioma"] = $idioma;
			}
			else {
				// Si no es testyou..... es decir, es testdeprivacidad
				$idioma = "es";
				setcookie("spip_lang", $idioma, time()+31556926 );
				$GLOBALS["idioma"] = $idioma;
			}
		}
	}
	else {
		// Existe el parametro get
		setcookie("spip_lang", $get, time()+31556926 );
		$GLOBALS["idioma"] = $get;
	}
}
else {
	// Existe la cookie
	if ($get != false) {
		// Existe parametro pasado por GET
		setcookie("spip_lang", $get, time()+31556926 );
		$GLOBALS["idioma"] = $get;
	}
	else {
		$GLOBALS["idioma"] = $_COOKIE["spip_lang"];
	}
}
// Recorro el array de idiomas
$longitus = count($idiomas_disponibles)-1;
for ($i = 0;$i <= count($idiomas_disponibles)-1; $i++) {
	if($idiomas_disponibles[$i] == $GLOBALS["idioma"]) {
		//echo "Posicion: " . $i . '<br>';
		//echo "Numero de posiciones: " . $longitus . '<br>';
		if ($i == $longitus) {
			$GLOBALS["idioma_sig"] = $idiomas_disponibles[0];
		}
		else {
			$GLOBALS["idioma_sig"] = $idiomas_disponibles[$i+1];
		}
	}
}

inicializar_cookies_embajador();
if($_SERVER["SERVER_NAME"] != "localhost") {
	// Miro el dominio actual
	$dominio = $_SERVER['HTTP_HOST'];
	// Lo parto por los puntos
	$partes_dominio = explode(".", $dominio);
	// Cogo la parte del medio
	$dominio = $partes_dominio[count($partes_dominio) - 2];
	// Segun lo que se lo mando a su home


	/* @TODO: Solo valido para las pruebas del dia 6 */
	if ($dominio == "betacitizens" || $dominio == "localhost") {
		setcookie("lang", "en");
		setcookie("spip_lang", "en");
		$GLOBALS["idioma"] = "en";
	}
	/* FIN */
	if (!isset($_GET['page'])) {
		switch ($dominio) {
			case "testdeprivacidad":
				$fond = "home";
				break;
			case "testyourprivacy":
				$fond = "home";
				break;
			case "betacitizens":
				$fond = "btz_home";
				break;
		}
	}
}
// Creo la cookie id_usuario
if ($fond == "betatester" && isset($_GET["token"])) {
	include 'test/test_ionic/www/php/loggin_basedatos.php';
	$conn = conectar();
	$token = $_GET["token"];
	$select = $conn->prepare("SELECT * FROM registroreto WHERE token = :token");
	if ($select->execute(array(":token" => $token))) {
		if ($select->rowCount() > 0) {
			$select = $select->fetchAll();
			setcookie("betatesterID", $select[0]["token_url"]);
		}
	}
}
/**
 * @return bool
 */
function comprobar_get() {
	if (isset($_GET['lang'])) {
		return $_GET['lang'];
	}
	else {
		return false;
	}
}

/**
 * Inizializa las cookies del emabajador
 */
function inicializar_cookies_embajador() {
	// Compruebo si hay parametros por GET
	if(isset($_GET["embajadores"])) {
		$GLOBALS['embajadores'] = $_GET["embajadores"];
		setcookie ('embajadores', $_GET["embajadores"]);
		$files = glob('tmp/cache/*'); // obtiene todos los archivos
		foreach($files as $file){
			if(is_file($file)) // si se trata de un archivo
				unlink($file); // lo elimina
		}
	}
	else {
		// Compruebo si hay cookie
		if (isset($_COOKIE["embajadores"])) {
			$GLOBALS['embajadores'] = $_COOKIE["embajadores"];
			// define('_NO_CACHE', -1);
		}
		else {
			$GLOBALS['embajadores'] = 73;
		}
	}
}
/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2016                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/
# ou est l'espace prive ?

if (!defined('_DIR_RESTREINT_ABS')) define('_DIR_RESTREINT_ABS', 'ecrire/');
include_once _DIR_RESTREINT_ABS.'inc_version.php';
# rediriger les anciens URLs de la forme page.php3fond=xxx
if (isset($_GET['fond'])) {
	include_spip('inc/headers');
	redirige_par_entete(generer_url_public($_GET['fond']));
}

# au travail...
include _DIR_RESTREINT_ABS.'public.php';
