<?php

#
# SPIP_LOADER recupere et installe la version stable de SPIP
#

# auteur(s) autorise(s) a proceder aux mises a jour : '1:2:3'
# (en tete, sinon defini trop tard !)
define('_SPIP_LOADER_UPDATE_AUTEURS', '1');

# repertoires d'installation
define('_DIR_BASE', './');
define('_DIR_PLUGINS', _DIR_BASE . 'plugins/');

# adresse du depot
define('_URL_SPIP_DEPOT', 'http://files.spip.org/');

######################### CONFIGURATION #
#
# pour les mises a jour effectuees avec ce script,
# toutes les constantes ci-dessous peuvent etre surchargees
# dans config/mes_options.php
#
# decommenter la ligne ci-dessous
# pour charger la version de developpement (nightly build SVN)
# et commenter la ligne de telechargement de la version STABLE
# define('_CHEMIN_FICHIER_ZIP', 'spip/dev/SPIP-svn.zip');

# decommenter la ligne ci-dessous
# pour charger la version stable de la branche 2.1
# et commenter la ligne de telechargement de la version STABLE
# define('_CHEMIN_FICHIER_ZIP', 'spip/stable/spip-2.1.zip');

# decommenter la ligne ci-dessous
# pour charger la version stable de la branche 3.0
# et commenter la ligne de telechargement de la version STABLE
# define('_CHEMIN_FICHIER_ZIP', 'spip/stable/spip-3.0.zip');

# Chemin du paquet de la version STABLE a telecharger
# pointe sur une branche donnee pour eviter les changements de branche involontaires et violents
define('_CHEMIN_FICHIER_ZIP', 'spip/stable/spip-3.1.zip');

# Adresse des librairies necessaires a spip_loader
# (pclzip et fichiers de langue)
define('_URL_LOADER_DL', 'http://www.spip.net/spip-dev/INSTALL/');
# telecharger a travers un proxy
define('_URL_LOADER_PROXY', '');

# surcharger le script
define('_NOM_PAQUET_ZIP', 'spip');
// par defaut le morceau de path a enlever est le nom : spip
define('_REMOVE_PATH_ZIP', _NOM_PAQUET_ZIP);

define('_SPIP_LOADER_PLUGIN_RETOUR', 'ecrire/?exec=admin_plugin&voir=tous');
define('_SPIP_LOADER_SCRIPT', 'spip_loader.php');

// "habillage" optionnel
// liste separee par virgules de fichiers inclus dans spip_loader
// charges a la racine comme spip_loader.php et pclzip.php
// selon l'extension: include .php , .css et .js dans le <head> genere par spip_loader
define('_SPIP_LOADER_EXTRA', '');

define('_DEST_PAQUET_ZIP', '');
define('_PCL_ZIP_SIZE', 249587);
define('_PCL_ZIP_RANGE', 200);

// version de spip-loader
// v 2.1 : introduction du parametre d'URL chemin
// v 2.2 : introduction du parametre d'URL dest
// v 2.3 : introduction du parametre d'URL range
// v 2.4 : redirection par meta refresh au lieu de header Location
// v 2.5 : affichage de la version à installer, de la version déjà installée (si elle existe),
//		   compatibilite PHP, loader obsolete
// v 2.7 : on télécharge maintenant SPIP 3.1 
define('_SPIP_LOADER_VERSION', '2.5.9');
#
#######################################################################

# langues disponibles
$langues = array (
	'ar' => "&#1593;&#1585;&#1576;&#1610;",
	'ast' => "asturianu",
	'br' => "brezhoneg",
	'ca' => "catal&#224;",
	'cs' => "&#269;e&#353;tina",
	'de' => "Deutsch",
	'en' => "English",
	'eo' => "Esperanto",
	'es' => "Espa&#241;ol",
	'eu' => "euskara",
	'fa' => "&#1601;&#1575;&#1585;&#1587;&#1609;",
	'fr' => "fran&#231;ais",
	'fr_tu' => "fran&#231;ais copain",
	'gl' => "galego",
	'hr' => "hrvatski",
	'id' => "Indonesia",
	'it' => "italiano",
	'km' => "Cambodian",
	'lb' => "L&euml;tzebuergesch",
	'nap' => "napulitano",
	'nl' => "Nederlands",
	'oc_lnc' => "&ograve;c lengadocian",
	'oc_ni' => "&ograve;c ni&ccedil;ard",
	'pt_br' => "Portugu&#234;s do Brasil",
	'ro' => "rom&#226;n&#259;",
	'sk' => "sloven&#269;ina",	// (Slovakia)
	'sv' => "svenska",
	'tr' => "T&#252;rk&#231;e",
	'wa' => "walon",
	'zh_tw' => "&#21488;&#28771;&#20013;&#25991;", // chinois taiwan (ecr. traditionnelle)
);

