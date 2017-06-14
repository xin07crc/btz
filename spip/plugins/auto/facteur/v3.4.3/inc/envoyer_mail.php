<?php
/*
 * Plugin Facteur 2
 * (c) 2009-2011 Collectif SPIP
 * Distribue sous licence GPL
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('classes/facteur');
// inclure le fichier natif de SPIP, pour les fonctions annexes
include_once _DIR_RESTREINT."inc/envoyer_mail.php";

/**
 * @param string $destinataire
 * @param string $sujet
 * @param string|array $corps
 *   au format string, c'est un corps d'email au format texte, comme supporte nativement par le core
 *   au format array, c'est un corps etendu qui peut contenir
 *     string texte : le corps d'email au format texte
 *     string html : le corps d'email au format html
 *     string from : email de l'envoyeur (prioritaire sur argument $from de premier niveau, deprecie)
 *     string nom_envoyeur : un nom d'envoyeur pour completer l'email from
 *     string cc : destinataires en copie conforme
 *     string bcc : destinataires en copie conforme cachee
 *     string|array repondre_a : une ou plusieurs adresses à qui répondre
 *     string adresse_erreur : addresse de retour en cas d'erreur d'envoi
 *     array pieces_jointes : listes de pieces a embarquer dans l'email, chacune au format array :
 *       string chemin : chemin file system pour trouver le fichier a embarquer
 *       string nom : nom du document tel qu'apparaissant dans l'email
 *       string encodage : encodage a utiliser, parmi 'base64', '7bit', '8bit', 'binary', 'quoted-printable'
 *       string mime : mime type du document
 *     array headers : tableau d'en-tetes personalises, une entree par ligne d'en-tete
 *     bool exceptions : lancer une exception en cas d'erreur (false par defaut)
 * @param string $from (deprecie, utiliser l'entree from de $corps)
 * @param string $headers (deprecie, utiliser l'entree headers de $corps)
 * @return bool
 */
