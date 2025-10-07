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

$TAXA = 0.07680; // 7,68%

// ====== Filtros (GET) ======
$filtroClienteId = isset($_GET['cliente_id']) && $_GET['cliente_id'] !== '' ? (int)$_GET['cliente_id'] : null;
$filtroTipo      = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';

// Carregar clientes para o select
$clientes = $conn->query("SELECT id, nome FROM helpdesk_clientes ORDER BY nome ASC");

// ====== Janela: 6 meses para trás e 6 meses para frente ======
// Mês inicial = -6 meses a partir do mês atual
// Mês final = +5 meses a partir do mês atual (totalizando 12 meses)
$startDate = date('Y-m-01', strtotime('-6 months'));
$endDate   = date('Y-m-t', strtotime('+5 months'));

// Montar todos os meses da janela
$months = [];
$cursor = new DateTime($startDate);
$end    = new DateTime($endDate);
while ($cursor <= $end) {
    $ym = $cursor->format('Y-m');
    $label = $cursor->format('M/Y');
    $months[$ym] = [
        'label' => $cursor->format('M/Y'),
        'bruto' => 0.0,
    ];
    $cursor->modify('+1 month');
}

// ====== Query agregada por prevpagto dentro da janela e filtros ======
$sql = "
    SELECT
        DATE_FORMAT(p.prevpagto, '%Y-%m') AS ym,
        SUM(p.valor) AS bruto
    FROM scnp_ped p
    WHERE p.faturado = 'SIM'
      AND p.prevpagto IS NOT NULL
      AND p.prevpagto BETWEEN ? AND ?
";
$types = "ss";
$params = [$startDate, $endDate];

if ($filtroClienteId) {
    $sql .= " AND p.cliente_id = ? ";
    $types .= "i";
    $params[] = $filtroClienteId;
}
if ($filtroTipo === 'SERVICO' || $filtroTipo === 'VENDA') {
    $sql .= " AND p.tipo = ? ";
    $types .= "s";
    $params[] = $filtroTipo;
}

$sql .= " GROUP BY ym ORDER BY ym ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) { die("Erro ao preparar consulta: " . $conn->error); }
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

// Preencher agregados na tabela de meses
if ($res && $res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) {
        $ym = $r['ym'];
        $bruto = (float)$r['bruto'];
        if (isset($months[$ym])) {
            $months[$ym]['bruto'] = $bruto;
        }
    }
}
$stmt->close();

// Montar categorias e série (líquido) na ordem cronológica
$categorias = [];
$serieLiquido = [];

$totalBruto = 0.0;
$totalTaxa = 0.0;
$totalLiquido = 0.0;

foreach ($months as $ym => $data) {
    $categorias[] = $data['label'];
    $bruto = (float)$data['bruto'];
    $taxa = $bruto * $TAXA;
    $liq  = $bruto - $taxa;

    $serieLiquido[] = round($liq, 2);

    $totalBruto   += $bruto;
    $totalTaxa    += $taxa;
    $totalLiquido += $liq;
}

