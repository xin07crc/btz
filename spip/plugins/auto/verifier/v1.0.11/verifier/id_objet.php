<?php

// Sécurité
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Vérifie que la valeur correspond à un identifiant d'objet valide
 *
 * @param string $valeur
 *   La valeur à vérifier.
 * @param array $options
 * @return string
 *   Retourne une chaine vide si c'est valide, sinon une chaine expliquant l'erreur.
 */
function verifier_id_objet_dist($valeur, $options = array()) {
	$erreur = '';

	if ($valeur !== '') {
		// On vérifie déjà qu'il s'agit d'un nombre
		if (!is_numeric($valeur)) {
			$erreur = _T('verifier:erreur_id_objet');
		} elseif (isset($options['objet'])) {
			$id_table_objet = id_table_objet($options['objet']);
			$table = table_objet_sql($options['objet']);
			if ($id_table_objet && $table) {
				if (!sql_countsel($table, $id_table_objet.'='.intval($valeur))) {
					$erreur = _T('verifier:erreur_id_objet');
				}
			} else {
				$erreur = _T('verifier:erreur_objet');
			}
		}
	}

	return $erreur;
}
