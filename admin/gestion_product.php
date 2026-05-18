<?php
include 'function/func_product_page.php';
include '../connect_pdo.php';

// ============================================
// إضافة منتج
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    addProduct(
        $pdo,
        $_POST['nom'],
        $_POST['description'],
        $_POST['prix'],
        $_POST['categorie'],
        $_POST['stock']
    );
    header('Location: gestion_product.php');
    exit();
}

// ============================================
// تعديل منتج
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    updateProduct(
        $pdo,
        $_POST['edit_id'],
        $_POST['nom'],
        $_POST['description'],
        $_POST['prix'],
        $_POST['categorie'],
        $_POST['stock']
    );
    header('Location: gestion_product.php');
    exit();
}

// ============================================
// حذف منتج
// ============================================
if (isset($_GET['delete'])) {
    deleteProduct($pdo, $_GET['delete']);
    header('Location: gestion_product.php');
    exit();
}

// ============================================
// جلب البيانات
// ============================================
$products     = getAllProducts($pdo);
$totalProd    = getNumberProducts($pdo);
$inStock      = getNumberInStock($pdo);
$outOfStock   = getNumberOutOfStock($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Produits</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="style/gestion_product_style.css" rel="stylesheet">
</head>
<body>
<div class="p-4">


    <!-- ==================== HEADER ==================== -->
    <div class="page-header">
        <div>
            <h4><i class="bi bi-box-seam-fill me-2 text-primary"></i>Gestion des Produits</h4>
            <small>Liste complète de tous les produits enregistrés</small>
        </div>
        <!-- Bouton Ajouter -->
        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-lg"></i> Ajouter un produit
        </button>
    </div>


    <!-- ==================== STATS ==================== -->
    <div class="row g-3 mb-2">

        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon icon-blue"><i class="bi bi-box-seam-fill"></i></div>
                <div class="stat-info">
                    <p>Total Produits</p>
                    <h3><?php echo $totalProd; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon icon-green"><i class="bi bi-check-circle-fill"></i></div>
                <div class="stat-info">
                    <p>En Stock</p>
                    <h3><?php echo $inStock; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon icon-red"><i class="bi bi-x-circle-fill"></i></div>
                <div class="stat-info">
                    <p>Stock Epuise</p>
                    <h3><?php echo $outOfStock; ?></h3>
                </div>
            </div>
        </div>

    </div>


    <!-- ==================== TABLE ==================== -->
    <div class="table-card">

        <div class="table-card-header">
            <h6><i class="bi bi-list-ul me-2 text-primary"></i>Liste des produits</h6>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher un produit...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" id="productTable">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Prix</th>
                        <th>Categorie</th>
                        <th>Stock</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $p): ?>
                        <tr>
                            <td class="text-muted fw-semibold">#<?php echo $p['produit_id']; ?></td>
                            <td class="fw-semibold"><?php echo htmlspecialchars($p['nom']); ?></td>
                            <td class="text-muted" style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                <?php echo htmlspecialchars($p['description'] ?? '—'); ?>
                            </td>
                            <td class="fw-bold"><?php echo number_format($p['prix'], 0, ',', ' '); ?> DA</td>
                            <td><?php echo $p['categorie']; ?></td>
                            <td class="fw-semibold"><?php echo $p['stock']; ?></td>

                            <!-- Statut stock -->
                            <td>
                                <?php if ($p['stock'] > 0): ?>
                                    <span class="badge-in-stock"><i class="bi bi-circle-fill me-1" style="font-size:7px;"></i>En stock</span>
                                <?php else: ?>
                                    <span class="badge-out-stock"><i class="bi bi-circle-fill me-1" style="font-size:7px;"></i>Epuise</span>
                                <?php endif; ?>
                            </td>

                            <!-- Actions -->
                            <td>
                                <div class="d-flex gap-2">

                                    <!-- Modifier -->
                                    <button class="btn-edit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal"
                                        data-id="<?php echo $p['produit_id']; ?>"
                                        data-nom="<?php echo htmlspecialchars($p['nom']); ?>"
                                        data-description="<?php echo htmlspecialchars($p['description'] ?? ''); ?>"
                                        data-prix="<?php echo $p['prix']; ?>"
                                        data-categorie="<?php echo $p['categorie']; ?>"
                                        data-stock="<?php echo $p['stock']; ?>">
                                        <i class="bi bi-pencil me-1"></i> Modifier
                                    </button>

                                    <!-- Supprimer -->
                                    <a href="gestion_product.php?delete=<?php echo $p['produit_id']; ?>"
                                       class="btn-delete"
                                       onclick="return confirm('Supprimer ce produit ?')">
                                        <i class="bi bi-trash me-1"></i> Supprimer
                                    </a>

                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="bi bi-box-seam"></i>
                                    <p>Aucun produit enregistré pour le moment.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>

    </div><!-- /table-card -->


    <!-- ==================== MODAL AJOUTER ==================== -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2 text-primary"></i>Ajouter un produit
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" action="gestion_product.php">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Nom du produit</label>
                            <input type="text" name="nom" class="form-control" placeholder="Ex: Laptop Pro X" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Description du produit..."></textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Prix (DA)</label>
                                <input type="number" name="prix" class="form-control" placeholder="0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Categorie</label>
                                <input type="number" name="categorie" class="form-control" placeholder="1" required>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" class="form-control" placeholder="0" required>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Ajouter
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
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>Modifier le produit
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" action="gestion_product.php">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Nom du produit</label>
                            <input type="text" name="nom" id="edit_nom" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Prix (DA)</label>
                                <input type="number" name="prix" id="edit_prix" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Categorie</label>
                                <input type="number" name="categorie" id="edit_categorie" class="form-control" required>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" id="edit_stock" class="form-control" required>
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
    // Remplir le modal Modifier avec les données
    // ============================================
    document.getElementById('editModal').addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        document.getElementById('edit_id').value          = btn.getAttribute('data-id');
        document.getElementById('edit_nom').value         = btn.getAttribute('data-nom');
        document.getElementById('edit_description').value = btn.getAttribute('data-description');
        document.getElementById('edit_prix').value        = btn.getAttribute('data-prix');
        document.getElementById('edit_categorie').value   = btn.getAttribute('data-categorie');
        document.getElementById('edit_stock').value       = btn.getAttribute('data-stock');
    });

    // ============================================
    // Recherche en temps réel
    // ============================================
    document.getElementById('searchInput').addEventListener('keyup', function () {
        var filter = this.value.toLowerCase();
        var rows   = document.querySelectorAll('#productTable tbody tr');
        rows.forEach(function (row) {
            row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
        });
    });

</script>
</body>
</html>