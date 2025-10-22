<?php

// Caminho do arquivo de pagamentos
$arquivoPagamentos = __DIR__ . '/pagamentos.json';

// Lê o payload do webhook
$rawPayload = file_get_contents('php://input');
if (!$rawPayload) {
    http_response_code(400);
    echo "Payload vazio.";
    exit;
}

// Salva o payload recebido para debug (opcional)
file_put_contents(__DIR__ . '/payload-log.json', $rawPayload);

$payload = json_decode($rawPayload, true);
if (!$payload || !isset($payload['data'])) {
    http_response_code(400);
    echo "Formato de payload inválido.";
    exit;
}

$transactionId = $payload['data']['id'] ?? null;
$status = $payload['data']['status'] ?? null;

if (!$transactionId || $status !== 'paid') {
    echo "Ignorado. Status diferente de 'paid' ou token ausente.";
    exit;
}

// Lê os pagamentos
if (!file_exists($arquivoPagamentos)) {
    echo "Arquivo de pagamentos não encontrado.";
    exit;
}

$pagamentos = json_decode(file_get_contents($arquivoPagamentos), true);
if (!is_array($pagamentos)) {
    echo "Erro ao decodificar pagamentos.";
    exit;
}

$atualizado = false;

// Atualiza o status no array
foreach ($pagamentos as &$pagamento) {
    if ($pagamento['token'] == $transactionId) {
        $pagamento['status'] = 'paid';
        $atualizado = true;
        break;
    }
}


unset($pagamento); // boa prática

if ($atualizado) {
    file_put_contents($arquivoPagamentos, json_encode($pagamentos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "✅ Pagamento $transactionId atualizado para 'paid'.";

    // Atualiza o status no array
    foreach ($pagamentos as &$pagamento) {
        if ($pagamento['token'] == $transactionId) {
            ///add_utm($pagamento['nome'], $pagamento['cpf'], $pagamento['email'], $pagamento['telefone'], $pagamento['valor'], $pagamento['utm_source'], $pagamento['utm_campaign'], $pagamento['utm_medium'], $pagamento['utm_content'], $pagamento['utm_term'], $pagamento['token']);
        }
    }

} else {
    echo "❌ Pagamento com token $transactionId não encontrado.";
}



function add_utm($nome, $cpf, $email, $telefone, $valor, $utm_source, $utm_campaign, $utm_medium, $utm_content, $utm_term, $token){

    $apiUrl = 'https://api.utmify.com.br/api-credentials/orders';
    $apiToken = 'JAHfdL1Un5DJciiKvCtmymuQ7qxLdVCDnoIW';

    $amount = $valor; // em centavos
    $gatewayFee = round($amount * 0.0349) + 169;
    $userCommission = $amount - $gatewayFee;
    $createdAt = date('Y-m-d H:i:s');

    $telefone_limpo = preg_replace('/\D/', '', $telefone);
    $cpf_limpo = preg_replace('/\D/', '', $cpf);


    $payload = [
        "orderId" => $token, // exemplo de token
        "platform" => "Pix",
        "paymentMethod" => "pix",
        "status" => "paid",
        "createdAt" => $createdAt,
        "approvedDate" => date("Y-m-d H:i:s"),
        "refundedAt" => null,
        "customer" => [
            "name" => "".$nome."",
            "email" => "".$email."",
            "phone" => "".$telefone_limpo."",
            "document" => "".$cpf_limpo."",
            "country" => "BR"
        ],
        "products" => [
            [
                "id" => "produto-externo",
                "name" => "Produto Externo",
                "planId" => null,
                "planName" => null,
                "quantity" => 1,
                "priceInCents" => $amount
            ]
        ],
        "trackingParameters" => [
            "src" => "produto-externo",
            "sck" => "1233",
            "utm_source" => "".$utm_source."",
            "utm_campaign" => "".$utm_campaign."",
            "utm_medium" => "".$utm_medium."",
            "utm_content" => "".$utm_content."",
            "utm_term" => "".$utm_term.""
        ],
        "commission" => [
            "totalPriceInCents" => $amount,
            "gatewayFeeInCents" => $gatewayFee,
            "userCommissionInCents" => $userCommission
        ],
        "isTest" => false
    ];
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-api-token: ' . $apiToken
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Erro no cURL: ' . curl_error($ch);
    } else {
        echo '✅ Resposta da UTMify:<br><pre>' . htmlentities($response) . '</pre>';
    }

    curl_close($ch);
}
