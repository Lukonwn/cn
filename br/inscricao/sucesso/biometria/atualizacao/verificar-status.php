<?php
// Recebe o token do POST
$input = json_decode(file_get_contents('php://input'), true);
$token = $input['orderId'] ?? null;


if (!$token) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Token ausente.']);
    exit;
}

// Caminho do arquivo JSON onde os pagamentos estão salvos
$caminhoArquivo = __DIR__ . '/pagamentos.json'; // ajuste o nome se for diferente

// Verifica se o arquivo existe
if (!file_exists($caminhoArquivo)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Arquivo de pagamentos não encontrado.']);
    exit;
}

// Lê e decodifica o JSON
$conteudo = file_get_contents($caminhoArquivo);
$pagamentos = json_decode($conteudo, true);

// Procura o pagamento com o token solicitado
$encontrado = null;

foreach ($pagamentos as $pagamento) {
    if ($pagamento['token'] == $token) {
        $encontrado = $pagamento;
        break;
    }
}

if ($encontrado) {
    echo json_encode([
        'status' => $encontrado['status'],
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Pagamento não encontrado.'
    ]);
}
