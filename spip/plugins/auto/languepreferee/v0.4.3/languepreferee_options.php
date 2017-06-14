<?php
function chemin_cookie()
{
	static $chemin;
    if (!isset($chemin)) {
        include_spip('inc/meta');
    	$chemin = preg_replace("/^https?:\/\/([^\/]+)(\/.*)$/", "$2", lire_meta('adresse_site').'/');
    }
    return $chemin;
}
?>