<?php
include 'function/func_command_page.php';
include '../connect_pdo.php';

// ============================================
// تغيير حالة commande
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commande_id'], $_POST['status'])) {
    updateStatutCommande($pdo, $_POST['commande_id'], $_POST['status']);
    header('Location: gestion_commande.php');
    exit();
}

// ============================================
// جلب البيانات
// ============================================
$commandes      = getAllCommandes($pdo);
$nbAujourdhui   = getCommandesAujourdhui($pdo);
$nbSemaine      = getCommandesSemaine($pdo);
$nbMois         = getCommandesMois($pdo);
$nbEnAttente    = getCommandesParStatus($pdo, 'en_attente');
$nbConfirme     = getCommandesParStatus($pdo, 'confirme');
$nbLivre        = getCommandesParStatus($pdo, 'livre');
$nbAnnule       = getCommandesParStatus($pdo, 'annule');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Commandes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="style/gestion_commande_style.css" rel="stylesheet">
</head>
<body>
<div class="p-4">


    <!-- ==================== HEADER ==================== -->
    <div class="page-header">
        <div>
            <h4><i class="bi bi-receipt me-2"></i>Gestion des Commandes</h4>
            <small>Suivi et gestion de toutes les commandes</small>
        </div>
    </div>


    <!-- ==================== STATS PÉRIODE ==================== -->
    <div class="row g-3 mb-3">

        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon icon-dark">
                    <i class="bi bi-calendar-day"></i>
                </div>
                <div class="stat-info">
                    <p>Aujourd'hui</p>
                    <h3><?php echo $nbAujourdhui; ?></h3>
                    <small>commandes du jour</small>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon icon-blue">
                    <i class="bi bi-calendar-week"></i>
                </div>
                <div class="stat-info">
                    <p>Cette semaine</p>
                    <h3><?php echo $nbSemaine; ?></h3>
                    <small>7 derniers jours</small>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon icon-green">
                    <i class="bi bi-calendar-month"></i>
                </div>
                <div class="stat-info">
                    <p>Ce mois</p>
                    <h3><?php echo $nbMois; ?></h3>
                    <small>30 derniers jours</small>
                </div>
            </div>
        </div>

    </div>


    <!-- ==================== STATS STATUT ==================== -->
    <div class="row g-3 mb-2">

        <div class="col-6 col-sm-3">
            <div class="stat-card">
                <div class="stat-icon icon-orange">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="stat-info">
                    <p>En attente</p>
                    <h3><?php echo $nbEnAttente; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-3">
            <div class="stat-card">
                <div class="stat-icon icon-blue">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-info">
                    <p>Confirmées</p>
                    <h3><?php echo $nbConfirme; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-3">
            <div class="stat-card">
                <div class="stat-icon icon-green">
                    <i class="bi bi-truck"></i>
                </div>
                <div class="stat-info">
                    <p>Livrées</p>
                    <h3><?php echo $nbLivre; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-3">
            <div class="stat-card">
                <div class="stat-icon icon-red">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="stat-info">
                    <p>Annulées</p>
                    <h3><?php echo $nbAnnule; ?></h3>
                </div>
            </div>
        </div>

    </div>


    <!-- ==================== TABLE ==================== -->
    <div class="table-card">

        <div class="table-card-header">
            <h6><i class="bi bi-list-ul me-2"></i>Toutes les commandes</h6>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" id="commandeTable">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Client</th>
                        <th>Produit</th>
                        <th>Qté</th>
                        <th>Prix unit.</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

                    <?php if (!empty($commandes)): ?>
                        <?php foreach ($commandes as $c): ?>
                        <tr>
                            <td class="text-muted fw-semibold">#<?php echo $c['commande_id']; ?></td>

                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars($c['client_nom']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($c['client_email']); ?></small>
                            </td>

                            <td class="fw-semibold"><?php echo htmlspecialchars($c['produit_nom']); ?></td>

                            <td><?php echo $c['quantite']; ?></td>

                            <td><?php echo number_format($c['prix_unitaire'], 0, ',', ' '); ?> DA</td>

                            <td class="fw-bold"><?php echo number_format($c['total'], 0, ',', ' '); ?> DA</td>

                            <td class="text-muted" style="white-space:nowrap;">
                                <?php echo date('d/m/Y', strtotime($c['date_commande'])); ?>
                                <br>
                                <small><?php echo date('H:i', strtotime($c['date_commande'])); ?></small>
                            </td>

                            <!-- Statut -->
                            <td>
                                <?php if ($c['status'] === 'en_attente'): ?>
                                    <span class="badge-attente">En attente</span>
                                <?php elseif ($c['status'] === 'confirme'): ?>
                                    <span class="badge-confirme">Confirmé</span>
                                <?php elseif ($c['status'] === 'livre'): ?>
                                    <span class="badge-livre">Livré</span>
                                <?php elseif ($c['status'] === 'annule'): ?>
                                    <span class="badge-annule">Annulé</span>
                                <?php endif; ?>
                            </td>

                            <!-- Actions -->
                            <td>
                                <button class="btn-status"
                                    data-bs-toggle="modal"
                                    data-bs-target="#statusModal"
                                    data-id="<?php echo $c['commande_id']; ?>"
                                    data-client="<?php echo htmlspecialchars($c['client_nom']); ?>"
                                    data-produit="<?php echo htmlspecialchars($c['produit_nom']); ?>"
                                    data-total="<?php echo number_format($c['total'], 0, ',', ' '); ?>"
                                    data-date="<?php echo date('d/m/Y H:i', strtotime($c['date_commande'])); ?>"
                                    data-status="<?php echo $c['status']; ?>">
                                    <i class="bi bi-pencil me-1"></i> Modifier
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="bi bi-receipt"></i>
                                    <p>Aucune commande enregistrée pour le moment.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>

    </div><!-- /table-card -->


    <!-- ==================== MODAL CHANGER STATUT ==================== -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Modifier la commande
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- Détails de la commande -->
                    <div class="mb-4">
                        <div class="detail-row">
                            <span class="detail-label">Client</span>
                            <span class="detail-value" id="m-client"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Produit</span>
                            <span class="detail-value" id="m-produit"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total</span>
                            <span class="detail-value" id="m-total"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Date</span>
                            <span class="detail-value" id="m-date"></span>
                        </div>
                    </div>

                    <!-- Changer statut -->
                    <form method="POST" action="gestion_commande.php" id="statusForm">
                        <input type="hidden" name="commande_id" id="m-id">
                        <div class="mb-2">
                            <label class="form-label">Nouveau statut</label>
                            <select name="status" id="m-status" class="form-select">
                                <option value="en_attente">En attente</option>
                                <option value="confirme">Confirmé</option>
                                <option value="livre">Livré</option>
                                <option value="annule">Annulé</option>
                            </select>
                        </div>
                    </form>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" form="statusForm" class="btn btn-dark">
                        <i class="bi bi-check-lg me-1"></i> Enregistrer
                    </button>
                </div>

            </div>
        </div>
    </div>


</div><!-- /p-4 -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

    // ============================================
    // Remplir le modal avec les données
    // ============================================
    document.getElementById('statusModal').addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        document.getElementById('m-id').value      = btn.getAttribute('data-id');
        document.getElementById('m-client').textContent  = btn.getAttribute('data-client');
        document.getElementById('m-produit').textContent = btn.getAttribute('data-produit');
        document.getElementById('m-total').textContent   = btn.getAttribute('data-total') + ' DA';
        document.getElementById('m-date').textContent    = btn.getAttribute('data-date');
        document.getElementById('m-status').value        = btn.getAttribute('data-status');
    });

    // ============================================
    // Recherche en temps réel
    // ============================================
    document.getElementById('searchInput').addEventListener('keyup', function () {
        var filter = this.value.toLowerCase();
        document.querySelectorAll('#commandeTable tbody tr').forEach(function (row) {
            row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
        });
    });

</script>
</body>
</html>