// Configuration des versions minimales de PHP en fonction des branches SPIP
$versions_php = array(
	'2.1' => '4.0.8',
	'3.0' => '5.1.0',
	'3.1' => '5.1.0',
	'dev' => '5.3.0',
);

// Url du fichier archivelist permettant de créer les zips de spip
define('_URL_ARCHIVELIST', 'http://core.spip.org/projects/spip/repository/raw/archivelist.txt');

// Url du fichier spip_loader permettant de tester sa version distante
define('_URL_SPIP_LOADER', _URL_LOADER_DL . 'spip_loader.php');

//
// Renvoie un tableau des versions SPIP dont l'index correspond à au chemin du fichier zip tel
// qu'utilisé par spip_loader
//
function lister_versions_spip() {

	$versions = array();

	// Récupération du fichier archivelist.txt du core
	$archivelist = recuperer_page(_URL_ARCHIVELIST);
	$contenu = explode("\n", $archivelist);

	// on supprime les retours chariot
	$contenu = array_filter($contenu, 'trim');
	// on supprime les lignes vides
	$contenu = array_filter($contenu);

	if ($contenu) {
		// On lit le fichier ligne par ligne et on
		foreach ($contenu as $ligne) {
			if (substr($ligne, 0, 1) != '#') {
				// C'est une ligne de definition d'un paquet :
				$parametres = explode(';', $ligne);
				// - et on extrait la version de spip du chemin svn
				$arbo_svn = rtrim($parametres[0], '/');
				$version = str_replace('spip-', '', basename($arbo_svn));
				// - on separe calcul le nom complet du zip
				$chemin = 'spip/' . $parametres[1] . '.zip';
				// - on determine l'état de l'archive (stable, dev, archives)
				$etat = substr($parametres[1], 0, strpos($parametres[1], '/'));
				// Ajout au tableau des versions
				$versions[$chemin] = array(
					'version' => $version,
					'etat' => $etat);
			}
		}
	}

	return $versions;
}

function branche_spip($version) {
	if ($version == 'spip') {
		return 'dev';
	}
	$v = explode('.', $version);
	$branche = $v[0] . '.' . (isset($v[1]) ? $v[1] : '0');
	return $branche;
}

// faut il mettre à jour le spip_loader ?
function spip_loader_necessite_maj() {
	return version_compare(_SPIP_LOADER_VERSION, spip_loader_recupere_version(), '<');
}

// trouver le numéro de version du dernier spip_loader
function spip_loader_recupere_version() {
	static $version = null;
	if (is_null($version)) {
		$version = false;
		$spip_loader = recuperer_page(_URL_SPIP_LOADER);
		if (preg_match("/define\('_SPIP_LOADER_VERSION', '([0-9.]*)'\)/", $spip_loader, $m)) {
			$version = $m[1];
		}
	}
	return $version;
}


//
// Traduction des textes de SPIP
//
function _TT($code, $args = array()) {
	global $lang;
	$code = str_replace('tradloader:', '', $code);
	$text = $GLOBALS['i18n_tradloader_'.$lang][$code];
	while (list($name, $value) = @each($args)) {
		$text = str_replace("@$name@", $value, $text);
	}
	return $text;
}

//
// Ecrire un fichier de maniere un peu sure
//
function ecrire_fichierT($fichier, $contenu) {

	$fp = @fopen($fichier, 'wb');
	$s = @fputs($fp, $contenu, $a = strlen($contenu));

	$ok = ($s == $a);

	@fclose($fp);

	if (!$ok) {
		@unlink($fichier);
	}

	return $ok;
}

function mkdir_recursif($chemin, $chmod) {
	$dirs = explode('/', $chemin);
	$d = array_shift($dirs);
	foreach ($dirs as $dir) {
		$d = "$d/$dir";
		if (!is_dir($d)) {
			mkdir($d, $chmod);
		}
	}
	return is_dir($chemin);
}

