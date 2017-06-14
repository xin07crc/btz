<?php
// This is a SPIP language file  --  Ceci est un fichier langue de SPIP
// extrait automatiquement de http://trad.spip.net/tradlang_module/facteur?lang_cible=fr_tu
// ** ne pas modifier le fichier **

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

$GLOBALS[$GLOBALS['idx_lang']] = array(

	// C
	'config_info_enregistree' => 'La configuration du facteur a bien été enregistrée',
	'configuration_adresse_envoi' => 'Configuration de l’adresse d’envoi', # MODIF
	'configuration_facteur' => 'Facteur',
	'configuration_mailer' => 'Configuration du mailer', # MODIF
	'configuration_smtp' => 'Choix de la méthode d’envoi de mail',
	'configuration_smtp_descriptif' => 'Si tu n’es pas sûr, choisis la fonction mail de PHP.',
	'corps_email_de_test' => 'Ceci est un email de test accentué',

	// E
	'email_envoye_par' => 'Envoyé par @site@',
	'email_test_envoye' => 'L’email de test a correctement été envoyé. Si tu ne le reçois pas correctement, vérifie la configuration de ton serveur ou contacte un administrateur du serveur.',
	'erreur' => 'Erreur',
	'erreur_dans_log' => ' : consulte le fichier log pour plus de détails',
	'erreur_generale' => 'Il y a une ou plusieurs erreurs de configuration. Vérifie le contenu du formulaire.',
	'erreur_invalid_host' => 'Ce nom d’hôte n’est pas correct',
	'erreur_invalid_port' => 'Ce numéro de port n’est pas correct',

	// F
	'facteur_adresse_envoi_email' => 'Email :',
	'facteur_adresse_envoi_nom' => 'Nom :',
	'facteur_bcc' => 'Copie Cachée (BCC) :',
	'facteur_cc' => 'Copie (CC) :',
	'facteur_copies' => 'Copies :', # MODIF
	'facteur_copies_descriptif' => 'Un email sera envoyé en copie aux adresses définies. Une seule adresse en copie et/ou une seule adresse en copie cachée.',
	'facteur_filtre_accents' => 'Transformer les accents en leur entités html (utile pour Hotmail notamment).',
	'facteur_filtre_css' => 'Transformer les styles contenus entre <head> et </head> en des styles "en ligne", utile pour les webmails car les styles en ligne ont la priorité sur les styles externes.',
	'facteur_filtre_images' => 'Embarquer les images référencées dans les emails',
	'facteur_filtre_iso_8859' => 'Convertir en ISO-8859-1',
	'facteur_filtres' => 'Filtres',
	'facteur_filtres_descriptif' => 'Des filtres peuvent être appliqués aux emails au moment de l’envoi.',
	'facteur_smtp_auth' => 'Requiert une authentification :',
	'facteur_smtp_auth_non' => 'non',
	'facteur_smtp_auth_oui' => 'oui',
	'facteur_smtp_host' => 'Hôte :',
	'facteur_smtp_password' => 'Mot de passe :',
	'facteur_smtp_port' => 'Port :',
	'facteur_smtp_secure' => 'Connexion sécurisée :',
	'facteur_smtp_secure_non' => 'non',
	'facteur_smtp_secure_ssl' => 'SSL', # MODIF
	'facteur_smtp_secure_tls' => 'TLS', # MODIF
	'facteur_smtp_sender' => 'Retour des erreurs (optionnel)', # MODIF
	'facteur_smtp_sender_descriptif' => 'Définit dans l’entête du mail l’adresse email de retour des erreurs (ou Return-Path), et lors d’un envoi via la méthode SMTP cela définit aussi l’adresse de l’envoyeur.', # MODIF
	'facteur_smtp_username' => 'Nom d’utilisateur :',

	// M
	'message_identite_email' => 'La configuration du plugin "facteur" surcharge cette adresse email pour l’envoi de courriels.',

	// N
	'note_test_configuration' => 'Un email sera envoyé à l’adresse d’envoi définie (ou celle du webmaster).', # MODIF

	// P
	'personnaliser' => 'Personnaliser ces réglages',

	// T
	'tester' => 'Tester',
	'tester_la_configuration' => 'Tester la configuration',

	// U
	'utiliser_mail' => 'Utiliser la fonction mail de PHP',
	'utiliser_reglages_site' => 'Utiliser les réglages du site SPIP : le nom affiché sera le nom du site SPIP et l’adresse email sera celle du webmaster', # MODIF
	'utiliser_smtp' => 'Utiliser SMTP',

	// V
	'valider' => 'Valider',
	'version_html' => 'Version HTML.',
	'version_texte' => 'Version texte.'
);
