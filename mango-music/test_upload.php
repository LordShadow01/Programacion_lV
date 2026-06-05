<?php

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/upload/image');
curl_setopt($ch, CURLOPT_POST, 1);
$cfile = new CURLFile('c:\\xampp\\htdocs\\Antigravity Program lV\\Programacion_lV\\mango-music\\public\\favicon.ico', 'image/x-icon', 'favicon.ico');
$data = array('file' => $cfile);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
// We don't have auth token here. So it will return 401. But we can check if it returns 401 or something else.
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
echo "RESPONSE: " . $response;
curl_close($ch);
