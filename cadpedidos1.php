<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("conexao.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Filtros
$filtroCliente = $_GET['cliente'] ?? '';
$filtroNumero = $_GET['numero'] ?? '';
$filtroFaturado = $_GET['faturado'] ?? '';
$filtroMesAnoFaturamento = $_GET['mes_ano_faturamento'] ?? '';
$filtroMesAnoPagto = $_GET['mes_ano_pagamento'] ?? '';
$filtroNF = $_GET['nnf'] ?? '';

// Cadastro
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cliente_id = $_POST['cliente_id'];
    $cnpj = $_POST['cnpj'];
    $numero = $_POST['numero'];
    $data = $_POST['data'];
    $tipo = $_POST['tipo'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $faturado = $_POST['faturado'];

    $stmt = $conn->prepare("INSERT INTO scnp_ped (cliente_id, cnpj, numero, data, tipo, descricao, valor, faturado) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssds", $cliente_id, $cnpj, $numero, $data, $tipo, $descricao, $valor, $faturado);
    $stmt->execute();
}

$clientes = $conn->query("SELECT id, nome, cnpj FROM helpdesk_clientes ORDER BY nome ASC");

// Consulta com filtros
$sql = "SELECT p.*, c.nome FROM scnp_ped p JOIN helpdesk_clientes c ON p.cliente_id = c.id WHERE 1=1";
if ($filtroCliente) $sql .= " AND c.nome LIKE '%$filtroCliente%'";
if ($filtroNumero) $sql .= " AND p.numero LIKE '%$filtroNumero%'";
if ($filtroFaturado) $sql .= " AND p.faturado = '$filtroFaturado'";
if ($filtroNF) $sql .= " AND p.nnf LIKE '%$filtroNF%'";
if ($filtroMesAnoFaturamento) $sql .= " AND DATE_FORMAT(p.data_faturamento, '%Y-%m') = '$filtroMesAnoFaturamento'";
if ($filtroMesAnoPagto) $sql .= " AND DATE_FORMAT(p.prevpagto, '%Y-%m') = '$filtroMesAnoPagto'";
$sql .= " ORDER BY p.id DESC";

$pedidos = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Cadastro de Pedidos</h4>
        <a href="dashboard.php" class="btn btn-secondary">Voltar</a>
    </div>

    <!-- Formulário de cadastro (sem alterações) -->
    <!-- ... mantém o formulário de cadastro de pedido aqui ... -->

    <!-- Filtros -->
    <form class="row g-2 mb-4" method="GET">
        <div class="col-md-3">
            <input type="text" name="cliente" class="form-control" placeholder="Cliente" value="<?= htmlspecialchars($filtroCliente) ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="numero" class="form-control" placeholder="Número Pedido" value="<?= htmlspecialchars($filtroNumero) ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="nnf" class="form-control" placeholder="Número NF" value="<?= htmlspecialchars($filtroNF) ?>">
        </div>
        <div class="col-md-2">
            <input type="month" name="mes_ano_faturamento" class="form-control" value="<?= htmlspecialchars($filtroMesAnoFaturamento) ?>">
        </div>
        <div class="col-md-2">
            <input type="month" name="mes_ano_pagamento" class="form-control" value="<?= htmlspecialchars($filtroMesAnoPagto) ?>">
        </div>
        <div class="col-md-1">
            <button class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <!-- Lista de Pedidos -->
    <h5>Pedidos cadastrados</h5>
    <table class="table table-bordered table-sm table-striped">
        <thead class="table-light">
            <tr>
                <th>Cliente</th>
                <th>CNPJ</th>
                <th>Número</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Data Faturamento</th>
                <th>Previsão de Pagto</th>
                <th>Nº NF</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($p = $pedidos->fetch_assoc()): ?>
            <tr>
                <td><?= $p['nome']; ?></td>
                <td><?= $p['cnpj']; ?></td>
                <td><?= $p['numero']; ?></td>
                <td><?= $p['tipo']; ?></td>
                <td>R$ <?= number_format($p['valor'], 2, ',', '.'); ?></td>
                <td><?= $p['faturado']; ?></td>
                <td><?= $p['data_faturamento'] ? date('d/m/Y', strtotime($p['data_faturamento'])) : '-'; ?></td>
                <td><?= $p['prevpagto'] ? date('d/m/Y', strtotime($p['prevpagto'])) : '-'; ?></td>
                <td><?= $p['nnf'] ?: '-'; ?></td>
                <td><a href="visualizar_pedido.php?id=<?= $p['id']; ?>" class="btn btn-sm btn-info">Visualizar</a></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
