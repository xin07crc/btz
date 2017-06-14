<?php
$pagina_reenvio = "http://betacitizens.com/?page=btz_home";
if (isset($_COOKIE['token'])) {
    unset($_COOKIE['token']);
    setcookie('token', null, -1, '/');
} else {

}
?>
<html>
<head>

</head>
<body topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<script LANGUAGE="JavaScript">
    var pagina="<? echo $pagina_reenvio ?>"
    function redireccionar()
    {
        location.href=pagina
    }
    setTimeout ("redireccionar()", 1);
</script>

</body>
</html>