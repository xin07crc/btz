<?php

if (!defined("_ECRIRE_INC_VERSION")) return;


function action_changer_langue_dist() {

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	list($id_objet,$lang)=explode("-",$arg);;

		
	if (!autoriser('modifier','article',$id_objet)) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
		sql_updateq('spip_articles',array('lang'=>$lang,'langue_choisie'=>'oui'),'id_article='.$id_objet);
	}
	
}
?>
