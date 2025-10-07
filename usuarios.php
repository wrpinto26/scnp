<?php
session_start();
include("conexao.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$isAdmin = ($_SESSION['usuario'] === 'admin');

// Inserir novo usuário
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['novo_usuario']) && $isAdmin) {
    $novo_usuario = $_POST['novo_usuario'];
    $nova_senha = hash('sha256', $_POST['nova_senha']);
    $stmt = $conn->prepare("INSERT INTO helpdesk_usuarios (usuario, senha) VALUES (?, ?)");
    $stmt->bind_param("ss", $novo_usuario, $nova_senha);
    $stmt->execute();
}

// Deletar usuário
if (isset($_GET['delete']) && $isAdmin) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM helpdesk_usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: usuarios.php");
    exit;
}

$usuarios = $conn->query("SELECT * FROM helpdesk_usuarios ORDER BY usuario ASC");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Usuários Cadastrados</h3>
        <a href="dashboard.php" class="btn btn-secondary">Voltar ao Dashboard</a>
    </div>

    <?php if ($isAdmin): ?>
    <form method="POST" class="mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <input type="text" name="novo_usuario" class="form-control" placeholder="Novo usuário" required>
            </div>
            <div class="col-md-4">
                <input type="password" name="nova_senha" class="form-control" placeholder="Senha" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Adicionar Usuário</button>
            </div>
        </div>
    </form>
    <?php endif; ?>

    <table class="table table-bordered table-striped table-sm">
        <thead class="table-light">
            <tr>
                
                <th style="text-align:center;">Usuário</th>
                <?php if ($isAdmin): ?>
                <th style="text-align:center;">Ações</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $usuarios->fetch_assoc()): ?>
            <tr>
                <td style="text-align:center;"><?php echo htmlspecialchars($row['usuario']); ?></td>
                <?php if ($isAdmin): ?>
                <td style="text-align:center;">
                    <?php if ($row['usuario'] !== 'admin'): ?>
                    <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Deseja realmente excluir este usuário?');">Remover</a>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>