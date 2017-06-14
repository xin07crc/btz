<?php
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
if (isset($postdata)) {
    include "loggin_basedatos.php";
    $mbd = conectar();
    $respuesta = array();
    $quien = $request->quien;
    $huella = $request->huella;

    $url = "http://track.netcom.it.uc3m.es/THIRDP/thirdP.php?post_arg1=" . $quien;
    $result = file_get_contents($url);
    $result_foreach = json_decode($result);
    $total_agentes = 0;
    foreach ($result_foreach as $clave => $valor) {
        if ($clave != "inspected_domain") {
            foreach ($valor as $item2) {
                $total_agentes++;
            }
        }
    }

    $insert_beta = $mbd->prepare("INSERT INTO quienrastrea(dominio, total_agentes, agentes_json, huella_digital, fecha) VALUES(:dominio, :total_agentes, :agentes_json, :huella_digital, :fecha)");
    $insert_beta->execute(array(
        "huella_digital" => $huella,
        "dominio" => $quien,
        "total_agentes" => $total_agentes,
        "agentes_json" => $result,
        "fecha" => date("Y-m-d"),
    ));
    echo $result;
}