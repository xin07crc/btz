<?php

function action_dereferencer_traduction_rubrique_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	list($type, $id_objet) = explode('/', $arg);
	if (!$type = objet_type($type) or !$id_objet = intval($id_objet)) {
		if (!_AJAX) {
			include_spip('inc/minipres');
			minipres('Arguments incompris');
		}
		else {
			spip_log('Arguments incompris dans action dereferencer_traduction_rubrique');
			return false;
		}
	}

	$objet = table_objet($type);
	$_id_objet = id_table_objet($objet);
	$table = table_objet_sql($objet);

	$id_trad_old = sql_getfetsel('id_trad', $table, "$_id_objet = " . sql_quote($id_objet));

	if ($id_trad_old) {
		include_spip('inc/modifier');
		modifier_contenu($objet, $id_objet, array('invalideur' => "id='$objet/$id_objet'"), array('id_trad' => 0));

		// si la deliaison fait qu'il ne reste plus que la source
		// dans le groupe de traduction on lui remet l'id_trad a 0
		if (1 == $nb_dans_groupe = sql_countsel($table, array('id_trad = ' . sql_quote($id_trad_old)))) {
			modifier_contenu($objet, $id_trad_old, array('invalideur' => "id='$objet/$id_trad_old'"), array('id_trad' => 0));
		}
	}
}
