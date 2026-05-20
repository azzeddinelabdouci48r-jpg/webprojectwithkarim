<?php
include 'function/func_exp_page.php';
include '../connect_pdo.php';

// ============================================
// Créer une expédition
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    addExpedition(
        $pdo,
        $_POST['commande_id'],
        $_POST['adresse_livraison'],
        $_POST['transporteur'],
        $_POST['numero_suivi']
    );
    header('Location: gestion_expedition.php?success=add');
    exit();
}

// ============================================
// Modifier une expédition
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $date_liv = !empty($_POST['date_livraison']) ? $_POST['date_livraison'] : null;
    updateExpedition(
        $pdo,
        $_POST['expedition_id'],
        $_POST['status_livraison'],
        $_POST['transporteur'],
        $_POST['numero_suivi'],
        $date_liv
    );
    header('Location: gestion_expedition.php?success=edit');
    exit();
}

// ============================================
// Supprimer une expédition
// ============================================
if (isset($_GET['delete'])) {
    deleteExpedition($pdo, $_GET['delete']);
    header('Location: gestion_expedition.php');
    exit();
}

// ============================================
// جلب البيانات
// ============================================
$expeditions        = getAllExpeditions($pdo);
$commandesSans      = getCommandesSansExpedition($pdo);
$nbPreparation      = getExpeditionsParStatus($pdo, 'en_preparation');
$nbExpedie          = getExpeditionsParStatus($pdo, 'expedie');
$nbTransit          = getExpeditionsParStatus($pdo, 'en_transit');
$nbLivre            = getExpeditionsParStatus($pdo, 'livre');
$nbEchec            = getExpeditionsParStatus($pdo, 'echec');
$nbAujourdhui       = getExpeditionsAujourdhui($pdo);
$totalExpeditions   = count($expeditions);

// Helper : badge status
function statusBadge($status) {
    $map = [
        'en_preparation' => ['class' => 'badge-preparation', 'label' => '⏳ En préparation'],
        'expedie'        => ['class' => 'badge-expedie',     'label' => '📤 Expédié'],
        'en_transit'     => ['class' => 'badge-transit',     'label' => '🚚 En transit'],
        'livre'          => ['class' => 'badge-livre',       'label' => '✅ Livré'],
        'echec'          => ['class' => 'badge-echec',       'label' => '❌ Échec'],
    ];
    $s = $map[$status] ?? ['class' => 'badge-preparation', 'label' => $status];
    return '<span class="' . $s['class'] . '">' . $s['label'] . '</span>';
}

