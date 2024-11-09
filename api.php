<?php
ini_set("display_errors", 1);
require 'vendor/autoload.php';

use MongoDB\Client;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

try {
    //conexao
    $client = new Client("mongodb+srv://<usuario>:<senha>@cluster0.v1ovk.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");

    $database = $client->log;
    $collection = $database->logs_uso;

    //post para gera a log
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        gravaLog($collection, $data);
    }

    //funcao pra retornar as logs gravadas no mongo
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        retornaLogs($collection);
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao conectar ao MongoDB: ' . $e->getMessage()
    ]);
}


//////////////////// FUNCOES ///////////////////////////////////////
function retornaLogs($collection){//voltar registros grvados do banco mongodb
    $logs = $collection->find();
    $logsArray = iterator_to_array($logs);

    echo json_encode([
        'status' => 'success',
        'logs' => $logsArray
    ]);
}

function gravaLog($collection, $data){//GRAVAR LOG NO BANCO MONGODB
    $acao = $data['acao'];
    $detalhes = $data['detalhes'];
    $sessionId = $data['sessionId'];
    $pageUrl = $data['pageUrl']; 
    $duracao = $data['duracao'];
    $timestamp = date('Y-m-d H:i:s');
    $navegador = $data['navegador'];

    $logData = [
        'acao' => $acao,
        'timestamp' => $timestamp,
        'detalhes' => $detalhes,
        'sessionId' => $sessionId,
        'pageUrl' => $pageUrl,      
        'duracao' => $duracao,
        'navegador' => $navegador
    ];

    $result = $collection->insertOne($logData);

    echo json_encode([
        'status' => 'success',
        'message' => 'Log gerado com sucesso',
        'logId' => $result->getInsertedId()
    ]);
}
?>
