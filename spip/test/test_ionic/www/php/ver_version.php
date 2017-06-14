<?php

include "loggin_basedatos.php";
include "funcion.php";

$return = array();

if (isset($_POST["id"])) {
    $conn = conectar();
    $select = $conn->prepare("SELECT * FROM spip_mots WHERE id_groupe = :id_groupe");
    if ($select->execute(array(":id_groupe" => strip_tags($_POST["id"])))) {
        if ($select->rowCount() > 0) {
            $select = $select->fetchAll();
            $id_mot = array();
            foreach ($select as $item) {
                $datos = array();
                $datos["nombre"] = $item["titre"];
                $datos["id"] = $item["descriptif"];
                array_push($id_mot, $datos);
            }
            $return["datos"] = $id_mot;
            $return["error"] = false;
        }
        else {
            $return["error"] = false;
        }
    }
    else {
        $return["error"] = false;
    }
}
else {
    $return["error"] = false;
}

echo json_encode($return);