function inc_envoyer_mail($destinataire, $sujet, $corps, $from = "", $headers = "") {
	$message_html	= '';
	$message_texte	= '';
	$nom_envoyeur = $cc = $bcc = $repondre_a = '';
	$pieces_jointes = array();

	// si $corps est un tableau -> fonctionnalites etendues
	// avec entrees possible : html, texte, pieces_jointes, nom_envoyeur, ...
	if (is_array($corps)) {
		$message_html   = isset($corps['html']) ? $corps['html'] : "";
		$message_texte  = isset($corps['texte']) ? nettoyer_caracteres_mail($corps['texte']) : "";
		$pieces_jointes = isset($corps['pieces_jointes']) ? $corps['pieces_jointes'] : array();
		$nom_envoyeur   = isset($corps['nom_envoyeur']) ? $corps['nom_envoyeur'] : "";
		$from = isset($corps['from']) ? $corps['from']: $from;
		$cc   = isset($corps['cc']) ? $corps['cc'] : "";
		$bcc  = isset($corps['bcc']) ? $corps['bcc'] : "";
		$repondre_a = isset($corps['repondre_a']) ? $corps['repondre_a'] : "";
		$adresse_erreur = isset($corps['adresse_erreur']) ? $corps['adresse_erreur'] : "";
		$headers = isset($corps['headers']) ? $corps['headers'] : $headers;
		if (is_string($headers)){
			$headers = array_map('trim',explode("\n",$headers));
			$headers = array_filter($headers);
		}
	}
	// si $corps est une chaine -> compat avec la fonction native SPIP
	// gerer le cas ou le corps est du html avec un Content-Type: text/html dans les headers
	else {
		if (preg_match(',Content-Type:\s*text/html,ims',$headers)){
			$message_html	= $corps;
		}
		else {
			// Autodetection : tester si le mail est en HTML
			if (strpos($headers,"Content-Type:")===false
				AND strpos($corps,"<")!==false // eviter les tests suivants si possible
				AND $ttrim = trim($corps)
				AND substr($ttrim,0,1)=="<"
				AND substr($ttrim,-1,1)==">"
				AND stripos($ttrim,"</html>")!==false){

				if(!strlen($sujet)){
					// dans ce cas on ruse un peu : extraire le sujet du title
					if (preg_match(",<title>(.*)</title>,Uims",$corps,$m))
						$sujet = $m[1];
					else {
						// fallback, on prend le body si on le trouve
						if (preg_match(",<body[^>]*>(.*)</body>,Uims",$corps,$m))
							$ttrim = $m[1];

						// et on extrait la premiere ligne de vrai texte...
						// nettoyer le html et les retours chariots
						$ttrim = textebrut($ttrim);
						$ttrim = str_replace("\r\n", "\r", $ttrim);
						$ttrim = str_replace("\r", "\n", $ttrim);
						// decouper
						$ttrim = explode("\n",trim($ttrim));
						// extraire la premiere ligne de texte brut
						$sujet = array_shift($ttrim);
					}
				}
				$message_html	= $corps;
			}
			// c'est vraiment un message texte
			else
				$message_texte	= nettoyer_caracteres_mail($corps);
		}
		$headers = array_map('trim',explode("\n",$headers));
		$headers = array_filter($headers);
	}
	$sujet = nettoyer_titre_email($sujet);

	// si le mail est en texte brut, on l'encapsule dans un modele surchargeable
	// pour garder le texte brut, il suffit de faire un modele qui renvoie uniquement #ENV*{texte}
	if ($message_texte AND ! $message_html){
		$message_html = recuperer_fond("emails/texte",array('texte'=>$message_texte,'sujet'=>$sujet));
	}
	// si le mail est en HTML sans alternative, la generer
	if ($message_html AND !$message_texte){
		$message_texte = facteur_mail_html2text($message_html);
	}

	$exceptions = false;
	if (is_array($corps) AND isset($corps['exceptions'])){
		$exceptions = $corps['exceptions'];
	}
	
	// mode TEST : forcer l'email
	if (defined('_TEST_EMAIL_DEST')) {
		if (!_TEST_EMAIL_DEST){
			spip_log($e="Envois bloques par la constante _TEST_EMAIL_DEST", 'mail.' . _LOG_ERREUR);
			if ($exceptions) {
				throw new Exception($e);
			}
			return false;
		}
		else
			$destinataire = _TEST_EMAIL_DEST;
	}

	// plusieurs destinataires peuvent etre fournis separes par des virgules
	// c'est un format standard dans l'envoi de mail
	// les passer au format array pour phpMailer
	// mais ne pas casser si on a deja un array en entree
	// si pas destinataire du courriel on renvoie false (eviter les warning PHP : ligne 464 de phpmailer-php5/class.phpmailer.php
	// suppression des adresses de courriels invalides, si aucune valide, renvoyer false (eviter un warning PHP : ligne 464 de phpmailer-php5/class.phpmailer.php)
	if (is_array($destinataire))
		$destinataire = implode(", ",$destinataire);

	if(strlen($destinataire) > 0){
		$destinataire = array_map('trim',explode(",",$destinataire));
		foreach ($destinataire as $key => $value) {
			if(!email_valide($value))
				unset($destinataire[$key]);
		}
		if(count($destinataire) == 0) {
			spip_log($e="Aucune adresse email de destination valable pour l'envoi du courriel.", 'mail.' . _LOG_ERREUR);
			if ($exceptions) {
				throw new Exception($e);
			}
			return false;
		}
	}
	else {
		spip_log($e="Aucune adresse email de destination valable pour l'envoi du courriel.", 'mail.' . _LOG_ERREUR);
		if ($exceptions) {
			throw new Exception($e);
		}
		return false;
	}

	// On crée l'objet Facteur (PHPMailer) pour le manipuler ensuite
	$facteur = new Facteur($destinataire, $sujet, $message_html, $message_texte);
	if (is_array($corps) AND isset($corps['exceptions'])){
		$facteur->SetExceptions($corps['exceptions']);
	}
	
	// On ajoute le courriel de l'envoyeur s'il est fournit par la fonction
	if (empty($from) AND empty($facteur->From)) {
		$from = $GLOBALS['meta']["email_envoi"];
		if (empty($from) OR !email_valide($from)) {
			spip_log("Meta email_envoi invalide. Le mail sera probablement vu comme spam.", 'mail.' . _LOG_ERREUR);
			if(is_array($destinataire) && count($destinataire) > 0)
				$from = $destinataire[0];
			else
				$from = $destinataire;
		}
	}

	// "Marie Toto <Marie@toto.com>"
	if (preg_match(",^([^<>\"]*)<([^<>\"]+)>$,i",$from,$m)){
		$nom_envoyeur = trim($m[1]);
		$from = trim($m[2]);
	}
	if (!empty($from)){
		$facteur->From = $from;
		// la valeur par défaut de la config n'est probablement pas valable pour ce mail,
		// on l'écrase pour cet envoi
		$facteur->FromName = $from;
	}

	// On ajoute le nom de l'envoyeur s'il fait partie des options
	if ($nom_envoyeur)
		$facteur->FromName = $nom_envoyeur;

	// Si plusieurs emails dans le from, pas de Name !
	if (strpos($facteur->From,",")!==false){
		$facteur->FromName = "";
	}

	// S'il y a des copies à envoyer
	if ($cc){
		if (is_array($cc))
			foreach ($cc as $courriel)
				$facteur->AddCC($courriel);
		else
			$facteur->AddCC($cc);
	}
	
	// S'il y a des copies cachées à envoyer
	if ($bcc){
		if (is_array($bcc))
			foreach ($bcc as $courriel)
				$facteur->AddBCC($courriel);
		else
			$facteur->AddBCC($bcc);
	}
	
	// S'il y a une adresse de reply-to
	if ($repondre_a){
		if (is_array($repondre_a))
			foreach ($repondre_a as $courriel)
				$facteur->AddReplyTo($courriel);
		else
			$facteur->AddReplyTo($repondre_a);
	}
	
	// S'il y a des pièces jointes on les ajoute proprement
	if (count($pieces_jointes)) {
		foreach ($pieces_jointes as $piece) {
			$facteur->AddAttachment(
				$piece['chemin'],
				isset($piece['nom']) ? $piece['nom']:'',
				(isset($piece['encodage']) AND in_array($piece['encodage'],array('base64', '7bit', '8bit', 'binary', 'quoted-printable'))) ? $piece['encodage']:'base64',
				isset($piece['mime']) ? $piece['mime']:Facteur::_mime_types(pathinfo($piece['chemin'], PATHINFO_EXTENSION))
			);
		}
	}

	// Si une adresse email a été spécifiée pour les retours en erreur, on l'ajoute
	if (!empty($adresse_erreur))
		$facteur->Sender = $adresse_erreur;

	// si entetes personalises : les ajouter
	// attention aux collisions : si on utilise l'option cc de $corps
	// et qu'on envoie en meme temps un header Cc: xxx, yyy
	// on aura 2 lignes Cc: dans les headers
	if (!empty($headers)) {
		foreach($headers as $h){
			// verifions le format correct : il faut au moins un ":" dans le header
			// et on filtre le Content-Type: qui sera de toute facon fourni par facteur
			if (strpos($h,":")!==false
			  AND strncmp($h,"Content-Type:",13)!==0)
				$facteur->AddCustomHeader($h);
		}
	}
	
	// On passe dans un pipeline pour modifier tout le facteur avant l'envoi
	$facteur = pipeline('facteur_pre_envoi', $facteur);
	
	// On génère les headers
	$head = $facteur->CreateHeader();

	// Et c'est parti on envoie enfin
	spip_log("mail via facteur\n$head"."Destinataire:".print_r($destinataire,true),'mail');
	spip_log("mail\n$head"."Destinataire:".print_r($destinataire,true),'facteur');
	$retour = $facteur->Send();
	
	if (!$retour){
		spip_log("Erreur Envoi mail via Facteur : ".print_r($facteur->ErrorInfo,true),'mail.'._LOG_ERREUR);
	}

	return $retour ;
}
