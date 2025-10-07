<?php
session_start();
include("conexao.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID da nota não fornecido.";
    exit;
}

$id = intval($_GET['id']);

// Buscar nota
$stmt = $conn->prepare("SELECT n.*, c.nome FROM scnp_nfs n JOIN helpdesk_clientes c ON n.cliente_id = c.id WHERE n.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$nf = $res->fetch_assoc();

if (!$nf) {
    echo "Nota não encontrada.";
    exit;
}

// Inserção de item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $desc = $_POST['descricao'];
    $qtd = $_POST['qtd'];
    $valor_unit = $_POST['valor_unit'];
    $cfop = $_POST['cfop'];
    $ncm = $_POST['ncm'];

    $stmtAdd = $conn->prepare("INSERT INTO scnp_nfs_itens (nf_id, descricao, qtd, valor_unit, cfop, ncm) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtAdd->bind_param("isidss", $id, $desc, $qtd, $valor_unit, $cfop, $ncm);
    $stmtAdd->execute();
    header("Location: visualizar_nota.php?id=$id");
    exit;
}

// Itens da nota
$itens = [];
$totalItens = 0;
$valorTotal = 0.0;

$stmtItens = $conn->prepare("SELECT * FROM scnp_nfs_itens WHERE nf_id = ?");
$stmtItens->bind_param("i", $id);
$stmtItens->execute();
$resItens = $stmtItens->get_result();
while ($item = $resItens->fetch_assoc()) {
    $itens[] = $item;
    $totalItens += $item['qtd'];
    $valorTotal += $item['qtd'] * $item['valor_unit'];
}

// Devolver NF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['devolver_nf'])) {
    $nfdev = $_POST['nfdev'];
    $data = date('Y-m-d');
    $stmtDev = $conn->prepare("UPDATE scnp_nfs SET dev = 'SIM', datadev = ?, nfdev = ? WHERE id = ?");
    $stmtDev->bind_param("ssi", $data, $nfdev, $id);
    $stmtDev->execute();
    header("Location: visualizar_nota.php?id=$id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Visualizar Nota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    function confirmarDevolucao() {
        const nfdev = prompt("Digite o número da NF de devolução:");
        if (nfdev !== null && nfdev.trim() !== "") {
            document.getElementById('nfdev').value = nfdev;
            document.getElementById('formDevolver').submit();
        }
    }
    </script>
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between mb-3 align-items-center">
        <h4>Nota Fiscal Nº <?= htmlspecialchars($nf['nnf']) ?></h4>
        <a href="cadnotas.php" class="btn btn-secondary">Voltar</a>
    </div>

    <div class="card shadow-sm p-4 mb-4">
        <div class="row mb-2">
            <div class="col-md-2"><strong>Cliente:</strong> <?= htmlspecialchars($nf['nome']) ?></div>
            <div class="col-md-2"><strong>CNPJ:</strong> <?= htmlspecialchars($nf['cnpj']) ?></div>
            <div class="col-md-5"><strong>Data:</strong> <?= date("d/m/Y", strtotime($nf['data_rec'])) ?></div>
            <div class="col-md-3"><strong>Devolvido:</strong> <?= $nf['dev'] ?></div>
        </div>
        <div class="row mb-2">
            <div class="col-md-2"><strong>Qtd Itens:</strong> <?= $totalItens ?></div>
            <div class="col-md-2"><strong>Valor Total:</strong> R$ <?= number_format($valorTotal, 2, ',', '.') ?></div>
            <div class="col-md-5"><strong>Chave:</strong> <?= htmlspecialchars($nf['chave']) ?></div>
            <div class="col-md-3"><strong>NF Devolução:</strong> <?= htmlspecialchars($nf['nfdev']) ?></div>
        </div>
    </div>

    <h5 class="mt-4">Itens da Nota</h5>
    <table class="table table-bordered table-sm table-striped">
        <thead>
            <tr>
                <th style="text-align: center;">Descrição</th>
                <th style="text-align: center;">NCM</th>
                <th style="text-align: center;">CFOP</th>
                <th style="text-align: center;">Qtd</th>
                <th style="text-align: center;">Valor Unitário</th>                          
            </tr>
        </thead>
        <tbody>
        <?php foreach ($itens as $item): ?>
            <tr style="text-align: center;">
                <td><?= htmlspecialchars($item['descricao']) ?></td>
                <td><?= $item['ncm'] ?></td>
                <td><?= $item['cfop'] ?></td>
                <td><?= $item['qtd'] ?></td>
                <td>R$ <?= number_format($item['valor_unit'], 2, ',', '.') ?></td>                
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($nf['dev'] !== 'SIM'): ?>
    <div class="card card-body bg-white shadow-sm mb-4 mt-4">
        <h6>Adicionar Item</h6>
        <form method="POST" class="row g-2 align-items-end">
            <input type="hidden" name="add_item" value="1">
            <div class="col-md-4">
                <label>Descrição</label>
                <input type="text" name="descricao" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label>NCM</label>
                <input type="text" name="ncm" class="form-control">
            </div>
            <div class="col-md-2">
                <label>CFOP</label>
                <input type="text" name="cfop" class="form-control">
            </div>
            <div class="col-md-2">
                <label>Qtd</label>
                <input type="number" name="qtd" class="form-control" required>
            </div>
              <div class="col-md-2">
                <label>Valor Unitário</label>
                <input type="number" step="0.01" name="valor_unit" class="form-control" required>
            </div>
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-success">Adicionar</button>
            </div>
        </form>

        <form method="POST" id="formDevolver" class="mt-3">
            <input type="hidden" name="nfdev" id="nfdev">
            <button type="button" onclick="confirmarDevolucao()" class="btn btn-danger">Devolver NF</button>
            <input type="hidden" name="devolver_nf" value="1">
        </form>
    </div>
<?php endif; ?>

</div>
</body>
</html>