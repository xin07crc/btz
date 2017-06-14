<?php

// Sécurité
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function traiter_enregistrement_dist($args, $retours) {
	include_spip('inc/formidable');
	include_spip('base/abstract_sql');
	$options = $args['options'];
	$formulaire = $args['formulaire'];
	$id_formulaire = $args['id_formulaire'];
	$saisies = unserialize($formulaire['saisies']);
	$saisies = saisies_lister_par_nom($saisies);

	// La personne a-t-elle un compte ?
	$id_auteur = isset($GLOBALS['visiteur_session']) ? (isset($GLOBALS['visiteur_session']['id_auteur']) ? $GLOBALS['visiteur_session']['id_auteur'] : 0) : 0;

	// traitement de l'anonymisation
	if ($options['anonymiser'] == 'on') {
		// mod de l'id_auteur
		$variables_anonymisation =
			$GLOBALS['formulaires']['variables_anonymisation'][$options['anonymiser_variable']];
		$id = eval("return $variables_anonymisation;");
		$id_auteur = formidable_scramble($id, $id_formulaire);
	}

	// On cherche le cookie et sinon on le crée
	$nom_cookie = formidable_generer_nom_cookie($id_formulaire);
	if (isset($_COOKIE[$nom_cookie])) {
		$cookie = $_COOKIE[$nom_cookie];
	} else {
		include_spip('inc/acces');
		$cookie = creer_uniqid();
	}

	// On regarde si c'est une modif d'une réponse existante
	$id_formulaires_reponse = $args['id_formulaires_reponse'];

	// recherche d'éventuelles anciennes réponses
	$reponses = formidable_verifier_reponse_formulaire(
		$id_formulaire,
		$options['identification'],
		($options['anonymiser'] == 'on')
			? $options['anonymiser_variable']
			: false
	);

	// pas d'id_formulaires_reponse : on cherche une éventuelle réponse en base
	if ($id_formulaires_reponse == false) {
		$traitements_formulaire = unserialize($formulaire['traitements']);

		if (isset($traitements_formulaire['enregistrement'])) {
			$options =  $traitements_formulaire['enregistrement'];

			if (isset($options['multiple']) && $options['multiple'] == ''
					&& isset($options['modifiable']) && $options['modifiable'] == 'on'
					&& is_array($reponses) && count($reponses) > 0) {
				$id_formulaires_reponse = max($reponses);
			}
		}
	} else {
		// vérifier que l'auteur est bien l'auteur de la réponse, si non, on invalide l'id_formulaires_reponse
		if (in_array($id_formulaires_reponse, $reponses) == false) {
			$id_formulaires_reponse = false;
		}
	}

	// Si la moderation est a posteriori ou que la personne est un boss, on publie direct
	if ($options['moderation'] == 'posteriori'
		or autoriser('instituer', 'formulaires_reponse', $id_formulaires_reponse, null, array('id_formulaire' => $id_formulaire, 'nouveau_statut' => 'publie'))) {
		$statut='publie';
	} else {
		$statut = 'prop';
	}

	// Si ce n'est pas une modif d'une réponse existante, on crée d'abord la réponse
	if (!$id_formulaires_reponse) {
		$id_formulaires_reponse = sql_insertq(
			'spip_formulaires_reponses',
			array(
				'id_formulaire' => $id_formulaire,
				'id_auteur' => $id_auteur,
				'cookie' => $cookie,
				'ip' => $args['options']['ip'] == 'on' ? $GLOBALS['ip'] : '',
				'date' => 'NOW()',
				'statut' => $statut
			)
		);
		// Si on a pas le droit de répondre plusieurs fois ou que les réponses seront modifiables,
		// il faut poser un cookie
		if (!$options['multiple'] or $options['modifiable']) {
			include_spip('inc/cookie');
			// Expiration dans 30 jours
			spip_setcookie($nom_cookie, $_COOKIE[$nom_cookie] = $cookie, time() + 30 * 24 * 3600);
		}
	} else {
		// simple mise à jour du champ maj de la table spip_formulaires_reponses
		sql_updateq(
			'spip_formulaires_reponses',
			array('maj' => 'NOW()'),
			"id_formulaires_reponse = $id_formulaires_reponse"
		);
	}

	// Si l'id n'a pas été créé correctement alors erreur
	if (!($id_formulaires_reponse > 0)) {
		$retours['message_erreur'] .= "\n<br/>"._T('formidable:traiter_enregistrement_erreur_base');
	} else {
		// Sinon on continue à mettre à jour
		$champs = array();
		$insertions = array();
		foreach ($saisies as $nom => $saisie) {
			// On ne prend que les champs qui ont effectivement été envoyés par le formulaire
			if (($valeur = _request($nom)) !== null) {
				$champs[] = $nom;
				$insertions[] = array(
					'id_formulaires_reponse' => $id_formulaires_reponse,
					'nom' => $nom,
					'valeur' => is_array($valeur) ? serialize($valeur) : $valeur
				);
			}
		}

		// S'il y a bien des choses à modifier
		if ($champs) {
			// On supprime d'abord les champs
			sql_delete(
				'spip_formulaires_reponses_champs',
				array(
					'id_formulaires_reponse = '.$id_formulaires_reponse,
					sql_in('nom', $champs)
				)
			);

			// Puis on insère les nouvelles valeurs
			sql_insertq_multi(
				'spip_formulaires_reponses_champs',
				$insertions
			);
		}
		if (!isset($retours['message_ok'])) {
			$retours['message_ok'] = '';
		}
		$retours['message_ok'] .= "\n"._T('formidable:traiter_enregistrement_message_ok');
		$retours['id_formulaires_reponse'] = $id_formulaires_reponse;
	}

	// noter qu'on a deja fait le boulot, pour ne pas risquer double appel
	$retours['traitements']['enregistrement'] = true;
	return $retours;
}

function traiter_enregistrement_update_dist($id_formulaire, $traitement, $saisies_anciennes, $saisies_nouvelles) {
	include_spip('inc/saisies');
	include_spip('base/abstract_sql');
	$comparaison = saisies_comparer($saisies_anciennes, $saisies_nouvelles);

	// Si des champs ont été supprimés, il faut supprimer les réponses à ces champs
	if ($comparaison['supprimees']) {
		// On récupère les réponses du formulaire
		$reponses = sql_allfetsel(
			'id_formulaires_reponse',
			'spip_formulaires_reponses',
			'id_formulaire = '.$id_formulaire
		);
		$reponses = array_map('reset', $reponses);

		// Tous les noms de champs à supprimer
		$noms = array_keys($comparaison['supprimees']);

		// On supprime
		sql_delete(
			'spip_formulaires_reponses_champs',
			array(
				sql_in('id_formulaires_reponse', $reponses),
				sql_in('nom', $noms)
			)
		);
	}
}
