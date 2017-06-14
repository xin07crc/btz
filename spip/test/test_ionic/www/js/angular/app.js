// Ionic Starter App

// angular.module is a global place for creating, registering and retrieving Angular modules
// 'starter' is the name of this angular module example (also set in a <body> attribute in index.html)
// the 2nd parameter is an array of 'requires'
angular.module('starter', ['ionic', 'gettext', 'ngCookies', 'LocalStorageModule', 'angular-toArrayFilter'])
.run(function ($rootScope, $http, gettextCatalog, huella, $cookies, localStorageService, get_datos, get_private_ip, globalFunctions) {
  if (getParameterByName("tracklang")) {
    gettextCatalog.setCurrentLanguage(getParameterByName("tracklang"));
  }
  // Cargo de forma predeterminada el español
  gettextCatalog.setCurrentLanguage('es');
  if ($cookies.get('lang')) {
    gettextCatalog.setCurrentLanguage($cookies.get('lang'));
  }
  if (getParameterByName("lang")) {
    gettextCatalog.setCurrentLanguage(getParameterByName("lang"));
    $cookies.put("lang", getParameterByName("lang"));
  }


  console.log(gettextCatalog.getCurrentLanguage());
  //gettextCatalog.setCurrentLanguage('es');
  // gettextCatalog.debug = true;
  //console.log($cookies.getAll());
  /* Llamo a las funciones globales */
  $rootScope.appData = globalFunctions;
  /**
   * Consige los parametros dados por GET
   * Lo hago asi porque routeparams no funciona bien, gracias angular
   * @param name
   * @returns {string}
     */
  function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
  }
  // Calculo la fecha de caducidad de la cookie, 1 año
  var expireDate = new Date();
  expireDate.setDate(expireDate.getDate() + 365);
  // Compruebo si tiene acpetadas el mensaje de las cookies
  if (!$cookies.get('avisoCookie')) {
    $rootScope.avisoCookies = true;
  }


  //console.log(window.location.pathname);
  obtenerPaginaActual(window.location.pathname);

  // Gestiona las cookies cuando se carga la página
  // Almaceno el nombre de la ultima pagina visualizada
  $cookies.put('ultima_pagina', obtenerPaginaActual(window.location.pathname), {'expires': expireDate});
  // Compruebo que exista el numero de visitas
  if (!$cookies.get("num_visitas")) {
    // Creo las visitas
    $cookies.put('num_visitas', 1, {'expires': expireDate});
  }
  else {
    // Aumento de numero de visitas
    var num_visitas = $cookies.get("num_visitas");
    num_visitas = parseInt(num_visitas) + 1;
    $cookies.put('num_visitas', num_visitas, {'expires': expireDate});
  }
  // Alamaceno la fecha acual
  var today = new Date().toISOString().slice(0, 10);
  $cookies.put('ultima_visita', today, {'expires': expireDate});
  // Calcula la IP
  get_datos.ip($http)
      .success(function(data) {
        var ip = data["ip"];
        var datos = data["componentes"];
        if ($cookies.get('ip') && $cookies.get('ip') == ip) {
          if(localStorageService.isSupported) {
            localStorageService.set("datos_ip", datos);
          }
        }
        else {
          // Almaceno la IP
          $cookies.put('ip', ip, {'expires': expireDate});
          if(localStorageService.isSupported) {
            localStorageService.set("datos_ip", datos);
          }
        }
      })
  // Calculo la huella digital
  get_datos.huella()
      .success(function(data) {
        var huella_repetida;
        if ($cookies.get('huella') && $cookies.get('huella') == data["huella"]) {
          localStorageService.set("datos_huella", data["componentes"]);
          huella_repetida = true;
        }
        else {
          huella_repetida = false;
          // Almaceno la huella
          $cookies.put('huella', data["huella"], {'expires': expireDate});
          if(localStorageService.isSupported) {
            localStorageService.set("datos_huella", data["componentes"]);
          }
        }
        // Subo al servidor los datos
        get_datos.almacenarCookiesServidor($cookies.get("ip"), $cookies.get("huella"), huella_repetida);
      });
})

.service('get_private_ip', function ($q, $http) {
  return {
    ipprivada: function () {
      var deferred = $q.defer();
      var promise = deferred.promise;
      getIPs(function(ip){
        var datos = {};
        if (ip.match(/^(192\.168\.|169\.254\.|10\.|172\.(1[6-9]|2\d|3[01]))/)) {
          datos["ip_local"] = ip;
          deferred.resolve(datos);
        }
      });
      promise.success = function(fn) {
        promise.then(fn);
        return promise;
      }
      promise.error = function(fn) {
        promise.then(null, fn);
        return promise;
      }
      return promise;
    }
  }
})

