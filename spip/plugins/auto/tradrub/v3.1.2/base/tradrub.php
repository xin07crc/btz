<?php

/**
 * Fonctions de déclarations des tables dans la bdd
 * et de sélection spécifique de la langue dans la rubrique...
 *
 * @package SPIP\Tradrub\Pipelines
 * @license
 *     Licence GPL (c) 2008-2010 Stephane Laurent (Bill), Matthieu Marcillaud
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Ajouter id_trad à la table rubriques
 *
 * @pipeline declarer_tables_objets_sql
 * @param array $tables
 *     Description des objets editoriaux
 * @return array
 *     Description des objets editoriaux
 */
function tradrub_declarer_tables_objets_sql($tables) {
	// Extension de la table rubriques
	$tables['spip_rubriques']['field']['id_trad'] = "bigint(21) DEFAULT '0' NOT NULL";
	$tables['spip_rubriques']['texte_definir_comme_traduction_objet'] = 'tradrub:texte_definir_comme_traduction_rubrique';
	return $tables;
}

/**
 * Ajout lors de l'insertion d'une traduction de rubrique
 * de la langue, qui peut ne pas être connue mais héritée
 * de la rubrique parente à la destination
 *
 * @param array $flux    Données du pipeline
 * @return array         Données du pipeline
**/
function tradrub_pre_insertion($flux) {
	// pour les rubriques
	if ($flux['args']['table'] == 'spip_rubriques') {
		// si on crée une traduction
		if ($id_rubrique_source = _request('lier_trad')) {
			$id_parent_trad = $flux['data']['id_parent'];

			$row = sql_fetsel('lang, id_secteur', 'spip_rubriques', 'id_rubrique='.intval($id_parent_trad));
			$lang_rub = $row['lang'];
			$lang = '';
			$choisie = 'non';

			// La langue a la creation : si les liens de traduction sont autorises
			// dans les rubriques, on essaie avec la langue de l'auteur,
			// ou a defaut celle de la rubrique
			// Sinon c'est la langue de la rubrique qui est choisie + heritee
			if (in_array('spip_rubriques', explode(',', $GLOBALS['meta']['multi_objets']))) {
				lang_select($GLOBALS['visiteur_session']['lang']);
				if (in_array($GLOBALS['spip_lang'], explode(',', $GLOBALS['meta']['langues_multilingue']))) {
					$lang = $GLOBALS['spip_lang'];
					$choisie = 'oui';
				}
			}

			if (!$lang) {
				$choisie = 'non';
				$lang = $lang_rub ? $lang_rub : $GLOBALS['meta']['langue_site'];
			}

			$flux['data']['lang'] = $lang;
			$flux['data']['langue_choisie'] = $choisie;

			// ici on ignore changer_lang qui est poste en cas de trad,
			// car l'heuristique du choix de la langue est pris en charge ici
			// en fonction de la config du site et de la rubrique choisie
			set_request('changer_lang');
		}
	}
	return $flux;
}
