<?php

include "test/test_ionic/www/php/loggin_basedatos.php";
include "test/test_ionic/www/php/fu";
/**
 *
 */
function formulaires_crearproyecto_charger_dist(){
    $valeurs = array();
    foreach($_POST as $key => $value) {
        $valeurs[$key] = $value;
    }
    return $valeurs;
}

/**
 *
 */
function formulaires_crearproyecto_verifier_dist(){
    $erreurs = array();
    return $erreurs;
}

/**
 *
 */
function formulaires_crearproyecto_traiter_dist(){
    $valuer = array(
        ":nombre_proyecto" => strip_tags($_POST["nom_pro"]),
        ":nombre_tarea" => strip_tags($_POST["nom_ta"]),
        ":descripcion_breve" => strip_tags($_POST["desc_pro"]),
        ":descripcion_detallada" => strip_tags($_POST["descbr_de"]),
        ":informacion" => strip_tags($_POST[""]),
        ":enlace_act" => strip_tags($_POST[""]),
        ":fecha_comienzo" => strip_tags($_POST[""]),
        ":fecha_finalizacion" => strip_tags($_POST[""]),
        ":dificultad" => strip_tags($_POST[""]),
        ":Idioma" => strip_tags($_POST[""]),
    );





}