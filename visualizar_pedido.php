<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'conexao.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// ID do pedido
$pedido_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($pedido_id <= 0) {
    die('ID de pedido inválido.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['faturar'])) {
    $nnf = $_POST['nnf'] ?? '';
    $prevpagto = ($_POST['prevpagto'] ?? '') !== '' ? $_POST['prevpagto'] : null;

    $sql = "UPDATE scnp_ped 
               SET faturado = 'SIM', data_faturamento = CURDATE(), nnf = ?, prevpagto = ?
             WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $nnf, $prevpagto, $pedido_id);

    if ($stmt->execute()) {
        $msg = 'Pedido faturado com sucesso.';
    } else {
        $msg = 'Falha ao faturar: ' . $stmt->error;
    }
    $stmt->close();
    header('Location: visualizar_pedido.php?id=' . $pedido_id . '&msg=' . urlencode($msg));
    exit;
}

// POST: Update / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update') {
        $tipo             = isset($_POST['tipo']) ? $_POST['tipo'] : 'SERVICO';
        $faturado         = isset($_POST['faturado']) ? $_POST['faturado'] : 'NAO';
        $descricao        = isset($_POST['descricao']) ? $_POST['descricao'] : '';
        $data_faturamento = (isset($_POST['data_faturamento']) && $_POST['data_faturamento'] !== '') ? $_POST['data_faturamento'] : null;
        $prevpagto        = (isset($_POST['prevpagto']) && $_POST['prevpagto'] !== '') ? $_POST['prevpagto'] : null;

        // Normaliza "valor" (aceita 1.234,56 ou 1234.56)
        $valor_raw = isset($_POST['valor']) ? $_POST['valor'] : '0';
        // remove tudo exceto dígitos, vírgula e ponto
        $valor_sanit = preg_replace('/[^\d\.,]/', '', $valor_raw);
        // se tiver vírgula e ponto, assume formato BR e troca vírgula por ponto
        if (strpos($valor_sanit, ',') !== false && strpos($valor_sanit, '.') !== false) {
            $valor_sanit = str_replace('.', '', $valor_sanit);     // remove separador de milhar
            $valor_sanit = str_replace(',', '.', $valor_sanit);    // vírgula -> ponto decimal
        } else {
            // se só tiver vírgula, trata como decimal
            $valor_sanit = str_replace(',', '.', $valor_sanit);
        }
        $valor = (float)$valor_sanit;

        // Regra: se faturado = NAO, zera a data de faturamento
        if ($faturado === 'NAO') {
            $data_faturamento = null;
        }

        // Monta UPDATE conforme combinações de NULL
        if ($data_faturamento === null && $prevpagto === null) {
            $sql = "UPDATE scnp_ped
                       SET tipo = ?, faturado = ?, data_faturamento = NULL, prevpagto = NULL, descricao = ?, valor = ?
                     WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssdi', $tipo, $faturado, $descricao, $valor, $pedido_id);
        } elseif ($data_faturamento === null && $prevpagto !== null) {
            $sql = "UPDATE scnp_ped
                       SET tipo = ?, faturado = ?, data_faturamento = NULL, prevpagto = ?, descricao = ?, valor = ?
                     WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssdi', $tipo, $faturado, $prevpagto, $descricao, $valor, $pedido_id);
        } elseif ($data_faturamento !== null && $prevpagto === null) {
            $sql = "UPDATE scnp_ped
                       SET tipo = ?, faturado = ?, data_faturamento = ?, prevpagto = NULL, descricao = ?, valor = ?
                     WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssdi', $tipo, $faturado, $data_faturamento, $descricao, $valor, $pedido_id);
        } else { // ambos preenchidos
            $sql = "UPDATE scnp_ped
                       SET tipo = ?, faturado = ?, data_faturamento = ?, prevpagto = ?, descricao = ?, valor = ?
                     WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssssdi', $tipo, $faturado, $data_faturamento, $prevpagto, $descricao, $valor, $pedido_id);
        }

        if ($stmt->execute()) {
            $msg = 'Pedido atualizado com sucesso.';
        } else {
            $msg = 'Falha ao atualizar: ' . $stmt->error;
        }
        $stmt->close();
        header('Location: visualizar_pedido.php?id=' . $pedido_id . '&msg=' . urlencode($msg));
        exit;
    }

    if ($_POST['action'] === 'delete') {
        $sql = "DELETE FROM scnp_ped WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $pedido_id);
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: cadpedidos.php?msg=' . urlencode('Pedido excluído com sucesso.'));
            exit;
        } else {
            $msg = 'Falha ao excluir: ' . $stmt->error;
            $stmt->close();
            header('Location: visualizar_pedido.php?id=' . $pedido_id . '&msg=' . urlencode($msg));
            exit;
        }
    }
}