function move_all($src, $dest) {
	global $chmod;
	$dest = rtrim($dest, '/');

	if ($dh = opendir($src)) {
		while (($file = readdir($dh)) !== false) {
			if (in_array($file, array('.', '..'))) {
				continue;
			}
			$s = "$src/$file";
			$d = "$dest/$file";
			if (is_dir($s)) {
				if (!is_dir($d)) {
					if (!mkdir_recursif($d, $chmod)) {
						die("impossible de creer $d");
					}
				}
				move_all($s, $d);
				rmdir($s);
				// verifier qu'on en a pas oublie (arrive parfois il semblerait ...)
				// si cela arrive, on fait un clearstatcache, et on recommence un move all...
				if (is_dir($s)) {
					clearstatcache();
					move_all($s, $d);
					rmdir($s);
				}
			} else {
				if (is_file($s)) {
					rename($s, $d);
				}
			}
		}
		// liberer le pointeur sinon windows ne permet pas le rmdir eventuel
		closedir($dh);
	}
}

function regler_langue_navigateurT() {
	$accept_langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
	if (is_array($accept_langs)) {
		foreach ($accept_langs as $s) {
			if (preg_match('#^([a-z]{2,3})(-[a-z]{2,3})?(;q=[0-9.]+)?$#i', trim($s), $r)) {
				$lang = strtolower($r[1]);
				if (isset($GLOBALS['langues'][$lang])) {
					return $lang;
				}
			}
		}
	}
	return false;
}

function menu_languesT($lang, $script = '', $hidden = array()) {
	$r = '';
	if (preg_match(',action=([a-z_]+),', $script, $m)) {
		$r .= "<input type='hidden' name='action' value='".$m[1]."' />";
		$script .= '&amp;';
	} else {
		$script .= '?';
	}

	foreach ($hidden as $k => $v) {
		if ($v and $k!='etape') {
			$script .= "$k=$v&amp;";
		}
	}
	$r .= '<select name="lang"
		onchange="window.location=\''.$script.'lang=\'+this.value;">';

	foreach ($GLOBALS['langues'] as $l => $nom) {
		$r .= '<option value="'.$l.'"' . ($l == $lang ? ' selected="selected"' : '')
			. '>'.$nom."</option>\n";
	}
	$r .= '</select> <noscript><div><input type="submit" name="ok" value="ok" /></div></noscript>';
	return $r;
}


//
// Gestion des droits d'acces
//
function tester_repertoire() {
	global $chmod;

	$ok = false;
	$self = basename($_SERVER['PHP_SELF']);
	$uid = @fileowner('.');
	$uid2 = @fileowner($self);
	$gid = @filegroup('.');
	$gid2 = @filegroup($self);
	$perms = @fileperms($self);

	// Comparer l'appartenance d'un fichier cree par PHP
	// avec celle du script et du repertoire courant
	@rmdir('test');
	@unlink('test'); // effacer au cas ou
	@touch('test');
	if ($uid > 0 && $uid == $uid2 && @fileowner('test') == $uid) {
		$chmod = 0700;
	} else {
		if ($gid > 0 && $gid == $gid2 && @filegroup('test') == $gid) {
			$chmod = 0770;
		} else {
			$chmod = 0777;
		}
	}
	// Appliquer de plus les droits d'acces du script
	if ($perms > 0) {
		$perms = ($perms & 0777) | (($perms & 0444) >> 2);
		$chmod |= $perms;
	}
	@unlink('test');

	// Verifier que les valeurs sont correctes

	@mkdir('test', $chmod);
	@chmod('test', $chmod);
	$ok = is_dir('test') && is_writable('test');
	@rmdir('test');

	return $ok;
}

