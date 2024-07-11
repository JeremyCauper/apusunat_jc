<?php
$id = isset($_GET['id']) ? $_GET['id'] : die('Debe ingresar el id de una empresa.');

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://74.208.186.238/apusunat/examples/resumen.php?id=$id",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
));

$response = curl_exec($curl);

curl_close($curl);

echo $response;