// Buscar registro
$sql = "SELECT id, cliente_id, cnpj, numero, data, tipo, descricao, valor, faturado, data_faturamento, prevpagto, nnf
          FROM scnp_ped WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $pedido_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die('Pedido não encontrado.');
}
$pedido = $result->fetch_assoc();
$csll = $pedido['valor'] * 0.01;
$pis = $pedido['valor'] * 0.0065;
$cofins = $pedido['valor'] * 0.03;
$stmt->close();

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Visualizar Pedido #<?php echo h($pedido['id']); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h3 class="mb-0">Pedido #<?php echo h($pedido['id']); ?> — <?php echo h($pedido['numero']); ?></h3>
        <small class="text-muted">Cliente ID: <?php echo h($pedido['cliente_id']); ?></small>
      </div>
      <div class="d-flex gap-2">
        <a href="cadpedidos.php" class="btn btn-outline-secondary">Voltar</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editarModal">Editar</button>
        <form id="formExcluir" action="" method="post" class="d-inline">
          <input type="hidden" name="action" value="delete">
          <button type="button" class="btn btn-danger" onclick="confirmarExclusao()">Excluir</button>
        </form>
      </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-info"><?php echo h($_GET['msg']); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
      <div class="card-body">
        <div class="row gy-2">
          <div class="col-md-4"><strong>CNPJ:</strong> <?php echo h($pedido['cnpj']); ?></div>
          <div class="col-md-4"><strong>Número:</strong> <?php echo h($pedido['numero']); ?></div>
          <div class="col-md-4"><strong>Data:</strong> <?php echo h($pedido['data']); ?></div>

          <div class="col-md-4"><strong>Tipo:</strong> <?php echo h($pedido['tipo']); ?></div>
          <div class="col-md-4"><strong>Faturado:</strong> <?php echo h($pedido['faturado']); ?></div>
          <div class="col-md-4"><strong>Data de Faturamento:</strong> <?php echo h($pedido['data_faturamento']); ?></div>

          <div class="col-md-4"><strong>Previsão de Pagamento:</strong> <?php echo h($pedido['prevpagto']); ?></div>
          <div class="col-md-4"><strong>NF:</strong> <?php echo h($pedido['nnf']); ?></div>
          <div class="col-md-4"><strong>Valor:</strong> R$ <?php echo number_format((float)$pedido['valor'], 2, ',', '.'); ?></div>

          <div class="col-12 mt-2">
            <strong>Descrição:</strong>
            <div class="border rounded p-2 bg-light"><?php echo nl2br(h($pedido['descricao'])); ?><br>Ordem de Compra: <?php echo h($pedido['numero']); ?></div>
          </div>
        </div>
      </div>
    </div>
      <?php if ($pedido['tipo'] !== 'VENDA'): ?>
<div class="card mt-4 p-3 border-info">
    <div class="row">
        <div class="col-md-4"><strong>CSLL (1%):</strong> R$ <?= number_format($csll, 2, ',', '.') ?></div>
        <div class="col-md-4"><strong>PIS (0,65%):</strong> R$ <?= number_format($pis, 2, ',', '.') ?></div>
        <div class="col-md-4"><strong>COFINS (3%):</strong> R$ <?= number_format($cofins, 2, ',', '.') ?></div>
    </div>
</div>
<?php endif; ?>

<?php if ($pedido['faturado'] != 'SIM'): ?>
    <button type="button" class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#modalFaturar">
        Faturar Pedido
    </button>
    <?php endif; ?>