.service('globalFunctions', function ($rootScope, $cookies) {
  return {
    aceptaCookies : function () {
      // Calculo la fecha de caducidad de la cookie, 1 año
      var expireDate = new Date();
      expireDate.setDate(expireDate.getDate() + 365);
      $cookies.put('avisoCookie', true, {'expires': expireDate});
      $rootScope.avisoCookies = false;
    },
    cambia_idioma : function (idioma) {
      $cookies.put('idioma', idioma);
      window.location = "http://localhost/typ/spip/spip.php?page=home&lang=" + idioma;
    }
  }
})
// Aglutina todos los servicios que hace la web
.service('get_datos', function ($q, $http, get_private_ip) {
    return {

      huella: function () {
        var deferred = $q.defer();
        var promise = deferred.promise;
        new Fingerprint2().get(function(result, components){
          var datos = {};
          datos["huella"] = result;
          datos["componentes"] = components;
          deferred.resolve(datos);
        });
        promise.success = function(fn) {
          promise.then(fn);
          return promise;
        }
        promise.error = function(fn) {
          promise.then(null, fn);
          return promise;
        }
        return promise;
      },
      ip : function ($http) {
        // alert("Calculo mi ip");
        var deferred = $q.defer();
        var promise = deferred.promise;
        var link = "test/test_ionic/www/php/miip.php";
        $http.post(link, {
        }).then(function (res) {
          console.log(res.data);
          var datosIp = {};
          var datosComponentes = new Array();
          var whois = {};
          whois["key"] = "whois";
          whois["value"] = res.data["curl"];
          datosComponentes.push(whois);
          var mapa = {};
          var lat = res.data["meta"]["geoplugin_latitude"];
          var long = res.data["meta"]["geoplugin_longitude"];
          var enlace = "http://maps.googleapis.com/maps/api/staticmap?center=" + lat + "," + long + "&zoom=13&size=400x400&sensor=false&markers=color:blue|" + lat + "," + long + "&key=AIzaSyAv33wP9gjUCFJd_nNLem_Vyd_wdDL7Zs4";
          mapa["key"] = "mapa";
          mapa["value"] = enlace;
          datosComponentes.push(mapa);
          var whois = {};
          whois["key"] = "whois";
          whois["value"] = res.data["whois"];
          datosComponentes.push(whois);
          var ip = {};
          ip["key"] = "ip_usada";
          ip["value"] = res.data["ip"];
          datosComponentes.push(ip);
          datosIp["ip"] = res.data["ip"];
          datosIp["componentes"] = datosComponentes;
          datosIp["whois"] = res.data["whois"];
          deferred.resolve(datosIp);
        })
        promise.success = function(fn) {
          promise.then(fn);
          return promise;
        }
        promise.error = function(fn) {
          promise.then(null, fn);
          return promise;
        }
        return promise;
      },
      ordenarObjetos: function (components) {
        //console.log(components);
        var datosHuella = {};
        var datosHuellaMas = {};
        var posicionMas = 0;
        var posicion = 0;
        var htmlHuella = {};
        htmlHuella["data"] = {};
        htmlHuella["html"] = "<div class='contenedor-mas-huella'>";
        console.log(components);
        angular.forEach(components, function(value, key) {
          // Si no es canvas ni webgl almacena la informacon en el objecto
          if (components[key]["key"] != "canvas" && components[key]["key"] != "webgl" && components[key]["key"] != "regular_plugins" && components[key]["key"] != "js_fonts") {
            datosHuella[posicion] = {};
            datosHuella[posicion] = components[key];
            datosHuella[posicion]["visible"] = true;
            posicion++;
          }
          else {
            // Para canvas
            var componentes = components[key];
            if (components[key]["key"] == "canvas") {
              var size = components[key]["value"].length;
              var numero = components[key]["value"].indexOf("fp:");
              // Se suma 3 a umero para quitar fp:
              var canvasString = components[key]["value"].substr(numero+3, size);
              htmlHuella["data"]["canvas"] = canvasString;
              // components[key]["value"] = b64_md5(canvasString);
              componentes["value"] = b64_md5(canvasString);
            }
            if (componentes["key"] == "webgl") {
              //console.log(components[key]["value"]);
              var valor_webgl = componentes["value"];
              var numero = valor_webgl.indexOf("~extensions:");
              // Se suma 3 a umero para quitar fp:
              var webglString = valor_webgl.substr(0, numero);
              htmlHuella["data"]["webgl"] = webglString;
              componentes["value"] = b64_md5(webglString);
             // console.log(components[key]["value"]);
            }
            datosHuellaMas[posicionMas] = {};
            //console.log(componentes);
            //console.log(components[key]);
            datosHuellaMas[posicionMas] = componentes;
            datosHuellaMas[posicionMas]["visible"] = false;
            datosHuellaMas[posicionMas]["boton"] = true;
            posicionMas++;
          }
        });
        // Recorre el array de datoshuellas, pinta el DOM de los datos de las huellas
        angular.forEach(datosHuella, function(value, key) {
          if (datosHuella[key]["visible"] == false) {
            var onclick = 'toggledisplay("texto-huella-' + key + '", "' + key + '")';
            htmlHuella["html"] += "<p class='texto-normal'><span onclick='" + onclick +  "'><img id='boton-mas-" + key + "' style='cursor: pointer;width: 20px;margin-right: 1%;' src='../test/test_ionic/www/img/seccion/mas.svg'><img id='boton-menos-" + key + "' style='cursor: pointer;width: 20px;margin-right: 1%;display: none' src='../test/test_ionic/www/img/seccion/menos.png'></span>" + "<span class='texto-normal-encabezado'>" + value['key'] + "</span> =</p>";
            htmlHuella["html"] += "<p style='display: none;' id='texto-huella-" + key + "' class='texto-normal'>" + value['value'] + "</p>"
          }
          else {
            htmlHuella["html"] += "<p class='texto-normal'>" + "<span class='texto-normal-encabezado'>" + value['key'] + "</span> = <span>" + value['value'] + "</span></p>";
          }
        });
        // Recorre el array de datoshuellasmas, pinta el DOM de los datos de las huellas
        angular.forEach(datosHuellaMas, function(value, key) {
          if (datosHuellaMas[key]["visible"] == false) {
            var onclick = 'toggledisplay("texto-huella-' + key + '", "' + key + '")';
            if (value["key"] == "canvas" || value["key"] == "webgl") {
              htmlHuella["html"] += "<p class='texto-normal'><span onclick='" + onclick +  "'><img id='boton-mas-" + key + "' style='cursor: pointer;width: 20px;margin-right: 1%;' src='../test/test_ionic/www/img/seccion/mas.svg'><img id='boton-menos-" + key + "' style='cursor: pointer;width: 20px;margin-right: 1%;display: none' src='../test/test_ionic/www/img/seccion/menos.png'></span>" + "<span class='texto-normal-encabezado'>" + value['key'] + "</span> = " + value['value'] + "</p>";
              htmlHuella["html"] += "<p style='display: none;' id='texto-huella-" + key + "' class='texto-normal'><canvas class='canvas-huella' id='canvas-" + value["key"] + "'></canvas></p>"
            }
            else {
              htmlHuella["html"] += "<p class='texto-normal'><span onclick='" + onclick +  "'><img id='boton-mas-" + key + "' style='cursor: pointer;width: 20px;margin-right: 1%;' src='../test/test_ionic/www/img/seccion/mas.svg'><img id='boton-menos-" + key + "' style='cursor: pointer;width: 20px;margin-right: 1%;display: none' src='../test/test_ionic/www/img/seccion/menos.png'></span>" + "<span class='texto-normal-encabezado'>" + value['key'] + "</span> = </p>";
              htmlHuella["html"] += "<p style='display: none;' id='texto-huella-" + key + "' class='texto-normal'>" + value['value'] + "</p>"
            }
          }
          else {
            htmlHuella["html"] += "<p class='texto-normal'>" + "<span class='texto-normal-encabezado'>" + value['key'] + "</span> = <span>" + value['value'] + "</span></p>";
          }
        });
        htmlHuella["html"] += '</div>';
        //console.log(components);
        return htmlHuella;
      },
      almacenarCookiesServidor: function (ip, huella, repetida) {
        var link = "test/test_ionic/www/php/almacenoCookies.php";

        $http.post(link, {
          ip : ip,
          huella : huella,
          repetida : repetida
        }).then(function (res) {
        })
      }
    }
})
// Controlador de la huella digital
.factory("huella", function($q) {
  var getFP = function () {
    return $q(function (resolve) {
      new Fingerprint2().get(function (result, components) {
        resolve(result);
      });
    });
  }

  return {
    getFP: getFP,
  }
})
    // Controlador para pagina de IP
