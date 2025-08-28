<?php
if (isset($_GET['cep'])) {
    $cep = preg_replace('/\D/', '', $_GET['cep']); // remove caracteres não numéricos
    if (strlen($cep) === 8) {
        $url = "https://viacep.com.br/ws/$cep/json/";
        $data = file_get_contents($url);
        echo $data;
    } else {
        echo json_encode(['erro' => true, 'msg' => 'CEP inválido']);
    }
} else {
    echo json_encode(['erro' => true, 'msg' => 'CEP não informado']);
}