<?php
// This is a SPIP language file  --  Ceci est un fichier langue de SPIP
// extrait automatiquement de http://trad.spip.net/tradlang_module/facteur?lang_cible=en
// ** ne pas modifier le fichier **

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

$GLOBALS[$GLOBALS['idx_lang']] = array(

	// C
	'config_info_enregistree' => 'Postman’s configuration is now saved',
	'configuration_adresse_envoi' => 'Sender’s address configuration', # MODIF
	'configuration_facteur' => 'Postman',
	'configuration_mailer' => 'Mailer’s configuration', # MODIF
	'configuration_smtp' => 'Choose your mailer',
	'configuration_smtp_descriptif' => 'If you’re not sure about the settings, leave them set to "PHP mail".',
	'corps_email_de_test' => 'This is a test email',

	// E
	'email_envoye_par' => 'Sent by @site@',
	'email_test_envoye' => 'The test email was successfully sent. If you do not receive it correctly, check the configuration of your server or contact a server administrator.',
	'erreur' => 'Error',
	'erreur_dans_log' => ': check the log file for more details',
	'erreur_generale' => 'There are one or more configuration errors. Please check the contents of the form.',
	'erreur_invalid_host' => 'This host name is not valid',
	'erreur_invalid_port' => 'This port number is not valid',

	// F
	'facteur_adresse_envoi_email' => 'Email:',
	'facteur_adresse_envoi_nom' => 'Name:',
	'facteur_bcc' => 'Blind Carbon Copy (BCC):',
	'facteur_cc' => 'Carbon Copy (CC):',
	'facteur_copies' => 'Copies:', # MODIF
	'facteur_copies_descriptif' => 'An email will be sent to specified adresses. One Carbon Copy and/or one Blind Carbon Copy.',
	'facteur_filtre_accents' => 'Transform accents into their html entities (useful for Hotmail).',
	'facteur_filtre_css' => 'Transform styles present between &lt;head&gt; and &lt;/head&gt; into inline styles, useful for webmails because inline styles overwrite external styles.',
	'facteur_filtre_images' => 'Embed images referenced in emails',
	'facteur_filtre_iso_8859' => 'Convert to ISO-8859-1',
	'facteur_filtres' => 'Filters',
	'facteur_filtres_descriptif' => 'Some filters can be applied before sending an email.',
	'facteur_smtp_auth' => 'Requires authentication:',
	'facteur_smtp_auth_non' => 'no',
	'facteur_smtp_auth_oui' => 'yes',
	'facteur_smtp_host' => 'Host:',
	'facteur_smtp_password' => 'Password:',
	'facteur_smtp_port' => 'Port:',
	'facteur_smtp_secure' => 'Secure:',
	'facteur_smtp_secure_non' => 'no',
	'facteur_smtp_secure_ssl' => 'SSL (depreciated)',
	'facteur_smtp_secure_tls' => 'TLS (recommended)',
	'facteur_smtp_sender' => 'Return-Path (optional)', # MODIF
	'facteur_smtp_sender_descriptif' => 'Define the Return-Path in the mail header, useful for error feedback, also in SMTP mode it defines the sender’s email.', # MODIF
	'facteur_smtp_username' => 'Username:',

	// M
	'message_identite_email' => 'The configuration of the plugin "factor" preset this email address for sending emails.',

	// N
	'note_test_configuration' => 'A test email will be sent to the "sender".', # MODIF

	// P
	'personnaliser' => 'Customize',

	// T
	'tester' => 'Test',
	'tester_la_configuration' => 'Test the config',

	// U
	'utiliser_mail' => 'Use mail function from PHP',
	'utiliser_reglages_site' => 'Use the site’s settings: the email address is the webmaster’s one and the name of the website is the name of the sender', # MODIF
	'utiliser_smtp' => 'Use SMTP',

	// V
	'valider' => 'Submit',
	'version_html' => 'HTML version.',
	'version_texte' => 'Text version.'
);
