<?php
/**
 * Escribe en un fichero cuando se retorno de la pagina de actividad
 * tu --> Token de usuario
 * ia --> Id de actividad
 * cf --> Codigo de retorno de OK
 */
include "../test/test_ionic/www/php/loggin_basedatos.php";
include "../test/test_ionic/www/php/funcion.php";

// Compruebo que viene desde betaciticens
//if (isset($_SERVER['HTTP_REFERER'])) {
	// Compruebo que los parametros existen
	if (isset($_GET["tu"]) && isset($_GET["ia"]) && isset($_GET["cf"])) {
		$conn = conectar();
		$token_url = strip_tags($_GET["tu"]);
		$url_ref = strip_tags($_GET["ia"]);
		$cod = strip_tags($_GET["cf"]);
		$usuario = comprobarUsuarioTokenUrl($conn, $token_url);
		if ($usuario["error"] == false) {
			$realizada = comprueba_realizada($conn, $usuario["id_usuairo"], $url_ref);
			if ($realizada == true) {
				$respuesta = almacenar_datos_sql($conn, $usuario["id_usuairo"], $url_ref, $cod);
				if ($respuesta == true) {
					$puntos = obtener_sumar_puntos($conn, $usuario["id_usuairo"], $url_ref);
					// Sumo los puntos al que invito al usuario
					$puntos_remitente = obtener_sumar_puntos_remitente($conn, $usuario["id_usuairo"], $url_ref);
					header("Location: ../?page=btz_realizadas");
				}
				else {
					error_log_func("ER-04");
				}
			}
			else {
				// Ha realizado la tarea, no sumo nada y no apunto nada.
				header("Location: ../?page=btz_realizadas");
			}
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
 * Obtengo los puntos de la prueba saco el porcentaje para el remitente y se los sumo
 * @param $conn
 * @param $id_usuario
 * @param $url_ref
 */
function obtener_sumar_puntos_remitente($conn, $id_usuario, $url_ref) {
	$return = array();
	// Busco si este usuario ha sido recomendado
	$select_remitente = $conn->prepare("SELECT * FROM btz_invitaciones WHERE id_invitado = :id_invitado");
	if ($select_remitente->execute(array(":id_invitado" => $id_usuario))) {
		$select_remitente = $select_remitente->fetchAll();
		$id_remitente = $select_remitente[0]["id_remitente"];
		$select_puntos = $conn->prepare("SELECT soustitre FROM spip_articles WHERE id_article = :id_article");
		if ($select_puntos->execute(array(":id_article" => $url_ref))) {
			$select_puntos = $select_puntos->fetchAll();
			// Obtengo los puntos del articulo
			$puntos_articulo = $select_puntos[0]["soustitre"];
			// 20% para el remitente
			$puntos_a_remitente = intval($puntos_articulo * 20 / 100);
			// Seleciono los puntos del remitente
			$select_puntos_actuales = obtener_datos_usuario($conn, $id_remitente);
			// Sumos los puntos
			$puntos_remitente = $select_puntos_actuales["datos"][0]["puntos"] + $puntos_a_remitente;
			// Inserto los puntos en la base de datos
			$insert = $conn->prepare("UPDATE registroreto SET puntos = :puntos WHERE id_usuario = :id_usuario");
			if ($insert->execute(array(":puntos" => $puntos_remitente, ":id_usuario" => $id_remitente))) {
				return true;
			}
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
}
/**
 * Obtengo los puntos y se los añado al usuario
 * @param $conn
 * @param $token_url
 * @param $url_ref
 */
function obtener_sumar_puntos($conn, $id_usuario, $url_ref) {
	$return = array();
	$select_puntos = $conn->prepare("SELECT soustitre FROM spip_articles WHERE id_article = :id_article");
	if ($select_puntos->execute(array(":id_article" => $url_ref))) {
		$select_puntos = $select_puntos->fetchAll();
		// Obtengo los puntos del articulo
		$puntos_articulo = $select_puntos[0]["soustitre"];
		// Obtengo los datos del usuario
		$select_puntos_actuales = obtener_datos_usuario($conn, $id_usuario);
		// Obtengo los puntos del usuario
		$puntos_usuario = $select_puntos_actuales["datos"][0]["puntos"];
		// Sumo ambos
		$puntos_totales = $puntos_articulo + $puntos_usuario;
		// Inserto los puntos en la tabla
		$insert = $conn->prepare("UPDATE registroreto SET puntos = :puntos WHERE id_usuario = :id_usuario");
		if ($insert->execute(array(":puntos" => $puntos_totales, ":id_usuario" => $id_usuario))) {
			return true;
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
}

/**
 * COmprueba si ya ha realizado esta tarea
 * @param $conn
 * @param $id_usuario
 * @param $url_ref
 * @return bool
 * 			false => Ya ha realizado la tarea
 * 			true => No la ha realizado
 *
 */
function comprueba_realizada($conn, $id_usuario, $url_ref) {
	$retun = array();
	$select = $conn->prepare("SELECT * FROM btz_testend WHERE id_usuario = :id_usuario and id_actividad = :id_actividad");
	if ($select->execute(array(":id_usuario" => $id_usuario, ":id_actividad" => $url_ref))) {
		if ($select->rowCount() > 0) {
			return false;
		}
		else {
			return true;
		}
	}
	else {
		return false;
	}
}
/**
 * Almacena los datos
 * @param $token_url
 * @param $url_ref
 * @param $cod
 */
function almacenar_datos($conn, $token_url, $url_ref, $cod) {
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
 * Almaceno los datos recibidos en el servidor
 * @param $conn
 * @param $token_url
 * @param $url_ref
 * @param $cod
 */
function almacenar_datos_sql($conn, $id_usuario, $url_ref, $cod) {
	if (isset($_SERVER['HTTP_REFERER'])) {
		$donde = $_SERVER['HTTP_REFERER'];
	}
	else {
		$donde = "null";
	}
	$values = array(
		":fecha" => date("Y-m-d H:i:s"),
		":ip" => $_SERVER['REMOTE_ADDR'],
		":donde" => $donde,
		":id_usuario" => $id_usuario,
		":id_actividad" => $url_ref,
		":codigo" => $cod,
	);
	$insert = $conn->prepare("INSERT into btz_testend (fecha, ip, donde, id_usuario, id_actividad, codigo) VALUES (:fecha, :ip, :donde, :id_usuario, :id_actividad, :codigo)");
	if ($insert->execute($values)) {
		if ($insert->rowCount()>0) {
			return true;
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
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
	$result = file_put_contents($fichero, $contenido . "\n", FILE_APPEND | LOCK_EX);
	echo $error;
	die();
}