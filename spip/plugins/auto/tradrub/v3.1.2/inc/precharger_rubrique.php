<?php

/**
 * Fonctions de pré-remplissage des traductions
 *
 * @package SPIP\Tradrub\Objets
 * @license
 *     Licence GPL (c) 2008-2010 Stephane Laurent (Bill), Matthieu Marcillaud
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}


include_spip('inc/precharger_objet');

/**
 * Retourne les valeurs à charger pour un formulaire d'édition d'une rubrique
 *
 * Lors d'une création, certains champs peuvent être préremplis
 * (c'est le cas des traductions)
 *
 * @param string|int $id
 *     Identifiant de la rubrique, ou "new" pour une création
 * @param int $id_rubrique
 *     Identifiant éventuel de la rubrique parente
 * @param int $lier_trad
 *     Identifiant éventuel de la traduction de référence
 * @return array
 *     Couples clés / valeurs des champs du formulaire à charger.
**/
function inc_precharger_rubrique_dist($id, $id_rubrique = 0, $lier_trad = 0) {
	return precharger_objet('rubrique', $id, $id_rubrique, $lier_trad, 'titre');
}

/**
 * Récupère les valeurs d'une traduction de référence pour la création
 * d'une rubrique (préremplissage du formulaire).
 *
 * @note Fonction facultative si pas de changement dans les traitements
 *
 * @param string|int $id
 *     Identifiant de la rubrique, ou "new" pour une création
 * @param int $id_rubrique
 *     Identifiant éventuel de la rubrique parente
 * @param int $lier_trad
 *     Identifiant éventuel de la traduction de référence
 * @return array
 *     Couples clés / valeurs des champs du formulaire à charger
**/
function inc_precharger_traduction_rubrique_dist($id, $id_rubrique = 0, $lier_trad = 0) {
	return precharger_traduction_objet('rubrique', $id, $id_rubrique, $lier_trad, 'titre');
}