.controller('ip', function($scope, $http, gettextCatalog, get_datos, $cookies, localStorageService, get_private_ip) {
  $scope.anadir_ext_hola = function (num) {
    var titulo_real = "titulo-acciones-" + num;
    var texto_real = "texto-acciones-" + num;
    var titulo = document.getElementById(titulo_real).innerHTML;
    var texto = document.getElementById(texto_real).innerHTML;

    swal({
      title: titulo,
      text: texto,
      animation: false,
    })

  }
  /**
   * Obtiene la ip y la muestra en un popUp
   */
  $scope.mostrar_informacion = function (titre, subtitulo) {
    var titulo = titre;
    if (subtitulo != "") {
      titulo = subtitulo;
    }
    console.log($scope.datos_ip);
    var comprobacion = $scope.datos_ip[2].value.objects.object["0"].attributes.attribute[9]["value"];
    var esCorrecta = comprobacion.lastIndexOf('You can access databases of other RIRs at:');
    console.log(comprobacion);
    console.log(esCorrecta);
    // Compruebo si la IP es europea
    if (esCorrecta == -1) {
      var datosIPArray = ordenarWhois($scope.datos_ip[2]["value"].objects.object["1"].attributes.attribute);
    }
    else {
      var datosIPArray = ordenarWhois($scope.datos_ip[2].value.objects.object["0"].attributes.attribute);
    }

    swal({
      title: titulo + " " + $scope.datos_mas,
      text: datosIPArray["html"],
      imageUrl: $scope.datos_ip[1]["value"],
      imageWidth: 400,
      imageHeight: 400,
      animation: false,
      id: "peque"
    })
  }
  get_private_ip.ipprivada()
      .success(function (data) {
        $scope.datos_mas_dos = data["ip_local"];
      })
  // Compruebo si tiene la IP almacenada en la cookie y si tiene el localStorage soportado
  // Obligo a que recalcule la ip (Por nuevo script)
  // if (($cookies.get('ip') && localStorageService.isSupported) || 1 != 1) {
  if (1 != 1) {
    // Recupero en el scope la IP y los datos de la misma en localStorage
    $scope.datos_mas = $cookies.get('ip');
    $scope.datos_ip = localStorageService.get("datos_ip");
  }
  else {
    console.log("No tiene ip");
    // Calcula la IP
    get_datos.ip($http)
        .success(function(data) {
          console.log(data);
          // Recupero la ip y los componentes y almaceno estos en cookie y localstorage
          $scope.datos_mas = data["ip"];
          $scope.datos_ip = data["componentes"];
          $scope.whois = data["whois"];
          $cookies.put('ip', data["ip"]);
          localStorageService.set('datos_ip', data["componentes"]);
        })
  }
})
    // COntrolador para la huella digital
