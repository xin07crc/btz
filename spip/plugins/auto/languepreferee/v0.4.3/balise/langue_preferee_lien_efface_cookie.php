<?php
/*
 * langue_preferee
 * Langue preferee par l'internaute
 *
 * Auteur :
 * Nicolas Hoizey
 * © 2007 - Distribue sous licence GNU/GPL
 */

function balise_LANGUE_PREFEREE_LIEN_EFFACE_COOKIE($p)
{
	return calculer_balise_dynamique($p, 'LANGUE_PREFEREE_LIEN_EFFACE_COOKIE', array());
}

function balise_LANGUE_PREFEREE_LIEN_EFFACE_COOKIE_stat($args, $filtres)
{
	return $args;
}

function balise_LANGUE_PREFEREE_LIEN_EFFACE_COOKIE_dyn($texte = '')
{
	if ($texte == '') {
		$texte = _T('languepreferee:efface_cookie');
	}

	include_spip('inc/meta');
	$lien = '';
	if (isset($_COOKIE['spip_langue_preferee']) && $_COOKIE['spip_langue_preferee'] != '') {
		if (isset($_GET['var_langue_preferee_efface_cookie'])) {
			include_spip('inc/cookie');
			spip_setcookie('spip_langue_preferee', '', time() - 3600*24*10, chemin_cookie());
		} else {
			$url = preg_replace("/([?&])var_langue_preferee_efface_cookie=[^&]+(&)?/", "$1", self());
			$url .= (strpos($url, '?') > 0 ? '&amp;' : '?').'var_langue_preferee_efface_cookie=oui';
			$lien = '<a href="'.$url.'">'.$texte.'</a>';
		}
	}
	return $lien;
}
?>
