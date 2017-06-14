<?php
include '../test/test_ionic/www/php/loggin_basedatos.php';
// Compruebo que viene desde betaciticens
if (isset($_SERVER['HTTP_REFERER'])) {
    if (isset($_GET["ref"]) && isset($_GET["tu"]) && isset($_GET["ia"])) {
        $conn = conectar();
        $token_url = strip_tags($_GET["tu"]);
        $url_ref = strip_tags($_GET["ref"]);
        $id_article = strip_tags($_GET["ia"]);
        // Compruebo si es una prueba
        $pos = strpos($url_ref, "prueba");
        if ($pos === false) {
            $url_ref_completa = $url_ref . "?tu=" . $token_url . "&ia=" . $id_article;
        }
        else {
            $url_ref_completa = $url_ref . "&tu=" . $token_url . "&ia=" . $id_article;
        }
        // Compruebo si el usuario que accede es anonimo
        if ($token_url == 0) {
            $insertado = insertaDatosEnTabla($conn, $token_url, $url_ref, $id_article);
        }
        else {
            // @TODO: hay que cambiar el nombre de este token
            $token = $_COOKIE["token"];
            $esUsuario = comprobarUsuario($conn, $token_url, $token);
            if ($esUsuario == true) {
                $insertado = insertaDatosEnTabla($conn, $token_url, $url_ref, $id_article);
            }
            else {
                gohome();
            }
        }
        // Compruebo si se han insertado los datos en la tabla
        if ($insertado == true) {
            header("Location: " . $url_ref_completa);
            die();
        }
        else {
            gohome();
        }
    }
    else {
        gohome();
    }
}
else {
    gohome();
}

/**
 * LLeva a testdeprivivacidad
 */
function gohome() {
    header("Location: http://testdeprivacidad.com?page=betatester");
    die();
}

/**
 * Compruebo si el usuario esta en la base de datos o no
 * @param $conn
 * @param $token_url
 * @param $token
 *
 * return
 *      false -> si no esta en la base de datos
 *      true  -> si el usuario esta en la base de datos
 */
function comprobarUsuario($conn, $token_url, $token) {
    // Compruebo que el usuario es real y que el token url es valido
    $select = $conn->prepare("SELECT * FROM registroreto WHERE token_url = :token_url and token = :token");
    // Ejecuto la consulta
    if ($select->execute(array(":token_url" => $token_url, ":token" => $token))) {
        if ($select->rowCount() > 0) {
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
 * Inserto en la base de datos que usuario es y a donde va
 * 1 --> Primero compruebo que el articulo realmente lleva a esa URL si no lo hace retorna un false
 * 2 --> Inserta los datos en la tabla
 * @param $conn
 * @param $token_url
 * @param $token
 * @param $url_ref
 */
function insertaDatosEnTabla($conn, $token_url, $url_ref, $id_article) {
    // Comprobar que el articulo lleva a la URL
    $urlCierta = comprueboUrl($conn, $url_ref, $id_article);
    if ($urlCierta == true) {
        $select = $conn->prepare("INSERT INTO lista_redireccion (token_url, donde, id_reto) values(:token_url, :donde, :id_reto)");
        if ($select->execute(array(":token_url" => $token_url, ":donde" => $url_ref, ":id_reto" => $id_article))) {
            if ($select->rowCount() > 0) {
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
    else {
        return false;
    }
}


/**
 * Comprueno que la URL existe
 * @param $conn
 * @param $url_ref
 * @param $id_article
 * return true, false
 */
function comprueboUrl($conn, $url_ref, $id_article) {
    $select = $conn->prepare("SELECT * from spip_articles WHERE url_site = :url_site and id_article = :id_article");
    if ($select->execute(array(":url_site" => $url_ref, ":id_article" => $id_article))) {
        if ($select->rowCount() > 0) {
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