.controller('huella', function ($scope, gettextCatalog, get_datos, $cookies, localStorageService) {
  /**
   * Obtiene la huella digital y la muestra en un popup
   */
  $scope.mostrar_informacion = function (titre, subtitulo) {
    var titulo = titre;
    if (subtitulo != "") {
      titulo = subtitulo;
    }
    var dat
    get_datos.huella()
        .success(function(data) {
          var ObjetoHuella = $scope.datos_huella;
            var datosHuella = "<h3 style='text-align: center; margin-bottom: 5%;'>" + $cookies.get("huella") + "</h3>";
            var huellaOrdenada = ordenarObjetos(localStorageService.get("datos_huella"));
            //var huellaOrdenada = get_datos.ordenarObjetos(ObjetoHuella);
            datosHuella += huellaOrdenada["html"]
            swal.queue([{
              title: titulo,
              text: '<b>' + datosHuella + '</b>',
              animation: false,
              id: "peque"
            }])
          // Meto los datos en el canvas
          var canvas = document.getElementById('canvas-canvas');
          var context = canvas.getContext('2d');
          // load image from data url
          var imageObj = new Image();
          imageObj.onload = function() {
            context.drawImage(this, 0, 0);
          };
          imageObj.src = huellaOrdenada["data"]["canvas"];
          // Canvas de webgl
          //webgl
          var canvas2 = document.getElementById('canvas-webgl');
          var context2 = canvas2.getContext('2d');
          // load image from data url
          var imageObj2 = new Image();
          imageObj2.onload = function() {
            context2.drawImage(this, 0, 0);
          };
          imageObj2.src = huellaOrdenada["data"]["webgl"];

        });

  }
  // Compruebo si tiene la huella almacenada en la cookie y si tiene el localStorage soportado
  if ($cookies.get('huella') && localStorageService.isSupported) {
    // Recupero en el scope la huella y los datos de la misma en localStorage
    $scope.datos_mas = $cookies.get('huella');
    $scope.datos_huella = localStorageService.get("datos_huella");
    //console.log($scope.datos_huella);
  }
  else {
    // Calcula la huella
    get_datos.huella()
        .success(function(data) {
          // Recupero la ip y los componentes y almaceno estos en cookie y localstorage
          $scope.datos_mas = data["huella"];
          $scope.datos_huella = data["componentes"];
          // console.log($scope.datos_huella);
          $cookies.put('huella', data["huella"]);
          localStorageService.set('datos_huella', data["componentes"]);
        })
  }
})
    // Controlador de la cookie
.controller('Cookie', function($scope, gettextCatalog, $cookies, get_datos, $window) {
  // Inactivo la variable para mostar las cookies
  $scope.cookiesMuestra = false;
  $scope.mostrar_informacion = function (textos, titulo) {
    var cookies = $scope.filtar_cookies($cookies.getAll());
    var mensajes = textos.split("/");
    var cookiesArray = get_datos.ordenarObjetos(cookies);
    $scope.cookiesFormateadas = cookiesArray["html"];
    swal({
      title: titulo,
      text: $scope.cookiesFormateadas,
      showCancelButton: true,
      animation: false,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: mensajes[0],
      cancelButtonText: mensajes[1],
      id: "peque"
    }).then(function() {
      var cookies = $cookies.getAll();
      angular.forEach(cookies, function (v, k) {
        $cookies.remove(k);
      });
      $scope.cookiesFormateadas = "";
    })
  };
  // Filtra las cookies
  $scope.filtar_cookies = function (cookies) {
    var devolver = new Array();
    angular.forEach(cookies, function(value, key) {
      if (key != "spip_admin"){
        //devolver[key] = value;
        var nuevo = {};
        nuevo["key"] = key;
        nuevo["value"] = value;
        devolver.push(nuevo);
      }
    });
    return devolver;
  }
  /**
   * Muestra un mensaje de confirmacion para borrar las cookies.
   * Se puede aceptar o rechazar
   * Si aceptas borra las cookies
   * @param titulo
   * @param texto
   * DECRAPET -> YA NO SE USA, LA DEJO POR SI HACE FALTA
     */
  $scope.borrar_nuestras_cookies = function (num) {
    var cookies = $scope.filtar_cookies($cookies.getAll());
    // Ordena las cookies
    var cookiesArray = get_datos.ordenarObjetos(cookies);
    $scope.cookiesFormateadas = cookiesArray["html"];
    var titulo = document.getElementById("titulo-acciones-" + num).innerHTML;
    var borrar_text = document.getElementById("texto-acciones-" + num).innerHTML;
    var cancel_text = document.getElementById("sub-acciones-" + num).innerHTML;
    swal({
      title: titulo,
      text: $scope.cookiesFormateadas,
      showCancelButton: true,
      animation: false,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: cancel_text,
      cancelButtonText: borrar_text,
      id: "peque"
    }).then(function() {
        var cookies = $cookies.getAll();
        angular.forEach(cookies, function (v, k) {
        $cookies.remove(k);
      });
      $scope.cookiesFormateadas = "";
    })
  }
  $scope.quienCookies = function (form) {
    //http://webanonymizer.org/a/elpais.com/
    var enlace = "http://webanonymizer.org/a/" + $scope.url;
    $window.location.href = enlace;
  }


  // Comprueba si tiene las cookies activas
  if (navigator.cookieEnabled == false) {
    // Si no tiene muestra el mensaje de error
    swal(
        'No tienes habilitadas las cookies',
        'error'
    )
  }
})
    // Controlador de la home