//
// Demarre une transaction HTTP (s'arrete a la fin des entetes)
// retourne un descripteur de fichier
//
function init_http($get, $url, $refuse_gz = false) {
	//global $http_proxy;
	$fopen = false;
	if (!preg_match(",^http://,i", _URL_LOADER_PROXY)) {
		$http_proxy = '';
	} else {
		$http_proxy = _URL_LOADER_PROXY;
	}

	$t = @parse_url($url);
	$host = $t['host'];
	if ($t['scheme'] == 'http') {
		$scheme = 'http';
		$scheme_fsock = '';
	} else {
		$scheme = $t['scheme'];
		$scheme_fsock = $scheme.'://';
	}
	if (!isset($t['port']) or !($port = $t['port'])) {
		$port = 80;
	}
	$query = isset($t['query']) ? $t['query'] : '';
	if (!isset($t['path']) or !($path = $t['path'])) {
		$path = "/";
	}

	if ($http_proxy) {
		$t2 = @parse_url($http_proxy);
		$proxy_host = $t2['host'];
		$proxy_user = $t2['user'];
		$proxy_pass = $t2['pass'];
		if (!($proxy_port = $t2['port'])) {
			$proxy_port = 80;
		}
		$f = @fsockopen($proxy_host, $proxy_port);
	} else {
		$f = @fsockopen($scheme_fsock.$host, $port);
	}

	if ($f) {
		if ($http_proxy) {
			fputs(
				$f,
				"$get $scheme://$host" . (($port != 80) ? ":$port" : "") .
				$path . ($query ? "?$query" : "") . " HTTP/1.0\r\n"
			);
		} else {
			fputs($f, "$get $path" . ($query ? "?$query" : "") . " HTTP/1.0\r\n");
		}
		$version_affichee = isset($GLOBALS['spip_version_affichee'])?$GLOBALS['spip_version_affichee']:"xx";
		fputs($f, "Host: $host\r\n");
		fputs($f, "User-Agent: SPIP-$version_affichee (http://www.spip.net/)\r\n");

		// Proxy authentifiant
		if (isset($proxy_user) and $proxy_user) {
			fputs($f, "Proxy-Authorization: Basic "
			. base64_encode($proxy_user . ":" . $proxy_pass) . "\r\n");
		}
	} elseif (!$http_proxy) {
		// fallback : fopen
		$f = @fopen($url, "rb");
		$fopen = true;
	} else {
		// echec total
		$f = false;
	}

	return array($f, $fopen);
}

//
// Recupere une page sur le net
// et au besoin l'encode dans le charset local
//
// options : get_headers si on veut recuperer les entetes
function recuperer_page($url) {

	// Accepter les URLs au format feed:// ou qui ont oublie le http://
	$url = preg_replace(',^feed://,i', 'http://', $url);
	if (!preg_match(',^[a-z]+://,i', $url)) {
		$url = 'http://'.$url;
	}

	// dix tentatives maximum en cas d'entetes 301...
	for ($i = 0; $i < 10; $i++) {
		list($f, $fopen) = init_http('GET', $url);

		// si on a utilise fopen() - passer a la suite
		if ($fopen) {
			break;
		} else {
			// Fin des entetes envoyees par SPIP
			fputs($f, "\r\n");

			// Reponse du serveur distant
			$s = trim(fgets($f, 16384));
			if (preg_match(',^HTTP/[0-9]+\.[0-9]+ ([0-9]+),', $s, $r)) {
				$status = $r[1];
			} else {
				return;
			}

			// Entetes HTTP de la page
			$headers = '';
			while ($s = trim(fgets($f, 16384))) {
				$headers .= $s."\n";
				if (preg_match(',^Location: (.*),i', $s, $r)) {
					$location = $r[1];
				}
				if (preg_match(",^Content-Encoding: .*gzip,i", $s)) {
					$gz = true;
				}
			}
			if ($status >= 300 and $status < 400 and $location) {
				$url = $location;
			} elseif ($status != 200) {
				return;
			} else {
				break; # ici on est content
			}
			fclose($f);
			$f = false;
		}
	}

	// Contenu de la page
	if (!$f) {
		return false;
	}

	$result = '';
	while (!feof($f)) {
		$result .= fread($f, 16384);
	}
	fclose($f);

	// Decompresser le flux
	if (isset($_GET['gz']) and $gz = $_GET['gz']) {
		$result = gzinflate(substr($result, 10));
	}

	return $result;
}

function telecharger_langue($lang, $droits) {

	$fichier = 'tradloader_'.$lang.'.php';
	$GLOBALS['idx_lang'] = 'i18n_tradloader_'.$lang;
	if (!file_exists(_DIR_BASE.$fichier)) {
		$contenu = recuperer_page(_URL_LOADER_DL.$fichier.".txt");
		if ($contenu and $droits) {
			ecrire_fichierT(_DIR_BASE.$fichier, $contenu);
			include(_DIR_BASE.$fichier);
			return true;
		} elseif ($contenu and !$droits) {
			eval('?'.'>'.$contenu);
			return true;
		} else {
			return false;
		}
	} else {
		include(_DIR_BASE.$fichier);
		return true;
	}
}

