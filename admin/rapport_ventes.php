<?php
// ============================================
// rapport_ventes.php
// État récapitulatif des ventes par catégorie
// ============================================
include 'function/func_rapport_page.php';
include '../connect_pdo.php';

// Période par défaut : mois en cours
$date_debut = $_GET['date_debut'] ?? date('Y-m-01');
$date_fin   = $_GET['date_fin']   ?? date('Y-m-d');

// Récupérer les données
$ventes_categorie = getVentesParCategorie($pdo, $date_debut, $date_fin);
$total_global     = array_sum(array_column($ventes_categorie, 'total_ventes'));
$total_commandes  = array_sum(array_column($ventes_categorie, 'nb_commandes'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Ventes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #f5f6fa;
            font-family: 'Segoe UI', sans-serif;
            color: #212529;
        }

        /* ── Header ── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
        }
        .page-header h4 {
            font-weight: 700;
            font-size: 18px;
            color: #1a1a2e;
        }
        .page-header small { color: #868e96; font-size: 13px; }

        /* ── Filter Card ── */
        .filter-card {
            background: #fff;
            border-radius: 14px;
            padding: 18px 22px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            display: flex;
            align-items: flex-end;
            gap: 16px;
            flex-wrap: wrap;
        }
        .filter-card label {
            font-size: 12px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
            display: block;
        }
        .filter-card input[type="date"] {
            border: 1.5px solid #e9ecef;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
            color: #495057;
            background: #f8f9fa;
            outline: none;
        }
        .filter-card input[type="date"]:focus { border-color: #1a1a2e; background:#fff; }
        .btn-filter {
            background: #1a1a2e;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 9px 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: opacity .2s;
        }
        .btn-filter:hover { opacity: .85; }

        /* ── Stat Cards ── */
        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .stat-icon {
            width: 46px; height: 46px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .icon-dark   { background: #1a1a2e; color: #fff; }
        .icon-green  { background: #d3f9d8; color: #2f9e44; }
        .icon-blue   { background: #dbe4ff; color: #3b5bdb; }
        .stat-info p { font-size: 12px; color: #868e96; margin-bottom: 2px; font-weight:500; }
        .stat-info h3 { font-size: 22px; font-weight: 700; color: #1a1a2e; }

        /* ── Table Card ── */
        .table-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            overflow: hidden;
            margin-top: 20px;
        }
        .table-card-header {
            padding: 16px 22px;
            border-bottom: 1px solid #f1f3f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .table-card-header h6 { font-weight: 700; font-size: 14px; margin: 0; }

        .table { margin: 0; }
        .table thead th {
            background: #f8f9fa;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #868e96;
            border: none;
            padding: 13px 16px;
        }
        .table tbody td {
            padding: 14px 16px;
            font-size: 13px;
            vertical-align: middle;
            border-color: #f1f3f5;
        }
        .table tbody tr:hover { background: #f8f9fa; }

        /* ── Category Badge ── */
        .cat-badge {
            background: #e9ecef;
            color: #495057;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 600;
        }

        /* ── Progress bar ── */
        .progress-wrap { min-width: 100px; }
        .mini-progress {
            height: 6px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 4px;
        }
        .mini-progress-bar {
            height: 100%;
            background: #1a1a2e;
            border-radius: 10px;
            transition: width .4s;
        }
        .pct-text { font-size: 11px; color: #868e96; }

        /* ── Empty ── */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #adb5bd;
        }
        .empty-state i { font-size: 40px; display: block; margin-bottom: 12px; }
        .empty-state p { font-size: 14px; }

        /* ── Period badge ── */
        .period-info {
            background: #f1f3f5;
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 12px;
            color: #495057;
            font-weight: 500;
        }
    </style>
</head>
<body>
<div class="p-4">

    <!-- HEADER -->
    <div class="page-header">
        <div>
            <h4><i class="bi bi-bar-chart-fill me-2"></i>Rapport des Ventes par Catégorie</h4>
            <small>Récapitulatif des ventes sur une période donnée</small>
        </div>
        <span class="period-info">
            <i class="bi bi-calendar3 me-1"></i>
            <?php echo date('d/m/Y', strtotime($date_debut)); ?> →
            <?php echo date('d/m/Y', strtotime($date_fin)); ?>
        </span>
    </div>

    <!-- FILTER -->
    <div class="filter-card">
        <form method="GET" action="rapport_ventes.php" style="display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end;width:100%;">
            <div>
                <label>Date début</label>
                <input type="date" name="date_debut" value="<?php echo $date_debut; ?>">
            </div>
            <div>
                <label>Date fin</label>
                <input type="date" name="date_fin" value="<?php echo $date_fin; ?>">
            </div>
            <button type="submit" class="btn-filter">
                <i class="bi bi-funnel-fill"></i> Filtrer
            </button>
        </form>
    </div>

    <!-- STATS GLOBALES -->
    <div class="row g-3 mb-2">
        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon icon-dark"><i class="bi bi-cash-stack"></i></div>
                <div class="stat-info">
                    <p>Chiffre d'affaires</p>
                    <h3><?php echo number_format($total_global, 0, ',', ' '); ?> DA</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon icon-green"><i class="bi bi-cart-check-fill"></i></div>
                <div class="stat-info">
                    <p>Total commandes</p>
                    <h3><?php echo $total_commandes; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon icon-blue"><i class="bi bi-grid-fill"></i></div>
                <div class="stat-info">
                    <p>Catégories actives</p>
                    <h3><?php echo count($ventes_categorie); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-card">
        <div class="table-card-header">
            <h6><i class="bi bi-list-ul me-2"></i>Détail par catégorie</h6>
        </div>

        <div class="table-responsive">
            <table class="table" id="rapportTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Catégorie</th>
                        <th>Nb Commandes</th>
                        <th>Qté vendue</th>
                        <th>Chiffre d'affaires</th>
                        <th>Part du total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ventes_categorie)): ?>
                        <?php foreach ($ventes_categorie as $i => $v): ?>
                        <?php $pct = $total_global > 0 ? round($v['total_ventes'] / $total_global * 100, 1) : 0; ?>
                        <tr>
                            <td class="text-muted fw-semibold"><?php echo $i + 1; ?></td>
                            <td>
                                <span class="cat-badge">
                                    <?php echo htmlspecialchars($v['categorie_nom'] ?? 'Sans catégorie'); ?>
                                </span>
                            </td>
                            <td class="fw-semibold"><?php echo $v['nb_commandes']; ?></td>
                            <td class="fw-semibold"><?php echo $v['total_quantite']; ?></td>
                            <td class="fw-bold"><?php echo number_format($v['total_ventes'], 0, ',', ' '); ?> DA</td>
                            <td>
                                <div class="progress-wrap">
                                    <span class="pct-text"><?php echo $pct; ?>%</span>
                                    <div class="mini-progress">
                                        <div class="mini-progress-bar" style="width:<?php echo $pct; ?>%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <!-- Ligne total -->
                        <tr style="background:#f8f9fa;">
                            <td colspan="2" class="fw-bold text-end">Total</td>
                            <td class="fw-bold"><?php echo $total_commandes; ?></td>
                            <td class="fw-bold"><?php echo array_sum(array_column($ventes_categorie, 'total_quantite')); ?></td>
                            <td class="fw-bold text-success"><?php echo number_format($total_global, 0, ',', ' '); ?> DA</td>
                            <td class="fw-bold">100%</td>
                        </tr>

                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-bar-chart"></i>
                                    <p>Aucune vente trouvée pour cette période.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>