.controller('home', function($scope, gettextCatalog, get_datos){
})
    // Importancia de la privacidad
.controller('Importancia_privacidad' , function ($scope, gettextCatalog, get_datos) {

  $scope.data = {};
  $scope.show = {};
  $scope.ip = 0;
  $scope.cookie = 0;
  $scope.show.huella = 0;
  $scope.show.datosValor = 0;
  $scope.show.importancia = 1;
  // Consige la url y la pone
  $scope.data.url = window.location.href;
})
    // Que datos recogen las webs que visitas
.controller('Datos_recogen', function ($scope, gettextCatalog, get_datos, $http, $cookies) {
  $scope.url = "http://";
  $scope.quien = true;

  /**
   * Recoge los datos de los graficos
   * @param datos
   */
  $scope.datosGrafico = function (datos) {
    var repuesta = {};
    var longitud = 0;
    var total = 0;
    angular.forEach(datos, function(value, key) {
      if (datos[key].length > 2) {
        repuesta[longitud] = {};
        repuesta[longitud]["titulo"] = key;
        repuesta[longitud]["longitud"] = datos[key].length;
        total = total + datos[key].length;
        longitud++;
      }
    });
    repuesta["total"] = total;
    return repuesta;
  }
  /**
   * Crea un html con el objeto recibido
   * @param datos
     */
  $scope.ordenar_resultadoQuien = function (datos, id_rubrique) {
    var respuesta = new Array();
    var html = "";
    angular.forEach(datos, function(value, key) {
      if (key == "inspected_domain") {
        respuesta["titulo"] = datos[key];
      }
      else {
        if (datos[key].length > 2) {
          var nombreCookie = key;
          for (object in traduccionesCookies) {
            if (key.toUpperCase() == object.toUpperCase()) {
              nombreCookie = traduccionesCookies[object];
            }
          }
          var onclick = 'toggledisplay("contenedor-' + key.toLowerCase() + '", "' + key.toLowerCase() + '")';
          html += "<p  class='texto-grande'>" +
              "<span onclick='" + onclick + "' >" +
                "<img id='boton-mas-" + key.toLowerCase() + "' style='cursor: pointer;width: 20px;margin-right: 1%;' src='../test/test_ionic/www/img/seccion/mas.svg'>" +
                "<img id='boton-menos-" + key.toLowerCase() + "' style='cursor: pointer;width: 20px;margin-right: 1%;display: none' src='../test/test_ionic/www/img/seccion/menos.png'>" +
              "</span>" +
              "<span class='texto-normal-encabezado'>" +
              datos[key].length + " " + nombreCookie.toUpperCase() +
              "</span></p>";
          html += "<div style='display: none' id='contenedor-" + key.toLowerCase() + "'>";
          html += '<p style="display: none;" id="texto-huella-' + key.toLowerCase() + '" class="texto-normal">';
          var ordenado = datos[key].sort();
            angular.forEach(ordenado, function(value2, key2) {
              var enlace_formateado = formatear_enlace(value2);
              var enlace_busqueda = enlace_formateado.split(".");
              // Define el idioma de la pagina, lo detecto por el rubrique
              if (id_rubrique == 32) {
                var idioma_actual = "en";
              }
              else {
                var idioma_actual = "es";
              }
              html += "<p><a target='_blank' style='margin-right: 10px' href='trackers/?buscar=" + enlace_busqueda[0] + "&tracklang=" + idioma_actual + "'><i  class='fa fa-address-card-o' aria-hidden='true'></i></a><a target='_blank' href='http://" + enlace_formateado + "'>" + enlace_formateado + "</a></p>"; // Antes dentro del enlace ponia value2
            });
            // html += "</p>";
            html += "</div>";
          }

      }
    });
    respuesta["html"] = html;
    return respuesta;
  }
  /**
   * Submit
   * @param form
     */
  $scope.quienRastrea = function (form, id_rubrique) {
    var string = form.url.$viewValue, substring = "http://";
    if (string.indexOf(substring) == -1) {
      form.url.$viewValue = "http://" + form.url.$viewValue;
    }
    $scope.enviado = 1;
    //var link = "http://track.netcom.it.uc3m.es/THIRDP/thirdP.php";
    var url = "test/test_ionic/www/php/recogeDatos.php";
    $http.post(url, {
      quien : form.url.$viewValue,
      huella : $cookies.get("huella")
    }).then(function (res) {
      var datosGraficos = $scope.datosGrafico(res.data);
      //console.log(datosGraficos);
      var respuestaOrdenada = $scope.ordenar_resultadoQuien(res.data, id_rubrique);
      console.log(respuestaOrdenada);
      var htmlTitulo = "<div>" + respuestaOrdenada["titulo"][0] + "</div><div id='chart_div'></div>"
      swal({
        title: htmlTitulo,
        text: respuestaOrdenada["html"],
        animation: false,
        id: "peque"
      })
      /* CARGO DATOS EN LOS CHARTS */
      google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string','category');
        data.addColumn('number', 'Slices');
        //console.log(datosGraficos);
        angular.forEach(datosGraficos, function(value, key) {
          if (key != "total") {
            var nombreCookie = datosGraficos[key]["titulo"];
            for (object in traduccionesCookies) {
              if (datosGraficos[key]["titulo"].toUpperCase() == object.toUpperCase()) {
                nombreCookie = traduccionesCookies[object];
              }
            }
            var nombre = nombreCookie.toUpperCase();
            data.addRow([nombre, datosGraficos[key]["longitud"]]);
          }
        })
        var options = {
          title: datosGraficos["total"] + ' AGENTES EXTERNOS',
          titleFontSize: 15,
          titlePosition: 'center'
        };
        function selectHandler() {
          var selectedItem = chart.getSelection()[0];
          if (selectedItem) {
            var nombreCookie = data.getValue(selectedItem.row, 0);
            // console.log(nombreCookie);
            for (object in traduccionesCookies) {
              if (nombreCookie.toUpperCase() == traduccionesCookies[object].toUpperCase()) {
                // console.log(object);
                var nombre = object.toLowerCase();
                toggledisplay("contenedor-" + nombre + "", nombre);
              }
            }
          }
        }
        var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
        google.visualization.events.addListener(chart, 'select', selectHandler);
        chart.draw(data, options);
      }
      /* FIN DE CHARTS */
      $scope.enviado = 0;
    });
  }
})
    // Cuanto valen tus datos personales
