<?php

if (!defined("_ECRIRE_INC_VERSION")) return;


function action_traduction_dist() {

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	list($id_objet,$objet,$id_trad)=explode("-",$arg);
	
	
	if(_request('lier_trad'))$id_trad=_request('lier_trad');


	$referencer_traduction = charger_fonction('referencer_traduction','action');
	$referencer_traduction($objet, $id_objet,$id_trad); // 0 si supprimer_trad
		
	
}
?>
