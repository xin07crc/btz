<?php

if (!defined('_ECRIRE_INC_VERSION'))
  return;

include_spip('inc/config');

if (!lire_config('multilingue/desactiver_traduction_logo_objet'))
  $traduire_logo = 'ok';
if ($traduire_logo == 'ok') {
  // surcharge de http://doc.spip.org/@inc_chercher_logo_dist pour ajouter
  function inc_chercher_logo($id, $_id_objet, $mode = 'on') {
    include_spip('inc/chercher_logo');
    global $formats_logos;
    # attention au cas $id = '0' pour LOGO_SITE_SPIP : utiliser intval()

    $type = type_du_logo($_id_objet);

    $objet = str_replace('id_', '', $_id_objet);
    $tables = lister_tables_objets_sql();
    $table = 'spip_' . $objet . 's';

    foreach ($formats_logos as $format) {
      $nom = $type . $mode . intval($id);
      if (@file_exists($d = (_DIR_LOGOS . $nom . '.' . $format))) {
        return array(
          $d,
          _DIR_LOGOS,
          $nom,
          $format,
          @filemtime($d)
        );
      }
      //si pas de logo on cherche si l'article d'origine en a un
      elseif ($tables[$table]['field']['id_trad'] AND $id_trad = sql_getfetsel('id_trad', $table, $_id_objet . '=' . intval($id)) AND _request('exec') != $objet) {
        $nom = $type . $mode . intval($id_trad);
        if (@file_exists($d = (_DIR_LOGOS . $nom . '.' . $format))) {
          return array(
            $d,
            _DIR_LOGOS,
            $nom,
            $format,
            @filemtime($d)
          );
        };
      }
    }
    # coherence de type pour servir comme filtre (formulaire_login)
    return array();
  }

}