function selectionner_langue($droits) {
	global $langues; # langues dispo

	$lang = '';

	if (isset($_COOKIE['spip_lang_ecrire'])) {
		$lang = $_COOKIE['spip_lang_ecrire'];
	}

	if (isset($_REQUEST['lang'])) {
		$lang = $_REQUEST['lang'];
	}

	# reglage par defaut selon les preferences du brouteur
	if (!$lang or !isset($langues[$lang])) {
		$lang = regler_langue_navigateurT();
	}

	# valeur par defaut
	if (!isset($langues[$lang])) {
		$lang = 'fr';
	}

	# memoriser dans un cookie pour l'etape d'apres *et* pour l'install
	setcookie('spip_lang_ecrire', $lang);

	# RTL
	if ($lang == 'ar' or $lang == 'he' or $lang == 'fa') {
		$GLOBALS['spip_lang_right']='left';
		$GLOBALS['spip_lang_dir']='rtl';
	} else {
		$GLOBALS['spip_lang_right']='right';
		$GLOBALS['spip_lang_dir']='ltr';
	}

	# code de retour = capacite a telecharger le fichier de langue
	$GLOBALS['idx_lang'] = 'i18n_tradloader_'.$lang;
	return telecharger_langue($lang, $droits) ? $lang : false;
}

