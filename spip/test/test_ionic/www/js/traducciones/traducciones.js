angular.module('gettext').run(['gettextCatalog', function (gettextCatalog) {
/* jshint -W100 */
    gettextCatalog.setStrings('en', {"Datos sobre tu IP:":"Information about your IP:","Su ciudad es: {{data.ciudad}}":"Your city is: {{data.ciudad}}","Su huella digital es:":"Your fingerprint is:","Su ip es: {{data.ip}}":"Your ip is: {{data.ip}}","Su pais es: {{data.pais}}":"Your country is: {{data.pais}}","Su region es: {{data.region}}":"Your region is: {{data.region}}","Ver en el mapa":"View on map"});
/* jshint +W100 */
}]);