<?php
// leitura.php
ini_set('display_errors', 1);
$host = '179.188.16.33';        // Host do MySQL (use '127.0.0.1' se 'localhost' der erro)
$user = 'entiresys';          // Usuário do banco
$pass = 'Etsys#2014';                // Senha do banco
$dbname = 'entiresys'; // Nome do banco


// === RECEBIMENTO DO JSON ===
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Verificação básica
if (!isset($data['medidor_id']) || !isset($data['leitura_kwh'])) {
    http_response_code(400);
    echo "Dados incompletos";
    exit;
}

$medidor_id = $data['medidor_id'];
$leitura_kwh = floatval($data['leitura_kwh']);

// === CONEXÃO COM MYSQL ===
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo "Erro de conexão: " . $conn->connect_error;
    exit;
}

// === INSERÇÃO NO BANCO ===
$stmt = $conn->prepare("INSERT INTO leituras_energia (medidor_id, leitura_kwh) VALUES (?, ?)");
$stmt->bind_param("sd", $medidor_id, $leitura_kwh);

if ($stmt->execute()) {
    echo "OK";
} else {
    http_response_code(500);
    echo "Erro na inserção: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
