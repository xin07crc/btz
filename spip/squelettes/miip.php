<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width">
    <title></title>

    <link rel="manifest" href="../test/test_ionic/www/manifest.json">

    <!-- un-comment this code to enable service worker
    <script>
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('service-worker.js')
          .then(() => console.log('service worker installed'))
          .catch(err => console.log('Error', err));
      }
    </script>-->

    <link href="../test/test_ionic/www/lib/ionic/css/ionic.css" rel="stylesheet">
    <link href="../test/test_ionic/www/css/style.css" rel="stylesheet">
    <link href="../test/test_ionic/www/css/animate.css" rel="stylesheet">

    <!-- IF using Sass (run gulp sass first), then uncomment below and remove the CSS includes above
    <link href="css/ionic.app.css" rel="stylesheet">
    -->

    <!-- ionic/angularjs js -->
    <script src="../test/test_ionic/www/lib/ionic/js/ionic.bundle.js"></script>

    <!-- cordova script (this will be a 404 during development) -->


    <!-- your app's js -->
    <script src="../test/test_ionic/www/js/app.js"></script>
</head>
<body ng-app="starter">
<INCLURE{fond=inclure/menu_langues,env}/>
<ion-pane>
    <ion-header-bar class="bar-stable">
        <BOUCLE_seccionPadre_header(RUBRIQUES){lang=#ENV{lang}}{par titre}{id_parent=0}>
        <BOUCLE_seccionPreguntas_header(RUBRIQUES){lang=#ENV{lang}}{par titre}{id_parent=#ID_RUBRIQUE}>
        <h1 class="title">#TITRE</h1>
        <br>
        #MENU_LANG
        </BOUCLE_seccionPreguntas_header>
        </BOUCLE_seccionPadre_header>
    </ion-header-bar>

    <ion-content>
        <!-- Bucle que saca la seccion padre en el idioma actual -->
        <BOUCLE_seccionPadre(RUBRIQUES){lang=#ENV{lang}}{par titre}{id_parent=0}>
        <!-- Bucle que saca el hijo de la primera seccion en el idioma actual -->
        <BOUCLE_seccionPreguntas(RUBRIQUES){lang=#ENV{lang}}{par titre}{id_parent=#ID_RUBRIQUE}>
        <!-- Saca las preguntas en el idioma actual -->
        <div class="list">
            <BOUCLE_listadoPreguntas(RUBRIQUES){lang=#ENV{lang}}{par titre}{id_parent=#ID_RUBRIQUE}>
            <div class="item item-button-right">
                #TITRE
                <div class="buttons">
                    <a href="spip.php?page=lista&id_rubrique=#ID_RUBRIQUE&titre_mot=Información" class="button button-positive">
                        <i ng-click="test==true" class="icon ion-ios-telephone"></i>
                    </a>
                    <a href="spip.php?page=lista&id_rubrique=#ID_RUBRIQUE&titre_mot=Consejos" class="button button-positive">
                        <i ng-click="test==true" class="icon ion-ios-telephone"></i>
                    </a>
                    <a href="spip.php?page=tal" class="button button-positive">
                        <i ng-click="test==true" class="icon ion-ios-telephone"></i>
                    </a>
                </div>
            </div>
            </BOUCLE_listadoPreguntas>
        </div>
        </BOUCLE_seccionPreguntas>
        </BOUCLE_seccionPadre>
    </ion-content>
</ion-pane>
</body>
</html>