function debut_html($corps = '', $hidden = array()) {

	global $lang, $spip_lang_dir, $spip_lang_right, $version_installee;

	if ($version_installee) {
		$titre = _TT('tradloader:titre_maj', array('paquet'=>strtoupper(_NOM_PAQUET_ZIP)));
	} else {
		$titre = _TT('tradloader:titre', array('paquet'=>strtoupper(_NOM_PAQUET_ZIP)));
	}
	$css = $js = '';
	foreach (explode(',', _SPIP_LOADER_EXTRA) as $fil) {
		switch (strrchr($fil, '.')) {
			case '.css':
				$css .= '
	<!-- css pour tuning optionnel, au premier chargement, il manquera si pas droits ... -->
	<link rel="stylesheet" href="' . basename($fil) . '" type="text/css" media="all" />';
				break;
			case '.js':
				$js .= '
	<!-- js pour tuning optionnel, au premier chargement, il manquera... -->
	<script src="' . basename($fil) . '" type="text/javascript"></script>';
				break;
		}
	}

	$hid = '';
	foreach ($hidden as $k => $v) {
		$hid .= "<input type='hidden' name='$k' value='$v' />\n";
	}
	$script = _DIR_BASE . _SPIP_LOADER_SCRIPT;
	echo
	"<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
	<html 'xml:lang=$lang' dir='$spip_lang_dir'>
	<head>
	<title>$titre</title>
	<meta http-equiv='Expires' content='0' />
	<meta http-equiv='cache-control' content='no-cache,no-store' />
	<meta http-equiv='pragma' content='no-cache' />
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
	<style type='text/css'>
	body {
		font-family:Verdana, Geneva, sans-serif;
		font-size:.9em;
		color: #222;
		background-color: #f8f7f3;
	}
	#main {
		margin:5em auto;
		padding:3em 2em;
		background-color:#fff;
		border-radius:2em;
		box-shadow: 0 0 20px #666;
		width:34em;
	}
	a {
		color: #E86519;
	}
	a:hover {
		color:#FF9900;
	}
	h1 {
		color:#5F4267;
		display:inline;
		font-size:1.6em;
	}
	h2 {
		font-weigth: normal;
		font-size: 1.2em;
	}
	div {
		line-height:140%;
	}
	div.progression {
		margin-top:2em;
		font-weight:bold;
		font-size:1.4em;
		text-align:center;
	}
	.bar {border:1px solid #aaa;}
	.bar div {background:#aaa;height:1em;}
	.version {background:#eee;margin:1em 0;padding:.5em;}
	.version-courante {color:#888;}
	.erreur {border-left:4px solid #f00; padding:1em 1em 1em 2em; background:#FCD4D4;}
	.info {border-left:4px solid #FFA54A; padding:1em 1em 1em 2em; background:#FFEED9; margin:1em 0;}
	</style>$css$js
	</head>
	<body>
	<div id='main'>
	<form action='" . $script . "' method='get'>" .
	"<div style='float:$spip_lang_right'>" .
	menu_languesT($lang, $script, $hidden) .
	"</div>
	<div>
  <h1>" . $titre . "</h1>". $corps .
	$hid .
	"</div></form>";
}

function fin_html()
{
	global $taux;
	echo ($taux ? '
	<div id="taux" style="display:none">'.$taux.'</div>' : '') .
	'
	<p style="text-align:right;font-size:x-small;">spip_loader '
	. _SPIP_LOADER_VERSION
	.'</p>
  </div>
	</body>
	</html>
	';

	// forcer l'envoi du buffer par tous les moyens !
	echo(str_repeat("<br />\r\n", 256));
	while (@ob_get_level()) {
		@ob_flush();
		@flush();
		@ob_end_flush();
	}
}

function nettoyer_racine($fichier) {

	@unlink($fichier);
	@unlink(_DIR_BASE.'pclzip.php');
	$d = opendir(_DIR_BASE);
	while (false !== ($f = readdir($d))) {
		if (preg_match('/^tradloader_(.+).php$/', $f)) {
			@unlink(_DIR_BASE.$f);
		}
	}
	closedir($d);
	return true;
}
// un essai pour parer le probleme incomprehensible des fichiers pourris
function touchCallBack($p_event, &$p_header)
{
	// bien extrait ?
	if ($p_header['status'] == 'ok') {
		// allez, on touche le fichier, le @ est pour les serveurs sous Windows qui ne comprennent pas touch()
		@touch($p_header['filename']);
	}
	return 1;
}
function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function verifie_zlib_ok()
{
	global $taux;
	if (!function_exists("gzopen") and !function_exists("gzopen64")) {
		return false;
	}

	if (!file_exists($f = _DIR_BASE . 'pclzip.php')) {
		$taux = microtime_float();
		$contenu = recuperer_page(_URL_LOADER_DL . 'pclzip.php.txt');
		if ($contenu) {
			ecrire_fichierT($f, $contenu);
		}
		$taux = _PCL_ZIP_SIZE / (microtime_float() - $taux);
	}
	include $f;
	$necessaire = array();
	foreach (explode(',', _SPIP_LOADER_EXTRA) as $fil) {
			$necessaire[$fil] = strrchr($fil, '.') == '.php' ? '.txt' : '';
	}
	foreach ($necessaire as $fil => $php) {
		if (!file_exists($f = _DIR_BASE . basename($fil))) {
			$contenu = recuperer_page(_URL_LOADER_DL . $fil . $php);
			if ($contenu) {
					ecrire_fichierT($f, $contenu);
			}
		}
		if ($php) {
			include $f;
		}
	}
	return true;
}

function spip_loader_reinstalle() {
	if (!defined('_SPIP_LOADER_UPDATE_AUTEURS')) {
		define('_SPIP_LOADER_UPDATE_AUTEURS', '1');
	}
	if (!isset($GLOBALS['auteur_session']['statut']) or
		$GLOBALS['auteur_session']['statut'] != '0minirezo' or
		!in_array($GLOBALS['auteur_session']['id_auteur'], explode(':', _SPIP_LOADER_UPDATE_AUTEURS))) {
		include_spip('inc/headers');
		include_spip('inc/minipres');
		http_status('403');
		install_debut_html();
		echo _T('ecrire:avis_non_acces_page');
		install_fin_html();
		exit;
	}
}

function spip_deballe_paquet($paquet, $fichier, $dest, $range) {
	global $chmod;

	// le repertoire temporaire est invariant pour permettre la reprise
	@mkdir($tmp = _DIR_BASE.'zip_'.md5($fichier), $chmod);
	$ok = is_dir($tmp);

	$zip = new PclZip($fichier);
	$content = $zip->listContent();
	$max_index = count($content);
	$start_index = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;

	if ($start_index < $max_index) {
		if (!$range) {
			$range = _PCL_ZIP_RANGE;
		}
		$end_index = min($start_index + $range, $max_index);
		$ok &= $zip->extractByIndex(
			"$start_index-$end_index",
			PCLZIP_OPT_PATH,
			$tmp,
			PCLZIP_OPT_SET_CHMOD,
			$chmod,
			PCLZIP_OPT_REPLACE_NEWER,
			PCLZIP_OPT_REMOVE_PATH,
			_REMOVE_PATH_ZIP."/",
			PCLZIP_CB_POST_EXTRACT,
			'touchCallBack'
		);
	}

	if (!$ok or $zip->error_code < 0) {
		debut_html();
		echo _TT('tradloader:donnees_incorrectes', array('erreur' => $zip->errorInfo()));
		fin_html();
	} else {
		// si l'extraction n'est pas finie, relancer
		if ($start_index < $max_index) {

			$url = _DIR_BASE._SPIP_LOADER_SCRIPT
			.  (strpos(_SPIP_LOADER_SCRIPT, '?') ? '&' : '?')
			. "etape=fichier&chemin=$paquet&dest=$dest&start=$end_index";
			$progres = $start_index/$max_index;
			spip_redirige_boucle($url, $progres);
		}

		if ($dest) {
			@mkdir(_DIR_PLUGINS, $chmod);
			$dir = _DIR_PLUGINS . $dest;
			$url = _DIR_BASE._SPIP_LOADER_PLUGIN_RETOUR;
		} else {
			$dir =  _DIR_BASE;
			$url = _DIR_BASE._SPIP_LOADER_URL_RETOUR;
		}
		move_all($tmp, $dir);
		rmdir($tmp);
		nettoyer_racine($fichier);
		header("Location: $url");
	}
}

function spip_redirige_boucle($url, $progres = ''){
	//@apache_setenv('no-gzip', 1); // provoque page blanche chez certains hebergeurs donc ne pas utiliser
	@ini_set('zlib.output_compression', '0'); // pour permettre l'affichage au fur et a mesure
	@ini_set('output_buffering', 'off');
	@ini_set('implicit_flush', 1);
	@ob_implicit_flush(1);
	$corps = '<meta http-equiv="refresh" content="0;'.$url.'">';
	if ($progres) {
		$corps .="<div class='progression'>".round($progres*100)."%</div>
				  <div class='bar'><div style='width:".round($progres*100)."%'></div></div>
				";
	}
	debut_html($corps);
	fin_html();
	exit;
}

function spip_presente_deballe($fichier, $paquet, $dest, $range) {
	global $version_installee, $versions_php;

	$nom = (_DEST_PAQUET_ZIP == '') ?
			_TT('tradloader:ce_repertoire') :
			(_TT('tradloader:du_repertoire').
				' <tt>'._DEST_PAQUET_ZIP.'</tt>');

	$hidden = array('chemin' => $paquet,
			'dest' => $dest,
			'range' => $range,
			'etape' => file_exists($fichier) ? 'fichier' : 'charger');

	// Version proposée à l'installation par défaut
	$versions_spip = lister_versions_spip();
	$version_future = 'SPIP ' . $versions_spip[_CHEMIN_FICHIER_ZIP]['version'];
	if ($versions_spip[_CHEMIN_FICHIER_ZIP]['etat'] == 'dev') {
		$version_future .= '-dev';
	}

	if ($version_installee) {
		// Mise à jour
		$bloc_courant =
			'<div class="version-courante">'
			. _TT('tradloader:titre_version_courante')
			. '<strong>'. 'SPIP ' . $version_installee .'</strong>'
			. '</div>';
		$bouton = _TT('tradloader:bouton_suivant_maj');
	} else {
		// Installation nue
		$bloc_courant = '';
		$bouton = _TT('tradloader:bouton_suivant');
	}

	// Détection d'une incompatibilité avec la version de PHP installée
	$branche_future = branche_spip($versions_spip[_CHEMIN_FICHIER_ZIP]['version']);
	$version_php_installee = phpversion();
	$version_php_spip = $versions_php[$branche_future];
	$php_incompatible = version_compare($version_php_spip, $version_php_installee, '>');

	if ($php_incompatible) {
		$bouton =
			'<div class="erreur">'
			. _TT('tradloader:echec_php', array('php1' => $version_php_installee, 'php2' => $version_php_spip))
			. '</div>';
	} else {
		$bouton =
			"<div style='text-align:".$GLOBALS['spip_lang_right']."'>"
			. '<input type="submit" value="' . $bouton . '" />'
			. '</div>';
	}

	// Construction du corps
	$corps =
		_TT('tradloader:texte_intro', array('paquet'=>strtoupper(_NOM_PAQUET_ZIP),'dest'=> $nom))
		. '<div class="version">'
		. $bloc_courant
		. '<div class="version-future">'
		. _TT('tradloader:titre_version_future')
		. '<strong>'. $version_future. '</strong>'
		. '</div>'
		. '</div>'
		. $bouton;

	if (spip_loader_necessite_maj()) {
		$corps .=
			"<div class='info'><a href='" . _URL_SPIP_LOADER . "'>"
			. _TT('tradloader:spip_loader_maj', array('version' => spip_loader_recupere_version()))
			. "</a></div>";
	}

	debut_html($corps, $hidden);
	fin_html();
}

function spip_recupere_paquet($paquet, $fichier, $dest, $range)
{
	$contenu = recuperer_page(_URL_SPIP_DEPOT . $paquet);

	if (!($contenu and ecrire_fichierT($fichier, $contenu))) {
		debut_html();
		echo _TT('tradloader:echec_chargement'), "$paquet, $fichier, $range" ;
		fin_html();
	} else {
		// Passer a l'etape suivante (desarchivage)
		$sep = strpos(_SPIP_LOADER_SCRIPT, '?') ? '&' : '?';
		header("Location: "._DIR_BASE._SPIP_LOADER_SCRIPT.$sep."etape=fichier&chemin=$paquet&dest=$dest&range=$range");
	}
}

function spip_deballe($paquet, $etape, $dest, $range)
{
	$fichier = _DIR_BASE . basename($paquet);

	if ($etape == 'fichier'	and file_exists($fichier)) {
		// etape finale: deploiement de l'archive
		spip_deballe_paquet($paquet, $fichier, $dest, $range);

	} elseif ($etape == 'charger') {

		// etape intermediaire: charger l'archive
		spip_recupere_paquet($paquet, $fichier, $dest, $range);

	} else {
		// etape intiale, afficher la page de presentation
		spip_presente_deballe($fichier, $paquet, $dest, $range);
	}
}

///////////////////////////////////////////////
// debut du process
//

error_reporting(E_ALL ^ E_NOTICE);

// PHP >= 5.3 rale si cette init est absente du php.ini et consorts
// On force a defaut de savoir anticiper l'erreur (il doit y avoir mieux)
if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('Europe/Paris');
}
$GLOBALS['taux'] = 0; // calcul eventuel du taux de transfert+dezippage

// En cas de reinstallation, verifier que le demandeur a les droits avant tout
// definir _FILE_CONNECT a autre chose que machin.php si on veut pas
$version_installee = '';
if (@file_exists('ecrire/inc_version.php')) {
	define('_SPIP_LOADER_URL_RETOUR', "ecrire/?exec=accueil");
	include_once 'ecrire/inc_version.php';
	$version_installee = $GLOBALS['spip_version_branche'];
	if ((defined('_FILE_CONNECT') and
		_FILE_CONNECT and
		strpos(_FILE_CONNECT, '.php')) or
		defined('_SITES_ADMIN_MUTUALISATION')) {
		spip_loader_reinstalle();
	}
} else {
	define('_SPIP_LOADER_URL_RETOUR', "ecrire/?exec=install");
}

$droits = tester_repertoire();

$GLOBALS['lang'] = selectionner_langue($droits);

if (!$GLOBALS['lang']) {
	//on ne peut pas telecharger
	$GLOBALS['lang'] = 'fr'; //francais par defaut
	$GLOBALS['i18n_tradloader_fr']['titre'] = 'T&eacute;l&eacute;chargement de SPIP';
	$GLOBALS['i18n_tradloader_fr']['echec_chargement'] = '<h4>Le chargement a &eacute;chou&eacute;.'.
	' Veuillez r&eacute;essayer, ou utiliser l\'installation manuelle.</h4>';
	debut_html();
	echo _TT('tradloader:echec_chargement');
	fin_html();
} elseif (!$droits) {
	//on ne peut pas ecrire
	debut_html();
	$q = $_SERVER['QUERY_STRING'];
	echo _TT(
		'tradloader:texte_preliminaire',
		array(
			'paquet' => strtoupper(_NOM_PAQUET_ZIP),
			'href'   => ('spip_loader.php' . ($q ? "?$q" : '')),
			'chmod'  => sprintf('%04o', $chmod)
		)
	);
	fin_html();
} elseif (!verifie_zlib_ok()) {
	// on ne peut pas decompresser
	die('fonctions zip non disponibles');
} else {

	// y a tout ce qu'il faut pour que cela marche
	$dest = '';
	$paquet = _CHEMIN_FICHIER_ZIP;
	if (isset($_REQUEST['dest']) and preg_match('/^[\w-_.]+$/', $_REQUEST['dest'])) {
		$dest = $_REQUEST['dest'];
	}
	if (isset($_REQUEST['chemin']) and $_REQUEST['chemin']) {
		$paquet = urldecode($_REQUEST['chemin']);
	}

	if ((strpos($paquet, '../') !== false) or (substr($paquet, -4, 4) != '.zip')) {
		die("chemin incorrect $paquet");
	} else {
		spip_deballe(
			$paquet,
			(isset($_REQUEST['etape']) ? $_REQUEST['etape'] : ''),
			$dest,
			intval(isset($_REQUEST['range']) ? $_REQUEST['range'] : 0)
		);
	}
}
