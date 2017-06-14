<?php

// Sécurité
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function traiter_email_dist($args, $retours) {
	$formulaire = $args['formulaire'];
	$options = $args['options'];
	$saisies = unserialize($formulaire['saisies']);
	$traitements = unserialize($formulaire['traitements']);
	$champs = saisies_lister_champs($saisies);
	$destinataires = array();

	// On récupère les destinataires
	if ($options['champ_destinataires']) {
		$destinataires = _request($options['champ_destinataires']);
		if (!is_array($destinataires)) {
			if (intval($destinataires)) {
				$destinataires = array($destinataires);
			} else {
				$destinataires = array();
			}
		}
		if (count($destinataires)) {
			// On récupère les mails des destinataires
			$destinataires = array_map('intval', $destinataires);
			$destinataires = sql_allfetsel(
				'email',
				'spip_auteurs',
				sql_in('id_auteur', $destinataires)
			);
			$destinataires = array_map('reset', $destinataires);
		}
	}

	if ($options['champ_courriel_destinataire_form']) {
		$courriel_champ_form = _request($options['champ_courriel_destinataire_form']);
		$destinataires[] = $courriel_champ_form;
	}


	// On ajoute les destinataires en plus
	if ($options['destinataires_plus']) {
		$destinataires_plus = explode(',', $options['destinataires_plus']);
		$destinataires_plus = array_map('trim', $destinataires_plus);
		$destinataires = array_merge($destinataires, $destinataires_plus);
		$destinataires = array_unique($destinataires);
	}
	
	// On ajoute les destinataires en fonction des choix de saisie dans le formulaire
	// @selection_1@/choix1 : mail@domain.tld
	// @selection_1@/choix2 : autre@domain.tld, lapin@domain.tld
	if (!empty($options['destinataires_selon_champ'])) {
		if ($destinataires_selon_champ = formidable_traiter_email_destinataire_selon_champ($options['destinataires_selon_champ'])) {
			$destinataires = array_merge($destinataires, $destinataires_selon_champ);
			$destinataires = array_unique($destinataires);
		}
	}

	// On récupère le courriel de l'envoyeur s'il existe
	if ($options['champ_courriel']) {
		$courriel_envoyeur = _request($options['champ_courriel']);
	}
	if (!$courriel_envoyeur) {
		$courriel_envoyeur = '';
	}

	// Si on a bien des destinataires, on peut continuer
	if ($destinataires or ($courriel_envoyeur and $options['activer_accuse'])) {
		include_spip('inc/filtres');
		include_spip('inc/texte');

		$nom_site_spip = supprimer_tags(typo($GLOBALS['meta']['nom_site']));

		// On parcourt les champs pour générer le tableau des valeurs
		$valeurs = array();
		foreach ($champs as $champ) {
			$valeurs[$champ] = _request($champ);
		}

		// On récupère le nom de l'envoyeur
		if ($options['champ_nom']) {
			$a_remplacer = array();
			if (preg_match_all('/@[\w]+@/', $options['champ_nom'], $a_remplacer)) {
				$a_remplacer = $a_remplacer[0];
				foreach ($a_remplacer as $cle => $val) {
					$a_remplacer[$cle] = trim($val, '@');
				}
				$a_remplacer = array_flip($a_remplacer);
				$a_remplacer = array_intersect_key($valeurs, $a_remplacer);
				$a_remplacer = array_merge($a_remplacer, array('nom_site_spip' => $nom_site_spip));
			}
			$nom_envoyeur = trim(_L($options['champ_nom'], $a_remplacer));
		}
		if (!$nom_envoyeur) {
			$nom_envoyeur = $nom_site_spip;
		}

		// On récupère le sujet s'il existe sinon on le construit
		if ($options['champ_sujet']) {
			$a_remplacer = array();
			if (preg_match_all('/@[\w]+@/', $options['champ_sujet'], $a_remplacer)) {
				$a_remplacer = $a_remplacer[0];
				foreach ($a_remplacer as $cle => $val) {
					$a_remplacer[$cle] = trim($val, '@');
				}
				$a_remplacer = array_flip($a_remplacer);
				$a_remplacer = array_intersect_key($valeurs, $a_remplacer);
				$a_remplacer = array_merge($a_remplacer, array('nom_site_spip' => $nom_site_spip));
			}
			$sujet = trim(_L($options['champ_sujet'], $a_remplacer));
		}
		if (!$sujet) {
			$sujet = _T('formidable:traiter_email_sujet', array('nom'=>$nom_envoyeur));
		}
		$sujet = filtrer_entites($sujet);

		// Mais quel va donc être le fond ?
		if (find_in_path('notifications/formulaire_'.$formulaire['identifiant'].'_email.html')) {
			$notification = 'notifications/formulaire_'.$formulaire['identifiant'].'_email';
		} else {
			$notification = 'notifications/formulaire_email';
		}

		// On génère le mail avec le fond
		$html = recuperer_fond(
			$notification,
			array(
				'id_formulaire' => $args['id_formulaire'],
				'titre' => _T_ou_typo($formulaire['titre']),
				'traitements' => $traitements,
				'saisies' => $saisies,
				'valeurs' => $valeurs,
				'ip' => $options['activer_ip']?$GLOBALS['ip']:''
			)
		);

		// On génère le texte brut
		include_spip('facteur_fonctions');
		$texte = facteur_mail_html2text($html);

		// On utilise la forme avancé de Facteur
		$corps = array(
			'html' => $html,
			'texte' => $texte,
			'nom_envoyeur' => filtrer_entites($nom_envoyeur),
		);
		// Si l'utilisateur n'a pas indiqué autrement, on met le courriel de l'envoyeur dans
		// Reply-To et on laisse le from par defaut de Facteur car sinon ca bloque sur les
		// SMTP un peu restrictifs.
		$courriel_from = '';
		if ($courriel_envoyeur && $options['activer_vrai_envoyeur']) {
			$courriel_from = $courriel_envoyeur;
		} else if ($courriel_envoyeur) {
			$corps['repondre_a'] = $courriel_envoyeur;
		}

		// On envoie enfin le message
		$envoyer_mail = charger_fonction('envoyer_mail', 'inc');

		// On envoie aux destinataires
		if ($destinataires) {
			$ok = $envoyer_mail($destinataires, $sujet, $corps, $courriel_from, 'X-Originating-IP: '.$GLOBALS['ip']);
		}

		// Si c'est bon, on envoie l'accusé de réception
		if ($ok and $courriel_envoyeur and $options['activer_accuse']) {
			// On récupère le sujet s'il existe sinon on le construit
			if ($options['sujet_accuse']) {
				$a_remplacer = array();
				if (preg_match_all('/@[\w]+@/', $options['sujet_accuse'], $a_remplacer)) {
					$a_remplacer = $a_remplacer[0];
					foreach ($a_remplacer as $cle => $val) {
						$a_remplacer[$cle] = trim($val, '@');
					}
					$a_remplacer = array_flip($a_remplacer);
					$a_remplacer = array_intersect_key($valeurs, $a_remplacer);
					$a_remplacer = array_merge($a_remplacer, array('nom_site_spip' => $nom_site_spip));
				}
				$sujet_accuse = trim(_L($options['sujet_accuse'], $a_remplacer));
			}
			if (!$sujet_accuse) {
				$sujet_accuse = _T('formidable:traiter_email_sujet_accuse');
			}
			$sujet_accuse = filtrer_entites($sujet_accuse);

			// Si un nom d'expéditeur est précisé pour l'AR, on l'utilise, sinon on utilise le nomde l'envoyeur du courriel principal
			$nom_envoyeur_accuse = trim($options['nom_envoyeur_accuse']);
			if (!$nom_envoyeur_accuse) {
				$nom_envoyeur_accuse = $nom_envoyeur;
			}

			//A fortiori, si un courriel d'expéditeur est précisé pour l'AR, on l'utilise
			if ($options['courriel_envoyeur_accuse']) {
				$courriel_envoyeur_accuse = $options['courriel_envoyeur_accuse'];
			} else {
				$courriel_envoyeur_accuse = $courriel_envoyeur;
			}

			//Et on teste si on doit mettre cela en from ou en reply-to
			if ($options['activer_vrai_envoyeur'] and $courriel_envoyeur_accuse) {
				$courriel_from_accuse = $courriel_envoyeur_accuse;
			} elseif ($courriel_envoyeur_accuse) {
				$corps['repondre_a'] = $courriel_envoyeur_accuse;
				$courriel_from_accuse = '';
			}

			// Mais quel va donc être le fond ?
			if (find_in_path('notifications/formulaire_'.$formulaire['identifiant'].'_accuse.html')) {
				$accuse = 'notifications/formulaire_'.$formulaire['identifiant'].'_accuse';
			} else {
				$accuse = 'notifications/formulaire_accuse';
			}

			// On génère l'accusé de réception
			$html_accuse = recuperer_fond(
				$accuse,
				array(
					'id_formulaire' => $formulaire['id_formulaire'],
					'titre' => _T_ou_typo($formulaire['titre']),
					'message_retour' => $formulaire['message_retour'],
					'traitements' => $traitements,
					'saisies' => $saisies,
					'valeurs' => $valeurs
				)
			);

			// On génère le texte brut
			$texte = facteur_mail_html2text($html_accuse);

			$corps = array(
				'html' => $html_accuse,
				'texte' => $texte,
				'nom_envoyeur' => filtrer_entites($nom_envoyeur_accuse),
			);

			$ok = $envoyer_mail($courriel_envoyeur, $sujet_accuse, $corps, $courriel_from_accuse, 'X-Originating-IP: '.$GLOBALS['ip']);
		}

		if ($ok) {
			$retours['message_ok'] .= "\n"._T('formidable:traiter_email_message_ok');
		} else {
			$retours['message_erreur'] .= "\n"._T('formidable:traiter_email_message_erreur');
		}
	}

	// noter qu'on a deja fait le boulot, pour ne pas risquer double appel
	$retours['traitements']['email'] = true;
	return $retours;
}


