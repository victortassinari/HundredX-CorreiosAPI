<?php
header('Content-type: application/json');
include("correios.class.php");
$api = new hundredXCorreiosAPI();
$api->setCodigoRastreio((isset($_GET["codigo"]) ? $_GET["codigo"] : ""));
echo $api->getDadosJson();