.controller('Datos_valor', function ($scope, gettextCatalog, get_datos) {

  $scope.calculaValorDatosCliente = function (form) {
  }
  $scope.data = {};
  $scope.show = {};
  $scope.ip = 0;
  $scope.cookie = 0;
  $scope.show.huella = 0;
  $scope.show.datosRecogen = 0;
  $scope.show.datosValor = 1;
  $scope.show.instalar = 1;
  // Consige la url y la pone
  $scope.data.url = window.location.href;
})
  //Como mejorar tu privacidad
.controller('Mejorar', function ($scope, gettextCatalog, get_datos) {

  $scope.data = {};
  // Consige la url y la pone
  $scope.data.url = window.location.href;
})

.controller('protege', function($scope, gettextCatalog, get_datos){

})
// Como obtener beneficio
.controller('beneficio', function ($scope) {

})
// Listado de todo
.controller('listado', function ($scope) {
})
// Paginas internas
.controller('internas', function ($scope) {
  $scope.ver_mas = function (num) {
    var titulo = document.getElementById("titulo-embajador-" + num).innerHTML;
    var texto = document.getElementById("texto-embajador-" + num).innerHTML;
    var url_image = document.getElementById("imagen-embajador-" + num).src;
    swal({
      title: titulo,
      text: texto,
      imageUrl: url_image,
      imageWidth: 400,
      imageHeight: 200,
      animation: true
    })
  }

})
// Formularios
.controller('formulario', function ($scope) {
  // Pilla los datos desde SPIP y los añade al scope para ponerlos en el hmtl
  angular.forEach(textos, function(value, key) {
    $scope[key] = value;
  });
})
// Formularios
.controller('validar', function ($scope, $http) {
  $scope.enviar_validacion = function (estado, id_articulo, correo) {
    var link = "../test/test_ionic/www/php/validar_embajador.php";
    $http.post(link, {
      passowod : $scope.password,
      correo : correo,
      validado : estado,
      articulo : id_articulo
    }).then(function (res) {
      if (res.data["error"] == true) {
        switch (res.data["cod"]) {
          case 0:
              alert("Error de password");
                break;
          case 1:
              alert("No existe el articulo");
                break;
        }
      }
      if (res.data["error"] == false) {
        switch (res.data["validado"]) {
          case true:
              alert("Actualizado");
                break;
          case false:
            alert("Rechazado");
              break;
        }
      }
    });
  }
})
    
.controller('listacookies', function ($scope) {
  var enlaces = document.getElementsByClassName("enlace");
  var textos_enlaces = [];
  for (var i = 0; i < enlaces.length; i++) {
    var texto = enlaces[i].innerText;
    var enlace = enlaces[i].href;
    //textos_enlaces.push(texto);

    textos_enlaces[i] = {};
    textos_enlaces[i]["enlace"] = enlace;
    textos_enlaces[i]["nombre"] = texto;

  }
  $scope.lista_enlaces = textos_enlaces;
  // Compruebo si ha entrado un parametro por GET
  if (getParameterByName("buscar")) {
    $scope.buscador = getParameterByName("buscar");
    $scope.buscando = 1;
  }
});
/**
 * Hace visible o invisible textos
 * @param id
 */
function toggledisplay(id, key) {
  var yourUl = document.getElementById(id);
  yourUl.style.display = yourUl.style.display === 'none' ? '' : 'none';
  if (yourUl.style.display == 'none') {
    //console.log(document.getElementById("boton-mas-" + key));
    document.getElementById("boton-mas-" + key).style.display = "inherit";
    document.getElementById("boton-menos-" + key).style.display = "none";
  }
  else {
    document.getElementById("boton-mas-" + key).style.display = "none";
    document.getElementById("boton-menos-" + key).style.display = "inherit";
  }
}
/**
 * Abre el popup para las acciones.
 * Como siempre va a ser el mismo popup creo una funcion global
 * @param num
 */
function abrir_accion(num) {
  var titulo = document.getElementById("titulo-acciones-" + num).innerHTML;
  var texto = document.getElementById("texto-acciones-" + num).innerHTML;;
  swal({
    title: titulo,
    text: texto,
    type: 'info',
    animation: false,
  })
}
/**
 * Abre la ventana de preguntas
 * titulo_spip
 * num
 * id_rubrique
 */
