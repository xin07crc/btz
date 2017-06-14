<?php
function trad_rub_header_prive($flux) {

	$flux .= '<link rel="stylesheet" href="' . find_in_path('css/trad_rub_styles.css') . '" type="text/css" media="all" />';
$flux .='<!-- Example script -->
<script type="text/javascript">
	$(document).ready( function() {
		$(".avis_source").click( function() {
			javascript:alert("'._T('tra:avis_rubrique_source').'");
		});
	});
</script>
';
	return $flux;

}

/*Ajoute la langue de traduction dans le chargement du formulaire edition_rubrique*/
function trad_rub_formulaire_charger($flux) {
	$form = $flux['args']['form'];
	if ($form == 'editer_rubrique' AND _request('new') == 'oui') {

		if (!$flux['data']['lang_dest'] = _request('lang_dest') AND $id_rubrique = _request('id_parent')) {
			$flux['data']['lang_dest'] = sql_getfetsel('lang', 'spip_rubriques', 'id_rubrique=' . $id_rubrique);
		}
		if (isset($flux['data']['lang_dest']))
			$flux['data']['_hidden'] .= '<input type="hidden" name="lang_dest" value="' . $flux['data']['lang_dest'] . '"/>';

	}
	return $flux;
}

/*Ajoute le id traduction a la rubrique d'origine*/
function trad_rub_formulaire_traiter($flux) {
	$form = $flux['args']['form'];
	if ($form == 'editer_rubrique') {
		$id_trad = _request('lier_trad');
		if ($id_trad) {
			sql_updateq('spip_rubriques', array('id_trad' => $id_trad), 'id_rubrique=' . $id_trad);
		}
	}
	return $flux;
}

/*Prise en compte de la langue de traduction dans le traitement du formulaire edition_article*/
function trad_rub_pre_insertion($flux) {
	if ($flux['args']['table'] == 'spip_rubriques') {

		if ($lang = _request('lang_dest')) {
			$id_trad = _request('lier_trad');
			$flux['data']['lang'] = $lang;
			$flux['data']['langue_choisie'] = 'oui';
			$flux['data']['id_trad'] = $id_trad;
		}
	}
	return $flux;
}

function trad_rub_recuperer_fond($flux) {
	//Insertion des onglets de langue

	if ($flux['args']['fond'] == 'prive/squelettes/contenu/rubrique') {
		include_spip('inc/config');
		$contexte = array('id_rubrique' => $flux['args']['contexte']['id_rubrique']);

		//Verifier si le plugin taa à prévu une limitation d'affiçchage au niveau des secteur
		$id_secteur = sql_getfetsel('id_secteur', 'spip_rubriques', 'id_rubrique=' . $contexte['id_rubrique']);
		$limiter_secteur = lire_config('taa/limiter_secteur') ? lire_config('taa/limiter_secteur') : array();

		if (!in_array($id_secteur, $limiter_secteur)) {
			$barre_langue = recuperer_fond("prive/editer/barre_traductions_rubrique", $contexte, array('ajax' => true));
			$flux['data']['texte'] = str_replace('</h1>', '</h1>' . $barre_langue, $flux['data']['texte']);
		}
	}

	return $flux;
}
?>
