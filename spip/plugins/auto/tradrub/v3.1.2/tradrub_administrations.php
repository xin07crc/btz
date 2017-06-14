<?php
/**
 * Création / suppression des champs dans la bdd
 *
 * @package SPIP\Tradrub\Installation
 * @license
 *     Licence GPL (c) 2008-2010 Stephane Laurent (Bill), Matthieu Marcillaud
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}


/**
 * Upgrade de la base
 *
 * @param string $nom_meta_base_version
 *     Nom de la meta informant de la version du schéma de données du plugin installé dans SPIP
 * @param string $version_cible
 *     Version du schéma de données dans ce plugin (déclaré dans paquet.xml)
 * @return void
 */
function tradrub_upgrade($nom_meta_base_version, $version_cible) {
	$maj = array();
	$maj['create'] = array(
		array('maj_tables', array('spip_rubriques')),
		array('sql_alter', 'TABLE spip_rubriques ADD INDEX (id_trad)')
	);

	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}

/**
 * Desinstallation du plugin
 *
 * Suppression de la colonne id_trad uniquement s'il ne reste
 * pas de traduction.
 *
 * @param string $nom_meta_base_version
 *     Nom de la meta informant de la version du schéma de données du plugin installé dans SPIP
 * @return void
 */
function tradrub_vider_tables($nom_meta_base_version) {
	// supprimer la colonne seulement s'il ne reste pas de traductions
	$il_en_reste = sql_countsel('spip_rubriques', array(
		'id_trad <> ' . sql_quote(0),
		'id_trad <> id_rubrique'));
	if (!$il_en_reste) {
		sql_alter('TABLE spip_rubriques DROP id_trad');
	}
	effacer_meta($nom_meta_base_version);
}
