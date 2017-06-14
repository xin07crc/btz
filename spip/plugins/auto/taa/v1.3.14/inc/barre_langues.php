<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

function inc_barre_langues_dist($id_article){
	include_spip('inc/config');
	include_spip('inc/actions');
	if(!function_exists('icone_verticale')) {
	       include_spip('inc/presentation');
	   }	
	$row = sql_fetsel("*", "spip_articles", "id_article=$id_article");
		
	$id_rubrique = $row['id_rubrique'];
	$id_trad = $row["id_trad"];
		
	$virtuel = (strncmp($row["chapo"],'=',1)!==0) ? '' :
	chapo_redirige(substr($row["chapo"], 1));
		
	$objet='article';	
	
	//création des onglets traduction
	
	// Les langues du site ou si restreint celle définis par config
	
	$langues_dispos=lire_config('taa/utiliser_langues')?lire_config('taa/utiliser_langues'):explode(',',lire_config('langues_multilingue'));

	
	// On établit les traductions de l'article
	$traductions	= array();
	
	if($langues_dispos){	
		
	// L'article est traduit	
	if($id_trad>0){
	
		// les traductions de l'article
		$sql=sql_select('lang,id_article','spip_articles','id_trad='.$id_trad);
	
		while($row=sql_fetch($sql)){
			$traductions[$row['lang']]=$row['id_article'];
			}
		$clic = _T('trad_delier');	
		$options = '<div class="options delier ajax">'.icone_verticale($clic, redirige_action_auteur('traduction',$id_article.'-article-0',"article&amp;id_article=$id_article"), "traductions-24.gif", "supprimer.gif",'right', false).'</div>';		
		}
	// L'article n'est pas traduit	
	else{
		$id_trad=$id_article;
		$row=sql_fetsel('lang,id_article','spip_articles','id_article='.$id_article);
		
		// Seul l'aticle présent est pris dans l'array traductions
		$traductions[$row['lang']]=$row['id_article'];
		
		$options =  '<div class="options form_lier"><h2>'._T('taa:lier_traduction').'</h2>'.redirige_action_auteur("traduction",
			$id_article.'-article',
			"article",
			"id_article=$id_article",
			("<label for='lier_trad'>" . _T('trad_lier') . "</label>" .
			"\n<input type='text' class='fondl' name='lier_trad' id='lier_trad' size='5' />\n"),
			_T('bouton_valider'),
			" class='fondl'").'</div>';
		}
	
	// Pour chaque langue présente on crée un bouton
	
	// indique l'article par défaut
	$span_content='<div class="ref_article">*<span>'._T('spip:trad_reference').'</span></div>';	
	
	foreach($langues_dispos as $key => $value){
		$class='';
		$span='';	
		$trad='';

		// les boutons hors article présent
		if($traductions[$value]!=$id_article){
					
			//Si il existe une traduction dans une langue du site on crée le bouton avec le lien de l'article
			if(array_key_exists($value,$traductions)){
				// Article de référence?	
				if($traductions[$value]==$id_trad){
					$span=$span_content;					
					}
				$onglets_traduction.='<li class="traduit box_onglet ajax">'.$span.'<a href="'.generer_url_ecrire($objet,'id_article='.$traductions[$value]).'">'.traduire_nom_langue($value).'</a></li>';					
			}
			// Sinon on crée un nouvel article dans la langue souhaitée
			else{
				// Si le plugin traduction rubriques est activé on regarde si on trouve la rubrique traduite
				if ($trad_rub=test_plugin_actif('tradrub')) {
					$id_rubrique_traduite=rubrique_traduction($value,$id_rubrique);

					if($id_rubrique_traduite){
						$onglets_traduction.= '<li class="non_traduit box_onglet"><a href="'.generer_url_ecrire($objet.'_edit','new=oui&lier_trad='.$id_trad.'&id_rubrique='.$id_rubrique_traduite.'&lang_dest='.$value).'" title="'._T('ecrire:info_tout_site2').'">'.traduire_nom_langue($value).'</a></li>';
						}
					elseif(test_plugin_actif('trad_rub')){
                       
						$donnes_trad=destination_traduction($value,$id_rubrique,$creer_racine='');
						$parent_trad=$donnes_trad[0];
						$trad=$donnes_trad[1];
						
						$onglets_traduction.='<li class="non_traduit box_onglet"><a href="'.generer_url_ecrire('rubrique_edit','new=oui&id_parent='.$parent_trad.'&lang_dest='.$value.'&lier_trad='.$trad.'&trad_new='.$trad_new.'&retour=nav').'" class="avis_source" title="'._T('tra:avis_rubrique_source').'">'.traduire_nom_langue($value).'</a>';
						} 					
					
					$section='oui';

					}
				else{
					$onglets_traduction.= '<li class="non_traduit box_onglet"><a href="'.generer_url_ecrire($objet.'_edit','new=oui&lier_trad='.$id_trad.'&lang_dest='.$value).'" title="'._T('ecrire:info_tout_site2').'">'.traduire_nom_langue($value).'</a></li>';				
				}

				$action=redirige_action_auteur ('changer_langue',$id_article.'-'.$value,$objet,"id_article=$id_article");
				// Si le plugin traduction rubriques est activé on affiche pas les onglets changement de langue car la langue se change en modifiant la rubrique
				if(!$section){
					$changer_traduction.='<li class="item  lang box_onglet"><a href="'.parametre_url($action,'changer_lang',$value).'">'.traduire_nom_langue($value).'</a></li>';					
					}

				}
			}
		// le bouton de l'article présent	
		else{
			if($traductions[$value]==$id_trad){
					$span=$span_content;					
					}
			$onglets_traduction.='<li class="box_onglet"><strong class="on">'.$span.traduire_nom_langue($value).'</strong></li>';
			}
		}
	}

	$contexte=array(
		'onglets_traduction'=>$onglets_traduction,
		'options'=>$options,
		'langue_article'=>$langue_article,
		'changer_traduction'=>$changer_traduction,
		'edition_seule'=>$edition_seule,
		'id_article'=>$id_article,	
		'voir'=>_request('voir'),									
		);
		
		$retour=recuperer_fond('prive/editer/barre_traductions_article',$contexte,array('ajax'=>true));
	return $retour;
	
}
	
?>