// Helper : steps pour tracking visuel
function getSteps($status) {
    $order  = ['en_preparation', 'expedie', 'en_transit', 'livre'];
    $current = array_search($status, $order);
    return ['order' => $order, 'current' => $current === false ? -1 : $current];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Expéditions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="style/exp_style.css" rel="stylesheet">
</head>
<body>
<div class="p-4">


    <!-- ==================== HEADER ==================== -->
    <div class="page-header">
        <div>
            <h4><i class="bi bi-truck me-2"></i>Gestion des Expéditions</h4>
            <small>Suivi et gestion des livraisons</small>
        </div>
        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-lg"></i> Nouvelle expédition
        </button>
    </div>


    <!-- ==================== STATS ==================== -->
    <div class="row g-3 mb-2">

        <div class="col-6 col-sm-4 col-xl-2">
            <div class="stat-card">
                <div class="stat-icon icon-dark"><i class="bi bi-box-seam"></i></div>
                <div class="stat-info">
                    <p>Total</p>
                    <h3><?php echo $totalExpeditions; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-xl-2">
            <div class="stat-card">
                <div class="stat-icon icon-dark"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-info">
                    <p>Préparation</p>
                    <h3><?php echo $nbPreparation; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-xl-2">
            <div class="stat-card">
                <div class="stat-icon icon-blue"><i class="bi bi-send"></i></div>
                <div class="stat-info">
                    <p>Expédiés</p>
                    <h3><?php echo $nbExpedie; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-xl-2">
            <div class="stat-card">
                <div class="stat-icon icon-orange"><i class="bi bi-truck"></i></div>
                <div class="stat-info">
                    <p>En transit</p>
                    <h3><?php echo $nbTransit; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-xl-2">
            <div class="stat-card">
                <div class="stat-icon icon-green"><i class="bi bi-check-circle"></i></div>
                <div class="stat-info">
                    <p>Livrés</p>
                    <h3><?php echo $nbLivre; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-xl-2">
            <div class="stat-card">
                <div class="stat-icon icon-red"><i class="bi bi-x-circle"></i></div>
                <div class="stat-info">
                    <p>Échecs</p>
                    <h3><?php echo $nbEchec; ?></h3>
                </div>
            </div>
        </div>

    </div>


    <!-- Alertes -->
    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success d-flex align-items-center gap-2 mt-3" style="border-radius:10px;font-size:14px;">
        <i class="bi bi-check-circle-fill"></i>
        <?php echo $_GET['success'] === 'add' ? 'Expédition créée avec succès !' : 'Expédition mise à jour !'; ?>
    </div>
    <?php endif; ?>


    <!-- ==================== TABLE ==================== -->
    <div class="table-card">

        <div class="table-card-header">
            <h6><i class="bi bi-list-ul me-2"></i>Liste des expéditions</h6>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" id="expeditionTable">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Commande</th>
                        <th>Client</th>
                        <th>Produit</th>
                        <th>Adresse</th>
                        <th>Transporteur</th>
                        <th>N° Suivi</th>
                        <th>Date expéd.</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

                    <?php if (!empty($expeditions)): ?>
                        <?php foreach ($expeditions as $e): ?>
                        <tr>
                            <td class="text-muted fw-semibold">#<?php echo $e['expedition_id']; ?></td>
                            <td class="fw-semibold">#<?php echo $e['commande_id']; ?></td>
                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars($e['client_nom']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($e['client_email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($e['produit_nom']); ?></td>
                            <td style="max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?php echo htmlspecialchars($e['adresse_livraison']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($e['transporteur'] ?? '—'); ?></td>
                            <td>
                                <?php if ($e['numero_suivi']): ?>
                                    <code style="font-size:12px;"><?php echo htmlspecialchars($e['numero_suivi']); ?></code>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted" style="white-space:nowrap;">
                                <?php echo $e['date_expedition'] ? date('d/m/Y H:i', strtotime($e['date_expedition'])) : '—'; ?>
                            </td>
                            <td><?php echo statusBadge($e['status_livraison']); ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn-edit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal"
                                        data-id="<?php echo $e['expedition_id']; ?>"
                                        data-client="<?php echo htmlspecialchars($e['client_nom']); ?>"
                                        data-produit="<?php echo htmlspecialchars($e['produit_nom']); ?>"
                                        data-total="<?php echo number_format($e['total'], 0, ',', ' '); ?>"
                                        data-adresse="<?php echo htmlspecialchars($e['adresse_livraison']); ?>"
                                        data-transporteur="<?php echo htmlspecialchars($e['transporteur'] ?? ''); ?>"
                                        data-suivi="<?php echo htmlspecialchars($e['numero_suivi'] ?? ''); ?>"
                                        data-status="<?php echo $e['status_livraison']; ?>"
                                        data-date-liv="<?php echo $e['date_livraison'] ?? ''; ?>">
                                        <i class="bi bi-pencil me-1"></i> Modifier
                                    </button>
                                    <a href="gestion_expedition.php?delete=<?php echo $e['expedition_id']; ?>"
                                       class="btn-delete"
                                       onclick="return confirm('Supprimer cette expédition ?')">
                                        <i class="bi bi-trash me-1"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">
                                    <i class="bi bi-truck"></i>
                                    <p>Aucune expédition enregistrée pour le moment.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>

    </div>


    <!-- ==================== MODAL NOUVELLE EXPÉDITION ==================== -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nouvelle Expédition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" action="gestion_expedition.php">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">

                        <!-- Sélection de la commande -->
                        <div class="mb-3">
                            <label class="form-label">Commande à expédier</label>
                            <select name="commande_id" id="selectCommande" class="form-select" required onchange="fillAdresse(this)">
                                <option value="">-- Sélectionner une commande --</option>
                                <?php foreach ($commandesSans as $cmd): ?>
                                <option value="<?php echo $cmd['commande_id']; ?>"
                                        data-adresse="<?php echo htmlspecialchars($cmd['client_adresse']); ?>"
                                        data-client="<?php echo htmlspecialchars($cmd['client_nom']); ?>"
                                        data-produit="<?php echo htmlspecialchars($cmd['produit_nom']); ?>"
                                        data-total="<?php echo number_format($cmd['total'], 0, ',', ' '); ?>">
                                    #<?php echo $cmd['commande_id']; ?> —
                                    <?php echo htmlspecialchars($cmd['client_nom']); ?> —
                                    <?php echo htmlspecialchars($cmd['produit_nom']); ?> —
                                    <?php echo number_format($cmd['total'], 0, ',', ' '); ?> DA
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($commandesSans)): ?>
                            <small class="text-muted">Toutes les commandes ont déjà une expédition.</small>
                            <?php endif; ?>
                        </div>

                        <!-- Aperçu commande sélectionnée -->
                        <div class="info-box" id="commandePreview" style="display:none;">
                            <div class="info-row">
                                <span class="info-label">Client</span>
                                <span class="info-value" id="previewClient"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Produit</span>
                                <span class="info-value" id="previewProduit"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Total</span>
                                <span class="info-value" id="previewTotal"></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Adresse de livraison</label>
                            <textarea name="adresse_livraison" id="adresseLivraison" class="form-control" rows="2" required placeholder="Adresse complète..."></textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Transporteur</label>
                                <input type="text" name="transporteur" class="form-control" placeholder="Ex: Yalidine, Zaki...">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Numéro de suivi</label>
                                <input type="text" name="numero_suivi" class="form-control" placeholder="Ex: YLD-1234567">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-truck me-1"></i> Créer l'expédition
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    <!-- ==================== MODAL MODIFIER ==================== -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Modifier l'expédition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" action="gestion_expedition.php">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="expedition_id" id="edit_expedition_id">

                    <div class="modal-body">

                        <!-- Tracking visuel -->
                        <div class="tracking-steps" id="trackingSteps"></div>

                        <!-- Infos commande -->
                        <div class="info-box mb-3">
                            <div class="info-row">
                                <span class="info-label">Client</span>
                                <span class="info-value" id="edit_client"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Produit</span>
                                <span class="info-value" id="edit_produit"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Total</span>
                                <span class="info-value" id="edit_total"></span>
                            </div>
                        </div>

                        <!-- Statut -->
                        <div class="mb-3">
                            <label class="form-label">Statut de livraison</label>
                            <select name="status_livraison" id="edit_status" class="form-select">
                                <option value="en_preparation">⏳ En préparation</option>
                                <option value="expedie">📤 Expédié</option>
                                <option value="en_transit">🚚 En transit</option>
                                <option value="livre">✅ Livré</option>
                                <option value="echec">❌ Échec livraison</option>
                            </select>
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Transporteur</label>
                                <input type="text" name="transporteur" id="edit_transporteur" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label">N° de suivi</label>
                                <input type="text" name="numero_suivi" id="edit_suivi" class="form-control">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Date de livraison effective</label>
                            <input type="datetime-local" name="date_livraison" id="edit_date_livraison" class="form-control">
                            <small class="text-muted">Remplir uniquement si livraison effectuée.</small>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-check-lg me-1"></i> Enregistrer
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


</div><!-- /p-4 -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

    // ============================================
    // Remplir adresse depuis commande sélectionnée
    // ============================================
    function fillAdresse(sel) {
        var opt     = sel.options[sel.selectedIndex];
        var preview = document.getElementById('commandePreview');

        if (!opt.value) {
            preview.style.display = 'none';
            document.getElementById('adresseLivraison').value = '';
            return;
        }

        document.getElementById('adresseLivraison').value = opt.getAttribute('data-adresse');
        document.getElementById('previewClient').textContent  = opt.getAttribute('data-client');
        document.getElementById('previewProduit').textContent = opt.getAttribute('data-produit');
        document.getElementById('previewTotal').textContent   = opt.getAttribute('data-total') + ' DA';
        preview.style.display = 'block';
    }

    // ============================================
    // Remplir modal Modifier
    // ============================================
    document.getElementById('editModal').addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;

        document.getElementById('edit_expedition_id').value  = btn.getAttribute('data-id');
        document.getElementById('edit_client').textContent   = btn.getAttribute('data-client');
        document.getElementById('edit_produit').textContent  = btn.getAttribute('data-produit');
        document.getElementById('edit_total').textContent    = btn.getAttribute('data-total') + ' DA';
        document.getElementById('edit_status').value         = btn.getAttribute('data-status');
        document.getElementById('edit_transporteur').value   = btn.getAttribute('data-transporteur');
        document.getElementById('edit_suivi').value          = btn.getAttribute('data-suivi');
        document.getElementById('edit_date_livraison').value = btn.getAttribute('data-date-liv');

        // Tracking steps
        renderTrackingSteps(btn.getAttribute('data-status'));
    });

    // ============================================
    // Tracking steps visuel
    // ============================================
    var stepsConfig = [
        { key: 'en_preparation', label: 'Préparation', icon: '⏳' },
        { key: 'expedie',        label: 'Expédié',     icon: '📤' },
        { key: 'en_transit',     label: 'En transit',  icon: '🚚' },
        { key: 'livre',          label: 'Livré',       icon: '✅' }
    ];

    function renderTrackingSteps(currentStatus) {
        var order   = ['en_preparation', 'expedie', 'en_transit', 'livre'];
        var current = order.indexOf(currentStatus);
        var html    = '';

        stepsConfig.forEach(function(step, idx) {
            var cls = '';
            if (idx < current)  cls = 'done';
            if (idx === current) cls = 'active';

            html += '<div class="step ' + cls + '">'
                  +   '<div class="step-icon">' + step.icon + '</div>'
                  +   '<div class="step-label">' + step.label + '</div>'
                  + '</div>';
        });

        document.getElementById('trackingSteps').innerHTML = html;
    }

    // ============================================
    // Recherche en temps réel
    // ============================================
    document.getElementById('searchInput').addEventListener('keyup', function () {
        var filter = this.value.toLowerCase();
        document.querySelectorAll('#expeditionTable tbody tr').forEach(function (row) {
            row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
        });
    });

</script>
</body>
</html>