<?php
/*
 * @TODO: no puede estar vacio FROM REQUIRED
 */
// Logeo en la base de datos
include "loggin_basedatos.php";

$return = array();
if (isset($_POST["id_article"]) && $_POST["tipo"] != "" && $_POST["mensaje"] != "") {
    $conn = conectar();
    $datos_tarea = conseguir_info_article($conn, strip_tags($_POST["id_article"]));
    $return["cosas"] = $datos_tarea;
    if ($datos_tarea["error"] == false) {
        $insert = $conn->prepare("INSERT INTO btz_comentarios_tareas (id_article, tipo, mensaje, titulo) VALUES (:id_article, :tipo, :mensaje, :titulo)");
        $valores = array(
            ":id_article" => strip_tags($_POST["id_article"]),
            ":tipo" => strip_tags($_POST["tipo"]),
            ":mensaje" => strip_tags($_POST["mensaje"]),
            ":titulo" => $datos_tarea["datos"][0]["titre"]
        );
        if ($insert->execute($valores)) {
            if ($insert->rowCount() > 0) {
                $return["error"] = false;
            }
            else {
                $return["error"] = true;
                $return["cod"] = 3;
            }
        } else {
            $return["error"] = true;
            $return["cod"] = 2;
        }
    }
    else {
        $return["error"] = true;
        $return["cod"] = 1;
    }
}
else {
    $return["error"] = true;
    $return["cod"] = 0;

}

echo json_encode($return);

/**
 * Consigue toda la informaicon de un articulo
 * @param $conn
 * @param $id_article
 */
function conseguir_info_article($conn, $id_article) {
    $return = array();
    $select = $conn->prepare("SELECT * FROM spip_articles WHERE id_article = :id_article");
    if ($select->execute(array(":id_article" => $id_article))) {
        if ($select->rowCount()>0) {
            $return["error"] = false;
            $return["datos"] = $select->fetchAll();
        }
        else {
            $return["error"] = true;
            $return["cod"] = 1;
        }
    }
    else {
        $return["error"] = true;
        $return["cod"] = 0;
    }
    return $return;
}