<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

function rubrique_traduction($lang,$id_rubrique){

	$id_trad_parent=sql_getfetsel('id_trad','spip_rubriques','id_rubrique=' . sql_quote($id_rubrique));
	$trad = '';
	
	if ($id_trad_parent){
		$trad = sql_getfetsel('id_rubrique','spip_rubriques','id_trad='. sql_quote($id_trad_parent) . ' AND lang='. sql_quote($lang));
		}


	return $trad;
}
?>
