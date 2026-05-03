<?php
include 'function/func_client_page.php';
include '../connect_pdo.php';

// ============================================
// جلب عدد الكلاينت الكلي
// ============================================
$numberClient = getNumberClient($pdo);

// ============================================
// جلب قائمة كل الكلاينت
// ============================================
$clients = getAllClients($pdo); // TODO: تعريف هذه الدالة في func_client_page.php

// ============================================
// حذف كلاينت
// ============================================
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
     deleteClient($pdo, $id);
    header('Location: gestion_client.php');
    exit();
}

// ============================================
// تعديل كلاينت (POST)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id      = $_POST['edit_id'];
    $nom     = $_POST['nom'];
    $adresse = $_POST['adresse'];
    $email   = $_POST['email'];
 updateClient($pdo, $id, $nom, $adresse, $email);
    header('Location: gestion_client.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Clients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="gestion_client_style.css" rel="stylesheet">
</head>
<body>
<div class="p-4">


    <!-- ==================== HEADER ==================== -->
    <div class="page-header">
        <div>
            <h4><i class="bi bi-people-fill me-2 text-primary"></i>Gestion des Clients</h4>
            <small>Liste complète de tous les clients enregistrés</small>
        </div>
    </div>


    <!-- ==================== STAT ==================== -->
    <div class="stat-card">
        <div class="stat-icon">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="stat-info">
            <p>Total Clients</p>
            <h3><?php echo $numberClient; ?></h3>
        </div>
    </div>


    <!-- ==================== TABLE ==================== -->
    <div class="table-card">

        <div class="table-card-header">
            <h6><i class="bi bi-list-ul me-2 text-primary"></i>Liste des clients</h6>
            <!-- Recherche -->
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher un client...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" id="clientTable">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Client</th>
                        <th>Email</th>
                        <th>Adresse</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

                    <?php if (!empty($clients)): ?>
                        <?php foreach ($clients as $client): ?>
                        <tr>
                            <td class="text-muted fw-semibold">#<?php echo $client['client_id']; ?></td>

                            <!-- Nom avec avatar -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="client-avatar">
                                        <?php echo strtoupper(substr($client['nom'], 0, 2)); ?>
                                    </div>
                                    <span class="fw-semibold"><?php echo htmlspecialchars($client['nom']); ?></span>
                                </div>
                            </td>

                            <td><?php echo htmlspecialchars($client['email']); ?></td>
                            <td><?php echo htmlspecialchars($client['adresse']); ?></td>

                            <!-- Boutons actions -->
                            <td>
                                <div class="d-flex gap-2">

                                    <!-- Bouton Modifier → ouvre modal -->
                                    <button class="btn-edit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal"
                                        data-id="<?php echo $client['client_id']; ?>"
                                        data-nom="<?php echo htmlspecialchars($client['nom']); ?>"
                                        data-email="<?php echo htmlspecialchars($client['email']); ?>"
                                        data-adresse="<?php echo htmlspecialchars($client['adresse']); ?>">
                                        <i class="bi bi-pencil me-1"></i> Modifier
                                    </button>

                                    <!-- Bouton Supprimer -->
                                    <a href="gestion_client.php?delete=<?php echo $client['client_id']; ?>"
                                       class="btn-delete"
                                       onclick="return confirm('Supprimer ce client ?')">
                                        <i class="bi bi-trash me-1"></i> Supprimer
                                    </a>

                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <!-- Aucun client -->
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-person-x"></i>
                                    <p>Aucun client enregistré pour le moment.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>

    </div><!-- /table-card -->


    <!-- ==================== MODAL MODIFIER ==================== -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>Modifier le client
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" action="gestion_client.php">
                    <div class="modal-body">

                        <!-- Champ caché ID -->
                        <input type="hidden" name="edit_id" id="edit_id">

                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" name="nom" id="edit_nom" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="adresse" id="edit_adresse" class="form-control" required>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
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
    // Remplir le modal avec les données du client
    // ============================================
    document.getElementById('editModal').addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        document.getElementById('edit_id').value      = btn.getAttribute('data-id');
        document.getElementById('edit_nom').value     = btn.getAttribute('data-nom');
        document.getElementById('edit_email').value   = btn.getAttribute('data-email');
        document.getElementById('edit_adresse').value = btn.getAttribute('data-adresse');
    });

    // ============================================
    // Recherche en temps réel dans le tableau
    // ============================================
    document.getElementById('searchInput').addEventListener('keyup', function () {
        var filter = this.value.toLowerCase();
        var rows   = document.querySelectorAll('#clientTable tbody tr');

        rows.forEach(function (row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

</script>
</body>
</html>