function brl($v) {
    return 'R$ ' . number_format($v, 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Painel de Faturas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap (tema escuro por data-bs-theme) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Highcharts -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>

    <style>
        body { background-color: #121212; color: #e0e0e0; }
        .card { background-color: #1e1e1e; border-color: #2a2a2a; }
        .form-select, .form-control { background-color: #1e1e1e; color: #e0e0e0; border-color: #2a2a2a; }
        .btn-outline-light { border-color: #bbb; }
        .btn-secondary { background-color: #2b2b2b; border-color: #3a3a3a; }
        .text-muted { color: #b0b0b0 !important; }
    </style>
</head>
<body>
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="m-0">Painel de Faturas</h4>
        <div class="d-flex gap-2">
            <a href="cadpedidos.php" class="btn btn-secondary">Voltar</a>
        </div>
    </div>

    <!-- Filtros -->
    <form class="row g-2 align-items-end mb-3" method="GET">
        <div class="col-md-5">
            <label class="form-label">Cliente</label>
            <select name="cliente_id" class="form-select">
                <option value="">Todos</option>
                <?php if ($clientes && $clientes->num_rows): ?>
                    <?php while($c = $clientes->fetch_assoc()): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $filtroClienteId == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nome']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tipo</label>
            <select name="tipo" class="form-select">
                <option value="">Todos</option>
                <option value="SERVICO" <?= $filtroTipo==='SERVICO'?'selected':'' ?>>SERVIÇO</option>
                <option value="VENDA"   <?= $filtroTipo==='VENDA'  ?'selected':'' ?>>VENDA</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button class="btn btn-outline-light flex-fill">Aplicar</button>
            <a class="btn btn-outline-light flex-fill" href="painelfat.php">Limpar</a>
        </div>
        <div class="col-12">
            <small class="text-muted">
                Janela: <strong><?= date('d/m/Y', strtotime($startDate)) ?></strong> a <strong><?= date('d/m/Y', strtotime($endDate)) ?></strong> (últimos 12 meses por <em>Prev. de Pagto</em>)
            </small>
        </div>
    </form>

    <?php if (array_sum(array_column($months, 'bruto')) == 0): ?>
        <div class="alert alert-warning">
            Não há dados de <em>prevpagto</em> nessa janela/filtros. Ajuste os filtros ou cadastre previsões.
        </div>
    <?php else: ?>
        <!-- Totais -->
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Bruto (por previsão)</div>
                        <div class="fs-5 fw-semibold"><?= brl($totalBruto) ?></div>
                        <small class="text-muted">Soma na janela e filtros aplicados</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Taxa (7,68%) - Prev.</div>
                        <div class="fs-5 fw-semibold"><?= brl($totalTaxa) ?></div>
                        <small class="text-muted">Estimativa total de taxas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Líquido (Prev.)</div>
                        <div class="fs-5 fw-semibold"><?= brl($totalLiquido) ?></div>
                        <small class="text-muted">Recebimento estimado</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div id="grafico-faturas" style="height: 520px;"></div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const categorias = <?= json_encode(array_values(array_column($months, 'label')), JSON_UNESCAPED_UNICODE) ?>;
    const serieLiquido = <?= json_encode($serieLiquido, JSON_UNESCAPED_UNICODE) ?>;

    if (!categorias.length) return;

    // Localização e tema escuro para Highcharts
    Highcharts.setOptions({
        lang: {
            decimalPoint: ',',
            thousandsSep: '.',
            contextButtonTitle: 'Menu do gráfico',
            downloadPNG: 'Baixar PNG',
            downloadJPEG: 'Baixar JPEG',
            downloadPDF: 'Baixar PDF',
            downloadSVG: 'Baixar SVG',
            viewFullscreen: 'Tela cheia',
            printChart: 'Imprimir gráfico',
            months: ['janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'],
            shortMonths: ['jan','fev','mar','abr','mai','jun','jul','ago','set','out','nov','dez'],
            weekdays: ['Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado'],
            loading: 'Carregando...'
        }
    });

    Highcharts.chart('grafico-faturas', {
        chart: {
            type: 'column',
            backgroundColor: '#121212'
        },
        title: {
            text: 'Previsão de Recebimentos (Líquido por mês)',
            style: { color: '#e0e0e0' }
        },
        subtitle: {
            text: 'Baseado em Prev. de Pagto nos últimos 12 meses (líquido = valor − 7,68%)',
            style: { color: '#bdbdbd' }
        },
        xAxis: {
            categories: categorias,
            crosshair: true,
            labels: {
                rotation: -45,
                style: { color: '#e0e0e0' }
            },
            lineColor: '#444',
            tickColor: '#444'
        },
        yAxis: {
            min: 0,
            title: { text: 'Valor (R$)', style: { color: '#e0e0e0' } },
            labels: { style: { color: '#e0e0e0' } },
            gridLineColor: '#2a2a2a'
        },
        legend: {
            itemStyle: { color: '#e0e0e0' }
        },
        tooltip: {
            backgroundColor: '#1e1e1e',
            borderColor: '#444',
            style: { color: '#e0e0e0' },
            pointFormatter: function () {
                return 'Líquido: <b>R$ ' + Highcharts.numberFormat(this.y, 2, ',', '.') + '</b><br/>';
            }
        },
        plotOptions: {
            column: {
                dataLabels: {
                    enabled: true,
                    formatter: function () {
                        return 'R$ ' + Highcharts.numberFormat(this.y, 2, ',', '.');
                    },
                    style: { color: '#e0e0e0', textOutline: 'none' }
                },
                borderColor: '#444'
            }
        },
        series: [{
            name: 'Líquido (prev.)',
            data: serieLiquido
        }],
        credits: { enabled: false }
    });
});
</script>
</body>
</html>
