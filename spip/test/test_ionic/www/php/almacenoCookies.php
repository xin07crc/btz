<?php
// Logeo en la base de datos
include "loggin_basedatos.php";
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
if (isset($postdata)) {
    $huella = $request->huella;
    $ip = $request->ip;
    $repetida = $request->repetida;
    $mbd = conectar();
    $return = array();
$return["debug"] = $ip;
    $select = $mbd->prepare("SELECT * from datos where huella = :huella");
    // Si exise
    if ($select->execute(array(':huella' => $huella))) {
        if ($select->rowCount() > 0) {
            $select = $select->fetchAll();
            $return["error"] = true;
            $return["cod"] = 1;
            $repetido = $select[0]["repetido"];
            $nuevo = $select[0]["nuevo"];
            $repetido++;
            if ($repetida == true){
                $update_beta = $mbd->prepare("UPDATE datos set ip = :ip, repetido = :repetido, fecha = :fecha where huella = :huella");
                $update_beta->execute(array(
			"ip" => $ip,
                    "huella" => $huella,
                    "repetido" => $repetido,
                    "fecha" => date("Y-m-d")
                ));
            }
            else {
                $update_beta = $mbd->prepare("UPDATE datos set ip = :ip, nuevo = :nuevo, fecha = :fecha where huella = :huella");
                $update_beta->execute(array(
			"ip" => $ip,
                    "huella" => $huella,
                    "nuevo" => $nuevo,
                    "fecha" => date("Y-m-d")
                ));
            }
		if ($update_beta->rowCount() > 0) {
			$return["debug2"] = "SI";
		}
		else {
			$return["debug2"] = "NO";
			$return["debug3"] = $update_beta->errorInfo();
			$return["debug4"] = $update_beta;
		}


        }
        else {
            $insert_beta = $mbd->prepare("INSERT INTO datos(ip, huella, nuevo, repetido, fecha, fecha_inicial) VALUES(:ip, :huella, :nuevo, :repetido, :fecha, :fecha_inicial)");
            $insert_beta->execute(array(
                "huella" => $huella,
                "ip" => $ip,
                "nuevo" => 1,
                "repetido" => 0,
                "fecha" => date("Y-m-d"),
                "fecha_inicial" => date("Y-m-d")
            ));
            $return["error"] = false;
            $return["cod"] = $insert_beta->rowCount();
        }
    }
    echo json_encode($return);
}