function abrir_pregunta(titulo_spip, num, id_rubrique) {
  var pregunta = "<div class='contenedor-mas-huella texto-normal'>";
  var imagen = "<div style='margin-bottom: 3%;'><img style='width: 46px' src='IMG/rubon" + id_rubrique + ".svg' alt='logo'></div>";
  var texto = document.getElementById("pregunta-" + num).innerHTML;
  pregunta += texto;
  pregunta += "</div>";
  var titulo = imagen;
  titulo += titulo_spip;
  swal({
    title: titulo,
    text: pregunta,
    animation: false,
  })
}
/**
 * Acpeta el aviso de cookies
 */
function aceptar_cookies() {

}
/**
 * Formatea el enlace las cookies para que funcione
 * Parto el enlace por puntos, cogo los datos que se encientrar entre el ultimo punto
 */
function formatear_enlace(enlace) {
  var url = "";
  var array_enlace = enlace.split(".");
  var size_array = array_enlace.length;
  if (size_array >= 2) {
    return array_enlace[size_array-2] + "." + array_enlace[size_array-1];
  }
  else {
    return enlace;
  }
}

/**
 * Obtendo la direccion y saco la pagina actual
 * @param path
 * @returns {*}
 */
function obtenerPaginaActual(path) {
  var partes = path.split("/");
  var tamano = partes.length;
  if (partes[tamano-1] == "es" || partes[tamano-1] == "en") {
    // console.log(partes[tamano-2]);
    return partes[tamano-2];
  }
  else {
    if (partes[tamano-1] == "") {
      // console.log("index");
      return "index";
    }
    else {
      // console.log(partes[tamano-1]);
      return partes[tamano-1];
    }
  }
}

/* TEST */
function ordenarObjetos(components) {
  //console.log(components);
  var datosHuella = {};
  var datosHuellaMas = {};
  var posicionMas = 0;
  var posicion = 0;
  var htmlHuella = {};
  htmlHuella["data"] = {};
  htmlHuella["html"] = "<div class='contenedor-mas-huella'>";
  angular.forEach(components, function(value, key) {
    // Si no es canvas ni webgl almacena la informacon en el objecto
    if (components[key]["key"] != "canvas" && components[key]["key"] != "webgl" && components[key]["key"] != "js_fonts" && components[key]["key"] != "regular_plugins") {
      datosHuella[posicion] = {};
      datosHuella[posicion] = components[key];
      datosHuella[posicion]["visible"] = true;
      posicion++;
    }
    else {
      // Para canvas
      var componentes = components[key];
      if (components[key]["key"] == "js_fonts") {
        componentes["value"] = components[key]["value"];
      }
      if (components[key]["key"] == "regular_plugins") {
        var string = "";
        angular.forEach(components[key]["value"], function(value2, key2) {
          string += components[key]["value"][key2];
        });
        componentes["value2"] = b64_md5(string);
      }
      if (components[key]["key"] == "canvas") {
        var size = components[key]["value"].length;
        var numero = components[key]["value"].indexOf("fp:");
        // Se suma 3 a umero para quitar fp:
        var canvasString = components[key]["value"].substr(numero+3, size);
        htmlHuella["data"]["canvas"] = canvasString;
        // components[key]["value"] = b64_md5(canvasString);
        componentes["value"] = b64_md5(canvasString);
      }
      if (componentes["key"] == "webgl") {
        //console.log(components[key]["value"]);
        var valor_webgl = componentes["value"];
        var numero = valor_webgl.indexOf("~extensions:");
        // Se suma 3 a umero para quitar fp:
        var webglString = valor_webgl.substr(0, numero);
        htmlHuella["data"]["webgl"] = webglString;
        componentes["value"] = b64_md5(webglString);
        // console.log(components[key]["value"]);
      }
      datosHuellaMas[posicionMas] = {};
      //console.log(components[key]);
      datosHuellaMas[posicionMas] = componentes;
      datosHuellaMas[posicionMas]["visible"] = false;
      datosHuellaMas[posicionMas]["boton"] = true;
      posicionMas++;
    }
  });
  // Recorre el array de datoshuellas, pinta el DOM de los datos de las huellas
  angular.forEach(datosHuella, function(value, key) {
    if (datosHuella[key]["visible"] == false) {
      var onclick = 'toggledisplay("texto-huella-' + key + '", "' + key + '")';
      htmlHuella["html"] += "<p class='texto-normal'><span onclick='" + onclick +  "'><img id='boton-mas-" + key + "' style='cursor: pointer;width: 20px;margin-right: 1%;' src='../test/test_ionic/www/img/seccion/mas.svg'><img id='boton-menos-" + key + "' style='cursor: pointer;width: 20px;margin-right: 1%;display: none' src='../test/test_ionic/www/img/seccion/menos.png'></span>" + "<span class='texto-normal-encabezado'>" + value['key'] + "</span> =</p>";
      htmlHuella["html"] += "<p style='display: none;' id='texto-huella-" + key + "' class='texto-normal'>" + value['value'] + "</p>"
    }
    else {
      htmlHuella["html"] += "<p class='texto-normal'>" + "<span class='texto-normal-encabezado'>" + value['key'] + "</span> = <span>" + value['value'] + "</span></p>";
    }
  });



  var keys = Object.keys(datosHuellaMas);
  var len = 0;
  var final = 0;
  var copiaDatosHuellaOrdenada = [];
  while(final < 4) {
    if (datosHuellaMas[len]["key"] == "js_fonts" && final == 0) {
      // copiaDatosHuellaOrdenada = datosHuellaMas[3];
      copiaDatosHuellaOrdenada.push(datosHuellaMas[3]);
      final++;
    }
    if (datosHuellaMas[len]["key"] == "regular_plugins" && final == 1) {
      // copiaDatosHuellaOrdenada = datosHuellaMas[0];
      copiaDatosHuellaOrdenada.push(datosHuellaMas[0]);
      final++;
    }
    if (datosHuellaMas[len]["key"] == "canvas" && final == 2) {
      // copiaDatosHuellaOrdenada = datosHuellaMas[1];
      copiaDatosHuellaOrdenada.push(datosHuellaMas[1]);
      final++;
    }
    if (datosHuellaMas[len]["key"] == "webgl" && final == 3) {
      // copiaDatosHuellaOrdenada = datosHuellaMas[2];
      copiaDatosHuellaOrdenada.push(datosHuellaMas[2]);
      final++;
    }
    len++;
    if (len == 4) {
      len = 0;
    }
  }


  // Recorre el array de datoshuellasmas, pinta el DOM de los datos de las huellas
  angular.forEach(copiaDatosHuellaOrdenada, function(value, key) {
    if (copiaDatosHuellaOrdenada[key]["visible"] == false) {
      var onclick = 'toggledisplay("texto-huella-' + key + '", "' + key + '")';
      if (value["key"] == "canvas" || value["key"] == "webgl") {
        htmlHuella["html"] += "<p class='texto-normal'><span onclick='" + onclick +  "'><img id='boton-mas-" + key + "' style='cursor: pointer;width: 20px;margin-right: 1%;' src='../test/test_ionic/www/img/seccion/mas.svg'><img id='boton-menos-" + key + "' style='cursor: pointer;width: 20px;margin-right: 1%;display: none' src='../test/test_ionic/www/img/seccion/menos.png'></span>" + "<span class='texto-normal-encabezado'>" + value['key'] + "</span> = " + value['value'] + "</p>";
        htmlHuella["html"] += "<p style='display: none;' id='texto-huella-" + key + "' class='texto-normal'><canvas class='canvas-huella' id='canvas-" + value["key"] + "'></canvas></p>"
      }
      else {
        if (value["key"] == "regular_plugins") {
          htmlHuella["html"] += "<p class='texto-normal'><span onclick='" + onclick +  "'><img id='boton-mas-" + key + "' style='cursor: pointer;width: 20px;margin-right: 1%;' src='../test/test_ionic/www/img/seccion/mas.svg'><img id='boton-menos-" + key + "' style='cursor: pointer;width: 20px;margin-right: 1%;display: none' src='../test/test_ionic/www/img/seccion/menos.png'></span>" + "<span class='texto-normal-encabezado'>" + value['key'] + "</span> = " + value['value2'] + "</p>";
          htmlHuella["html"] += "<p style='display: none;' id='texto-huella-" + key + "' class='texto-normal'>" + value['value'] + "</p>"
        }
        else {
          htmlHuella["html"] += "<p class='texto-normal'><span onclick='" + onclick +  "'><img id='boton-mas-" + key + "' style='cursor: pointer;width: 20px;margin-right: 1%;' src='../test/test_ionic/www/img/seccion/mas.svg'><img id='boton-menos-" + key + "' style='cursor: pointer;width: 20px;margin-right: 1%;display: none' src='../test/test_ionic/www/img/seccion/menos.png'></span>" + "<span class='texto-normal-encabezado'>" + value['key'] + "</span> = </p>";
          htmlHuella["html"] += "<p style='display: none;' id='texto-huella-" + key + "' class='texto-normal'>" + value['value'] + "</p>"
        }
      }
    }
    else {
      htmlHuella["html"] += "<p class='texto-normal'>" + "<span class='texto-normal-encabezado'>" + value['key'] + "</span> = <span>" + value['value'] + "</span></p>";
    }
  });
  htmlHuella["html"] += '</div>';
  //console.log(components);
  return htmlHuella;
}

