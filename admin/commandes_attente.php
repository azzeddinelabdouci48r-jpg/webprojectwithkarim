<?php
// ============================================
// commandes_attente.php
// État récapitulatif des commandes en attente
// ============================================
include 'function/func_rapport_page.php';
include 'function/func_command_page.php';
include '../connect_pdo.php';

// Récupérer les commandes en attente de livraison
// (statut = en_attente OU confirme = pas encore livré)
$commandes_attente = getCommandesEnAttenteLivraison($pdo);
$nb_attente   = getCommandesParStatus($pdo, 'en_attente');
$nb_confirme  = getCommandesParStatus($pdo, 'confirme');
$total_valeur = 0;
foreach ($commandes_attente as $c) {
    $total_valeur += $c['total'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commandes en Attente</title>
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
        .page-header h4 { font-weight: 700; font-size: 18px; color: #1a1a2e; }
        .page-header small { color: #868e96; font-size: 13px; }

        /* ── Alert banner ── */
        .alert-banner {
            background: #fff3cd;
            border: 1.5px solid #ffc107;
            border-radius: 12px;
            padding: 14px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #664d03;
            font-weight: 500;
        }
        .alert-banner i { font-size: 18px; color: #ffc107; }

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
        .icon-orange { background: #fff3bf; color: #f08c00; }
        .icon-blue   { background: #dbe4ff; color: #3b5bdb; }
        .icon-red    { background: #ffe3e3; color: #c92a2a; }
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

        .search-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 7px 14px;
            border: 1.5px solid #e9ecef;
        }
        .search-box i { color: #adb5bd; font-size: 13px; }
        .search-box input {
            border: none;
            background: transparent;
            font-size: 13px;
            outline: none;
            width: 180px;
            color: #495057;
        }

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

        /* ── Badges statut ── */
        .badge-attente {
            background: #fff3bf; color: #e67700;
            border: 1px solid #ffd43b;
            border-radius: 20px; padding: 4px 12px;
            font-size: 12px; font-weight: 600;
        }
        .badge-confirme {
            background: #dbe4ff; color: #3b5bdb;
            border: 1px solid #748ffc;
            border-radius: 20px; padding: 4px 12px;
            font-size: 12px; font-weight: 600;
        }

        /* ── Urgency indicator ── */
        .days-badge {
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 700;
        }
        .days-urgent  { background: #ffe3e3; color: #c92a2a; }
        .days-warning { background: #fff3bf; color: #e67700; }
        .days-ok      { background: #d3f9d8; color: #2f9e44; }

        /* ── Action btn ── */
        .btn-view {
            background: #f1f3f5;
            border: none;
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            color: #495057;
            cursor: pointer;
            transition: background .15s;
        }
        .btn-view:hover { background: #1a1a2e; color: #fff; }

        /* ── Empty ── */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #adb5bd;
        }
        .empty-state i { font-size: 48px; display: block; margin-bottom: 12px; color: #2f9e44; }
        .empty-state p { font-size: 14px; }
        .empty-state .ok-msg { color: #2f9e44; font-weight: 600; font-size: 16px; }
    </style>
</head>
<body>
<div class="p-4">

    <!-- HEADER -->
    <div class="page-header">
        <div>
            <h4><i class="bi bi-hourglass-split me-2"></i>Commandes en Attente de Livraison</h4>
            <small>Suivi des commandes non encore livrées</small>
        </div>
        <?php if (count($commandes_attente) > 0): ?>
        <span style="background:#ffe3e3;color:#c92a2a;border-radius:8px;padding:6px 14px;font-size:12px;font-weight:700;">
            <i class="bi bi-exclamation-circle me-1"></i>
            <?php echo count($commandes_attente); ?> commande(s) à traiter
        </span>
        <?php endif; ?>
    </div>

    <!-- ALERT -->
    <?php if (count($commandes_attente) > 0): ?>
    <div class="alert-banner">
        <i class="bi bi-exclamation-triangle-fill"></i>
        Il y a <strong><?php echo count($commandes_attente); ?> commandes</strong> en attente de livraison pour une valeur totale de
        <strong><?php echo number_format($total_valeur, 0, ',', ' '); ?> DA</strong>.
        Veuillez les traiter dès que possible.
    </div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="row g-3 mb-2">
        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon icon-dark"><i class="bi bi-list-check"></i></div>
                <div class="stat-info">
                    <p>Total en attente</p>
                    <h3><?php echo count($commandes_attente); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon icon-orange"><i class="bi bi-hourglass"></i></div>
                <div class="stat-info">
                    <p>Statut En attente</p>
                    <h3><?php echo $nb_attente; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon icon-blue"><i class="bi bi-check-circle"></i></div>
                <div class="stat-info">
                    <p>Confirmées (non livrées)</p>
                    <h3><?php echo $nb_confirme; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-card">
        <div class="table-card-header">
            <h6><i class="bi bi-clock-history me-2"></i>Liste des commandes en attente</h6>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" id="attenteTable">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Client</th>
                        <th>Produit</th>
                        <th>Qté</th>
                        <th>Total</th>
                        <th>Date commande</th>
                        <th>Ancienneté</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($commandes_attente)): ?>
                        <?php foreach ($commandes_attente as $c): ?>
                        <?php
                            $jours = (int) floor((time() - strtotime($c['date_commande'])) / 86400);
                            $dayClass = $jours >= 7 ? 'days-urgent' : ($jours >= 3 ? 'days-warning' : 'days-ok');
                        ?>
                        <tr>
                            <td class="text-muted fw-semibold">#<?php echo $c['commande_id']; ?></td>

                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars($c['client_nom']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($c['client_email']); ?></small>
                            </td>

                            <td class="fw-semibold"><?php echo htmlspecialchars($c['produit_nom']); ?></td>

                            <td><?php echo $c['quantite']; ?></td>

                            <td class="fw-bold"><?php echo number_format($c['total'], 0, ',', ' '); ?> DA</td>

                            <td class="text-muted" style="white-space:nowrap;">
                                <?php echo date('d/m/Y', strtotime($c['date_commande'])); ?>
                                <br>
                                <small><?php echo date('H:i', strtotime($c['date_commande'])); ?></small>
                            </td>

                            <td>
                                <span class="days-badge <?php echo $dayClass; ?>">
                                    <?php echo $jours; ?> j
                                </span>
                            </td>

                            <td>
                                <?php if ($c['status'] === 'en_attente'): ?>
                                    <span class="badge-attente">En attente</span>
                                <?php elseif ($c['status'] === 'confirme'): ?>
                                    <span class="badge-confirme">Confirmé</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <a href="gestion_commande.php" class="btn-view">
                                    <i class="bi bi-pencil me-1"></i>Gérer
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <p class="ok-msg">Toutes les commandes sont livrées !</p>
                                    <p>Aucune commande en attente de livraison.</p>
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
<script>
    document.getElementById('searchInput').addEventListener('keyup', function () {
        var filter = this.value.toLowerCase();
        document.querySelectorAll('#attenteTable tbody tr').forEach(function (row) {
            row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
        });
    });
</script>
</body>
</html>