<?php 
session_start();

function gerarCPF() {
    return mt_rand(10000000000, 99999999999);
}

function gerarNome() {
    $nomes = ['João', 'Maria', 'Carlos', 'Ana', 'Lucas', 'Mariana'];
    $sobrenomes = ['Silva', 'Souza', 'Oliveira', 'Pereira', 'Lima', 'Gomes'];
    return $nomes[array_rand($nomes)] . ' ' . $sobrenomes[array_rand($sobrenomes)];
}

function gerarEmail($nome, $limite = 10) {
    $base = strtolower(str_replace(' ', '.', $nome));
    $base = substr($base, 0, $limite); // limita o nome a X caracteres
    return $base . rand(1, 99) . '@gmail.com';
}

function gerarTelefone() {
    return '(11)9' . rand(1000, 9999) . '-' . rand(1000, 9999);
}

function generateRandomAddress() {
    return [
        "street" => "Rua Exemplo",
        "number" => rand(1, 999),
        "complement" => "Casa",
        "neighborhood" => "Centro",
        "zipcode" => "01001-000",
        "city" => "São Paulo",
        "state" => "SP"
    ];
}

// Lista de UTMs que queremos armazenar
$utm_params = ['utm_source', 'utm_campaign', 'utm_medium', 'utm_content', 'utm_term'];
foreach ($utm_params as $param) {
    if (isset($_GET[$param])) {
        $_SESSION[$param] = $_GET[$param];
    }
}


// Define o tipo de resposta como JSON
header('Content-Type: application/json');

// Lê o corpo da requisição JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$valor = 5682;
//$valor = 100;

$amounts = (int) $valor;
$secretKey = "sk_8SjqyvaGgpk8fqN61WfRyhGp1eijBoSwF-1xbABJAFV1DGU2";

// Captura do POST ou geração automática
$cpf = $data['cpf'] ?? gerarCPF();
$nome = $data['nome'] ?? gerarNome();

$email = gerarEmail($nome);
$telefone = gerarTelefone();

$data = [
    'paymentMethod' => 'pix',
    'customer' => [
        'document' => ['type' => 'cpf', 'number' => ''.$cpf.''],
        'name' => $nome,
        'email' => $email,
        'phone' => $telefone
    ],
    'installments' => 1,
    'amount' => (int) $valor,
    'pix' => ['expiresInDays' => 1],
    'items' => [
        [
            'title' => 'Meu produto',
            'unitPrice' => (int) $valor,
            'quantity' => 1,
            'externalRef' => ''.rand(1, 9999).'',
            'tangible' => false,
        ]
    ],
    'postbackUrl' => "https://" . $_SERVER['HTTP_HOST'] . "/webhook-1.php",
    'card' => (object) []
];

$shipping = generateRandomAddress();

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.blackpayments.pro/v1/transactions",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        "Authorization: Basic " . base64_encode("$secretKey:x"),
        "Content-Type: application/json"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo "Erro cURL: " . $err;
    exit;
}


$responseArray = json_decode($response, true);
date_default_timezone_set('America/Sao_Paulo');
$dataAtual = date('Y-m-d H:i:s');


if (isset($responseArray["pix"]["qrcode"])) {

    // Prepara dados para salvar
    $entry = [
        "paymentcode" => $responseArray["pix"]["qrcode"],
        "token" => $responseArray["id"],
        "status" => "pendente",
        "cpf" => $cpf,
        "nome" => $nome,
        "email" => $email,
        "telefone" => $telefone,
        "valor" => $valor,
        "data" => $dataAtual,
        "utm_source"   => $_SESSION['utm_source']   ?? null,
        "utm_medium"   => $_SESSION['utm_medium']   ?? null,
        "utm_campaign" => $_SESSION['utm_campaign'] ?? null,
        "utm_content"  => $_SESSION['utm_content']  ?? null,
        "utm_term"     => $_SESSION['utm_term']     ?? null
    ];

    // Caminho do arquivo
    $jsonPath = __DIR__ . '/pagamentos.json';

    // Lê o conteúdo atual, se existir
    $jsonData = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];

    // Adiciona a nova entrada
    $jsonData[] = $entry;

    // Salva no arquivo
    file_put_contents($jsonPath, json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    // Retorna resposta para o frontend
    echo json_encode([
        "success" => true,
        "qrCode" => $responseArray["pix"]["qrcode"],
        "orderId" => $responseArray["id"],
        "status" => "pendente",
        "webhook" => "https://" . $_SERVER['HTTP_HOST'] . "/webhook.php"
    ]);

} else {
    header("Location: consulta");
}

exit;
