<?php

function formulaires_ajouter_traduction_charger_dist($id_rubrique) {

	$id_trad = sql_getfetsel('id_trad', 'spip_rubriques', 'id_rubrique=' . $id_rubrique);

	if (!$id_trad) {
		$id_trad = $id_rubrique;
		$trad_new = 'oui';
	}

	$valeurs = array(
		'id_rubrique' => $id_rubrique,
		'id_trad' => $id_trad,
		'rubriques_menu' => '',
		'trad_new=' => '',
	);

	$valeurs['_hidden'] .= '<input type="hidden" name="id_trad" value="' . $id_trad . '"/>';
	$valeurs['_hidden'] .= '<input type="hidden" name="id_rubrique" value="' . $id_rubrique . '"/>';
	if ($trad_new)
		$valeurs['_hidden'] .= '<input type="hidden" name="trad_new" value="' . $trad_new . '"/>';
	return $valeurs;
}

function formulaires_ajouter_traduction_traiter_dist() {

	$id_trad = _request('id_trad');
	$trad_new = _request('trad_new');
	$id_rubrique = _request('rubriques_menu');
	$id_rubrique = explode('|', $id_rubrique[0]);
	$id_rubrique = $id_rubrique[1];

	sql_updateq('spip_rubriques', array('id_trad' => $id_trad), 'id_rubrique=' . $id_rubrique);

	if ($trad_new)
		sql_updateq('spip_rubriques', array('id_trad' => $id_trad), 'id_rubrique=' . $id_trad);

	return $valeurs;
}
?>