/**
 * Retourne la liste des destinataires sélectionnés en fonction
 * de l'option 'destinataires_selon_champ' du traitement email.
 * 
 * @param string $description
 *     Description saisie dans l'option du traitement du formulaire,
 *     qui respecte le schéma prévu, c'est à dire : 1 description par ligne,
 *     tel que `@champ@/valeur : mail@domain.tld, mail@domain.tld, ...`
 *     {@example : `@selection_2@/choix_1 : toto@domain.tld`}
 * @return array 
 *     Liste des destinataires, s'il y en a.
**/
function formidable_traiter_email_destinataire_selon_champ($description) {
	$destinataires = array();

	// 1 test à rechercher par ligne
	$descriptions = explode("\n", trim($description));
	$descriptions = array_map('trim', $descriptions);
	$descriptions = array_filter($descriptions);

	// pour chaque test, s'il est valide, ajouter les courriels indiqués
	foreach ($descriptions as $test) {
		// Un # est un commentaire
		if ($test[0] == '#') {
			continue;
		}
		// Le premier caractère est toujours un @
		if ($test[0] != '@') {
			continue;
		}


		list($champ, $reste) = explode('/', $test, 2);
		$champ = substr(trim($champ), 1, -1); // enlever les @

		if ($reste) {
			list($valeur, $mails) = explode(':', $reste, 2);
			$valeur = trim($valeur);
			$mails = explode(',', $mails);
			$mails = array_map('trim', $mails);
			$mails = array_filter($mails);
			if ($mails) {
				// obtenir la valeur du champ saisi dans le formulaire.
				// cela peut être un tableau.
				$champ = _request($champ);
				if (!is_null($champ)) {
					$ok = is_array($champ) ? in_array($valeur, $champ) : ($champ == $valeur);

					if ($ok) {
						$destinataires = array_merge($destinataires, $mails);
						$destinataires = array_unique($destinataires);
					}
				}
			}
		}
	}

	return $destinataires;
}
