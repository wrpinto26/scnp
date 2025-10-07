<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include("conexao.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Inserção da nota fiscal
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cliente_id  = $_POST['cliente_id'];
    $cnpj        = $_POST['cnpj'];
    $nnf         = $_POST['nnf'];
    $data_rec    = $_POST['data_rec'];
    $chave       = $_POST['chave'];
    $dev         = $_POST['dev'] ?? 'NAO';

    $stmt = $conn->prepare("INSERT INTO scnp_nfs (cliente_id, cnpj, nnf, data_rec, chave, dev) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isssss", $cliente_id, $cnpj, $nnf, $data_rec, $chave, $dev);
        $stmt->execute();
        $id_nota = $stmt->insert_id;
        header("Location: visualizar_nota.php?id=$id_nota");
        exit;
    } else {
        echo "Erro na query: " . $conn->error;
    }
}

// Filtros
$filtro_cliente = $_GET['cliente'] ?? '';
$filtro_cnpj    = $_GET['cnpj'] ?? '';
$filtro_numero  = $_GET['nnf'] ?? '';
$filtro_data    = $_GET['data_rec'] ?? '';
$filtro_dev     = $_GET['dev'] ?? '';

$query = "SELECT n.*, c.nome FROM scnp_nfs n
          JOIN helpdesk_clientes c ON n.cliente_id = c.id
          WHERE 1=1";

if ($filtro_cliente) $query .= " AND c.id = '" . $conn->real_escape_string($filtro_cliente) . "'";
if ($filtro_cnpj)    $query .= " AND n.cnpj LIKE '%" . $conn->real_escape_string($filtro_cnpj) . "%'";
if ($filtro_numero)  $query .= " AND n.nnf LIKE '%" . $conn->real_escape_string($filtro_numero) . "%'";
if ($filtro_data)    $query .= " AND n.data_rec = '" . $conn->real_escape_string($filtro_data) . "'";
if ($filtro_dev)     $query .= " AND n.dev = '" . $conn->real_escape_string($filtro_dev) . "'";

$query .= " ORDER BY n.id DESC";
$result = $conn->query($query);

$clientes = $conn->query("SELECT id, nome, cnpj FROM helpdesk_clientes ORDER BY nome ASC");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Notas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Cadastro de Notas Fiscais</h4>
        <a href="dashboard.php" class="btn btn-secondary">Voltar</a>
    </div>

    <!-- Formulário de Cadastro -->
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-4">
            <label>Cliente</label>
            <select name="cliente_id" class="form-select" required onchange="updateCNPJ(this)">
                <option value="">Selecione</option>
                <?php while ($c = $clientes->fetch_assoc()): ?>
                    <option value="<?= $c['id']; ?>" data-cnpj="<?= $c['cnpj']; ?>"><?= $c['nome']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label>CNPJ</label>
            <input type="text" name="cnpj" id="cnpj" class="form-control" readonly required>
        </div>
        <div class="col-md-4">
            <label>Nº Nota</label>
            <input type="text" name="nnf" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label>Data Recebimento</label>
            <input type="date" name="data_rec" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label>Chave de Acesso</label>
            <input type="text" name="chave" class="form-control">
        </div>
        
        <div style="align-content=center;align-self: end;text-align:right;" class="col-md-2" >
            <button type="submit"  class="btn btn-success">Cadastrar Nota</button>
        </div>
    </form>

    <!-- Filtros -->
    <form method="GET" class="row g-3 mb-3">
        <div class="col-md-3">
            <label>Cliente</label>
            <select name="cliente" class="form-select">
                <option value="">Todos</option>
                <?php $clientes->data_seek(0); while ($c = $clientes->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filtro_cliente == $c['id']) ? 'selected' : '' ?>><?= $c['nome'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label>CNPJ</label>
            <input type="text" name="cnpj" value="<?= htmlspecialchars($filtro_cnpj) ?>" class="form-control">
        </div>
        <div class="col-md-2">
            <label>Número NF</label>
            <input type="text" name="nnf" value="<?= htmlspecialchars($filtro_numero) ?>" class="form-control">
        </div>
        <div class="col-md-2">
            <label>Data</label>
            <input type="date" name="data_rec" value="<?= htmlspecialchars($filtro_data) ?>" class="form-control">
        </div>
        <div class="col-md-2">
            <label>Devolvido</label>
            <select name="dev" class="form-select">
                <option value="">Todos</option>
                <option value="SIM" <?= ($filtro_dev == 'SIM') ? 'selected' : '' ?>>SIM</option>
                <option value="NAO" <?= ($filtro_dev == 'NAO') ? 'selected' : '' ?>>NÃO</option>
            </select>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <!-- Listagem -->
    <table class="table table-bordered table-sm table-striped">
        <thead class="table-light">
            <tr style="text-align: center;">
                <th>Cliente</th>
                <th>CNPJ</th>
                <th>Nº NF</th>
                <th>NF Devolução</th>
                <th>Devolvido</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($n = $result->fetch_assoc()): ?>
            <tr style="text-align: center;">
                <td><?= $n['nome'] ?></td>
                <td><?= $n['cnpj'] ?></td>
                <td><?= $n['nnf'] ?></td>
                <td><?= $n['nfdev'] ?? '-' ?></td>
                <td><?= $n['dev'] ?></td>
                <td>
                    <a href="visualizar_nota.php?id=<?= $n['id'] ?>" class="btn btn-info btn-sm">Visualizar</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
function updateCNPJ(select) {
    const cnpj = select.options[select.selectedIndex].getAttribute('data-cnpj');
    document.getElementById('cnpj').value = cnpj || '';
}
</script>

</body>
</html>