</div>  

    <!-- Modal de Edição -->
    <div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form method="post" action="" class="modal-content">
          <input type="hidden" name="action" value="update">
          <div class="modal-header">
            <h5 class="modal-title" id="editarLabel">Editar Pedido #<?php echo h($pedido['id']); ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <div class="modal-body">

            <div class="mb-3">
              <label for="tipo" class="form-label">Tipo</label>
              <select class="form-select" id="tipo" name="tipo" required>
                <option value="SERVICO" <?php echo ($pedido['tipo']=='SERVICO'?'selected':''); ?>>SERVIÇO</option>
                <option value="VENDA"   <?php echo ($pedido['tipo']=='VENDA'  ?'selected':''); ?>>VENDA</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="faturado" class="form-label">Faturado</label>
              <select class="form-select" id="faturado" name="faturado" required>
                <option value="NAO" <?php echo ($pedido['faturado']=='NAO'?'selected':''); ?>>NÃO</option>
                <option value="SIM" <?php echo ($pedido['faturado']=='SIM'?'selected':''); ?>>SIM</option>
              </select>
              <div class="form-text">Se “NÃO”, a data de faturamento será limpa.</div>
            </div>

            <div class="mb-3">
              <label for="data_faturamento" class="form-label">Data de faturamento</label>
              <input type="date" class="form-control" id="data_faturamento" name="data_faturamento"
                     value="<?php echo ($pedido['data_faturamento'] ? h($pedido['data_faturamento']) : ''); ?>">
            </div>

            <div class="mb-3">
              <label for="prevpagto" class="form-label">Previsão de pagamento</label>
              <input type="date" class="form-control" id="prevpagto_edit" name="prevpagto"
                      value="<?php echo ($pedido['prevpagto'] ? h($pedido['prevpagto']) : ''); ?>">
            </div>

            <div class="mb-3">
              <label for="valor" class="form-label">Valor (R$)</label>
              <input type="number" class="form-control" id="valor" name="valor" min="0" step="0.01"
                     value="<?php echo number_format((float)$pedido['valor'], 2, '.', ''); ?>">
              <div class="form-text">Use ponto como separador decimal (ex.: 1234.56). Você também pode digitar 1.234,56.</div>
            </div>

            <div class="mb-3">
              <label for="descricao" class="form-label">Descrição</label>
              <textarea class="form-control" id="descricao" name="descricao" rows="4"><?php echo h($pedido['descricao']); ?></textarea>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">Salvar alterações</button>
          </div>
        </form>
      </div>
    </div>

  </div>
<div class="modal fade" id="modalFaturar" tabindex="-1" aria-labelledby="modalFaturarLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
        <input type="hidden" name="faturar" value="1">
        <div class="modal-header">
            <h5 class="modal-title" id="modalFaturarLabel">Faturar Pedido</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label for="nnf" class="form-label">Número da NF</label>
                <input type="text" class="form-control" name="nnf" id="nnf" required>
            </div>
            <div class="mb-3">
                <label for="prevpagto" class="form-label">Data Prevista de Pagamento</label>
                <input type="date" class="form-control" name="prevpagto" id="prevpagto_fat" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Confirmar Faturamento</button>
        </div>
    </form>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Preenche somente o campo do modal Faturar
  const prevPagtoFat = document.getElementById('prevpagto_fat');
  if (prevPagtoFat) {
    const hoje = new Date();
    hoje.setDate(hoje.getDate() + 60);
    const ano = hoje.getFullYear();
    const mes = String(hoje.getMonth() + 1).padStart(2, '0');
    const dia = String(hoje.getDate()).padStart(2, '0');
    prevPagtoFat.value = `${ano}-${mes}-${dia}`;
  }
});
</script>

  <!-- Bootstrap 5 Bundle (inclui Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  function confirmarExclusao() {
    if (confirm('Tem certeza que deseja excluir este pedido? Esta ação não pode ser desfeita.')) {
      document.getElementById('formExcluir').submit();
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    var sel = document.getElementById('faturado');
    var inputData = document.getElementById('data_faturamento');

    function toggleDataFaturamento() {
      if (!sel || !inputData) return;
      if (sel.value === 'NAO') {
        inputData.value = '';
        inputData.disabled = true;
      } else {
        inputData.disabled = false;
      }
    }

    if (sel) {
      sel.addEventListener('change', toggleDataFaturamento);
      toggleDataFaturamento();
    }
  });
  </script>
</body>
</html>
