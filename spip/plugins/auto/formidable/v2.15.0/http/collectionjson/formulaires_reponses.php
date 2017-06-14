<?php

// Sécurité
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Répondre à un formulaire Formidable en Collection+JSON
 * 
 * Il faut avoir un id_formulaire et pouvoir y répondre.
 *
 * @param Request $requete
 * @param Response $reponse
 * @return void
 */
function http_collectionjson_formulaires_reponses_post_collection_dist($requete, $reponse){
	include_spip('inc/session');
	include_spip('inc/autoriser');
	$fonction_erreur = charger_fonction('erreur', "http/collectionjson/");
	
	// On teste si on a bien du contenu au bon format
	if (
		$contenu = $requete->getContent()
		and $json = json_decode($contenu, true)
		and is_array($json)
		and isset($json['collection']['items'][0]['data'])
		and $data = $json['collection']['items'][0]['data']
		and is_array($data)
	) {
		// Pour chaque champ envoyé, ça dépend ce que c'est
		foreach ($data as $champ) {
			if (
				isset($champ['name'])
				and isset($champ['value'])
			) {
				// Si c'est id_formulaire, on le garde de côté pour l'autorisation et pour le traitement
				if ($champ['name'] == 'id_formulaire') {
					$id_formulaire = intval($champ['value']);
				}
				// Sinon on le met dans le post pour verifier/traiter
				set_request($champ['name'], $champ['value']);
			}
		}
		
		// On teste l'autorisation
		if ($id_formulaire > 0 and autoriser('repondre', 'formulaire', $id_formulaire)) {
			// On vérifie maintenant les erreurs
			$formidable_verifier = charger_fonction('verifier', 'formulaires/formidable');
			$erreurs = $formidable_verifier($id_formulaire);
		
			// On passe les erreurs dans le pipeline "verifier" (par exemple pour Saisies)
			$erreurs = pipeline('formulaire_verifier', array(
				'args' => array(
					'form' => 'formidable',
					'args' => array($id_formulaire),
				),
				'data' => $erreurs,
			));
			
			// S'il y a des erreurs, on va générer un JSON les listant
			if ($erreurs) {
				$reponse->setStatusCode(400);
				$reponse->headers->set('Content-Type', 'application/json');
				$reponse->setCharset('utf-8');
				
				$json_reponse = array(
					'collection' => array(
						'version' => '1.0',
						'href' => url_absolue(self('&')),
						'error' => array(
							'title' => _T('erreur'),
							'code' => 400,
						),
						'errors' => array(),
					),
				);
				
				foreach ($erreurs as $nom => $erreur) {
					$json_reponse['collection']['errors'][$nom] = array(
						'title' => $erreur,
						'code' => 400,
					);
				}
				$reponse->setContent(json_encode($json_reponse));
			}
			// Sinon on continue le traitement
			else {
				// On lance le traitement de la réponse
				$formidable_traiter = charger_fonction('traiter', 'formulaires/formidable', true);
				$retours_formidable = $formidable_traiter($id_formulaire);
				
				// On passe dans le pipeline "traiter" (par exemple pour les quizz)
				$retours_formidable = pipeline('formulaire_traiter', array(
					'args' => array(
						'form' => 'formidable',
						'args' => array($id_formulaire),
					),
					'data' => $retours_formidable,
				));
				
				// Si à la fin on a bien un identifiant de réponse
				if ($id_formulaires_reponse = $retours_formidable['id_formulaires_reponse']) {
					// On va cherche la fonction qui génère la vue d'une ressource
					if ($fonction_ressource = charger_fonction('get_ressource', 'http/collectionjson/', true)) {
						// On ajoute à la requête, l'identifiant de la nouvelle ressource
						$requete->attributes->set('ressource', $id_formulaires_reponse);
						$reponse = $fonction_ressource($requete, $reponse);
						
						// C'est une création, on renvoie 201
						$reponse->setStatusCode(201);
					}
				}
				// Sinon l'enregistrement n'a pas fonctionné donc erreur
				// (TODO : il faudrait les chaînes pour les 5XX dans le plugin HTTP)
				else {
					$reponse = $fonction_erreur(500, $requete, $reponse);
				}
			}
		}
		// Sinon pas le droit
		else {
			$reponse = $fonction_erreur(403, $requete, $reponse);
		}
	}
	// Sinon on ne comprend pas ce qui se passe
	else {
		$reponse = $fonction_erreur(415, $requete, $reponse);
	}
	
	return $reponse;
}
