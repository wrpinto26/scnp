<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Painel de Controle - NF/Pedidos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .card-option {
      border-radius: 10px;
      padding: 30px;
      text-align: center;
      color: white;
      box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
      transition: all 0.2s ease-in-out;
    }
    .card-option:hover {
      transform: scale(1.03);
      box-shadow: 0px 6px 15px rgba(0,0,0,0.2);
    }
    .bg-blue    { background-color: #0d6efd; }
    .bg-green   { background-color: #198754; }
    .bg-yellow  { background-color: #ffc107; color: #000; }
    .bg-red     { background-color: #dc3545; }
    a {
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="container mt-5">
    <h3 class="mb-4">Painel de Controle</h3>
    <div class="row g-4">
      <div class="col-md-6">
        <a href="cadpedidos.php">
          <div class="card-option bg-yellow">
            <h4><strong>Cadastro de Pedidos</strong></h4>
            <p>Registrar pedidos de serviço ou venda</p>
          </div>
        </a>
      </div>
      <div class="col-md-6">
        <a href="cadnotas.php">
          <div class="card-option bg-green">
            <h4><strong>Cadastro de NFs</strong></h4>
            <p>Registrar notas fiscais recebidas</p>
          </div>
        </a>
      </div>
      
      <div class="col-md-6">
        <a href="usuarios.php">
          <div class="card-option bg-blue">
            <h4><strong>Usuários</strong></h4>
            <p>Gerenciar usuários do sistema</p>
          </div>
        </a>
      </div>
      <div class="col-md-6">
        <a href="logout.php">
          <div class="card-option bg-red">
            <h4><strong>Sair</strong></h4>
            <p>Encerrar sessão</p>
          </div>
        </a>
      </div>
    </div>
  </div>
</body>
</html>