function ordenarWhois(components) {
  //console.log(components);
  var datosHuella = {};
  var datosHuellaMas = {};
  var posicionMas = 0;
  var posicion = 0;
  var htmlHuella = {};
  htmlHuella["data"] = {};
  htmlHuella["html"] = "<div class='contenedor-mas-huella'>";
  // console.log(components);
  angular.forEach(components, function(value, key) {
    // Si no es canvas ni webgl almacena la informacon en el objecto
      datosHuella[posicion] = {};
      datosHuella[posicion] = components[key];
      datosHuella[posicion]["visible"] = true;
      posicion++;
  });
  // console.log(datosHuella);
  // Recorre el array de datoshuellas, pinta el DOM de los datos de las huellas
  angular.forEach(datosHuella, function(value, key) {
    if (datosHuella[key]["name"] == "remarks") {
      htmlHuella["html"] += "<p class='texto-normal'>" + "<span class='ip-no-eu'>" + value['value'] + "</span></p>";
    }
    else {
      htmlHuella["html"] += "<p class='texto-normal'>" + "<span class='texto-normal-encabezado'>" + value['name'] + "</span> = <span>" + value['value'] + "</span></p>";
    }

  });
  // Recorre el array de datoshuellasmas, pinta el DOM de los datos de las huellas
  htmlHuella["html"] += '</div>';
  return htmlHuella;
}


/**
 * Consige los parametros dados por GET
 * Lo hago asi porque routeparams no funciona bien, gracias angular
 * @param name
 * @returns {string}
 */
function getParameterByName(name) {
  name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
  var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
      results = regex.exec(location.search);
  return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}
