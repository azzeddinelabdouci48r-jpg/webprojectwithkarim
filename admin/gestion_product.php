<?php
include 'function/func_product_page.php';
include '../connect_pdo.php';

// ============================================
// إضافة منتج
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $tags_array = isset($_POST['tags']) ? explode(',', $_POST['tags']) : [];
    addProduct(
        $pdo,
        $_POST['nom'],
        $_POST['description'],
        $_POST['prix'],
        $_POST['categorie_nom'],
        $_POST['stock'],
        $tags_array
    );
    header('Location: gestion_product.php');
    exit();
}

// ============================================
// تعديل منتج
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $tags_array = isset($_POST['tags']) ? explode(',', $_POST['tags']) : [];
    updateProduct(
        $pdo,
        $_POST['edit_id'],
        $_POST['nom'],
        $_POST['description'],
        $_POST['prix'],
        $_POST['categorie_nom'],
        $_POST['stock'],
        $tags_array
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
$products   = getAllProducts($pdo);
$totalProd  = getNumberProducts($pdo);
$inStock    = getNumberInStock($pdo);
$outOfStock = getNumberOutOfStock($pdo);
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
            <h4><i class="bi bi-box-seam-fill me-2"></i>Gestion des Produits</h4>
            <small>Liste complète de tous les produits</small>
        </div>
        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-lg"></i> Ajouter un produit
        </button>
    </div>


    <!-- ==================== STATS ==================== -->
    <div class="row g-3 mb-2">

        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon icon-dark"><i class="bi bi-box-seam-fill"></i></div>
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
                    <p>Stock Epuisé</p>
                    <h3><?php echo $outOfStock; ?></h3>
                </div>
            </div>
        </div>

    </div>


    <!-- ==================== TABLE ==================== -->
    <div class="table-card">

        <div class="table-card-header">
            <h6><i class="bi bi-list-ul me-2"></i>Liste des produits</h6>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" id="productTable">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Tags SEO</th>
                        <th>Prix</th>
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

                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars($p['nom']); ?></div>
                                <small class="text-muted" style="font-size:11px;">
                                    <?php echo htmlspecialchars(substr($p['description'] ?? '', 0, 40)); ?>
                                    <?php if (strlen($p['description'] ?? '') > 40) echo '...'; ?>
                                </small>
                            </td>

                            <td>
                                <?php if ($p['categorie_nom']): ?>
                                    <span class="categorie-badge"><?php echo htmlspecialchars($p['categorie_nom']); ?></span>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($p['tags']): ?>
                                    <?php foreach (explode(', ', $p['tags']) as $tag): ?>
                                        <span class="tag-item"><?php echo htmlspecialchars($tag); ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>

                            <td class="fw-bold"><?php echo number_format($p['prix'], 0, ',', ' '); ?> DA</td>
                            <td class="fw-semibold"><?php echo $p['stock']; ?></td>

                            <td>
                                <?php if ($p['stock'] > 0): ?>
                                    <span class="badge-in-stock">En stock</span>
                                <?php else: ?>
                                    <span class="badge-out-stock">Epuisé</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn-edit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal"
                                        data-id="<?php echo $p['produit_id']; ?>"
                                        data-nom="<?php echo htmlspecialchars($p['nom']); ?>"
                                        data-description="<?php echo htmlspecialchars($p['description'] ?? ''); ?>"
                                        data-prix="<?php echo $p['prix']; ?>"
                                        data-categorie="<?php echo htmlspecialchars($p['categorie_nom'] ?? ''); ?>"
                                        data-stock="<?php echo $p['stock']; ?>"
                                        data-tags="<?php echo htmlspecialchars($p['tags'] ?? ''); ?>">
                                        <i class="bi bi-pencil me-1"></i> Modifier
                                    </button>
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

    </div>


    <!-- ==================== MODAL AJOUTER ==================== -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Ajouter un produit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" action="gestion_product.php" id="addForm">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="tags"          id="add_tags_hidden">
                    <input type="hidden" name="categorie_nom" id="add_categorie_hidden">

                    <div class="modal-body">
                        <div class="row g-3">

                            <!-- Colonne gauche : infos produit -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nom du produit</label>
                                    <input type="text" id="add_nom" class="form-control" placeholder="Ex: Laptop Pro X" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea id="add_description" class="form-control" rows="3" placeholder="Description..."></textarea>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label">Prix (DA)</label>
                                        <input type="number" name="prix" class="form-control" placeholder="0" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Stock</label>
                                        <input type="number" name="stock" class="form-control" placeholder="0" required>
                                    </div>
                                </div>

                                <!-- Champs cachés pour nom et description (pour le POST) -->
                                <input type="hidden" name="nom"         id="add_nom_hidden">
                                <input type="hidden" name="description" id="add_desc_hidden">
                            </div>

                            <!-- Colonne droite : AI -->
                            <div class="col-md-6">
                                <div class="ai-section">
                                    <div class="ai-section-title">
                                        <i class="bi bi-stars"></i> AI Auto-Categorizer & SEO Tags
                                    </div>
                                    <p style="font-size:12px;color:#868e96;margin-bottom:12px;">
                                        Remplissez le nom et la description, puis cliquez pour que l'IA génère automatiquement la catégorie et les mots-clés SEO.
                                    </p>
                                    <button type="button" class="btn-ai" id="btnAnalyzeAdd" onclick="analyzeProduct('add')">
                                        <i class="bi bi-stars"></i> Analyser avec l'IA
                                    </button>

                                    <div class="ai-result" id="aiResultAdd">
                                        <div class="mb-3">
                                            <div class="ai-result-label">Catégorie suggérée</div>
                                            <div id="aiCategorieAdd" class="categorie-badge" style="font-size:14px;padding:6px 12px;"></div>
                                        </div>
                                        <div>
                                            <div class="ai-result-label">Tags SEO</div>
                                            <div class="tags-preview" id="aiTagsAdd"></div>
                                        </div>
                                        <div class="mt-3" style="font-size:12px;color:#2f9e44;">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Ces valeurs seront enregistrées automatiquement.
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark" onclick="prepareForm('add')">
                            <i class="bi bi-plus-lg me-1"></i> Ajouter
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    <!-- ==================== MODAL MODIFIER ==================== -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Modifier le produit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" action="gestion_product.php" id="editForm">
                    <input type="hidden" name="action"        value="edit">
                    <input type="hidden" name="edit_id"       id="edit_id">
                    <input type="hidden" name="tags"          id="edit_tags_hidden">
                    <input type="hidden" name="categorie_nom" id="edit_categorie_hidden">
                    <input type="hidden" name="nom"           id="edit_nom_hidden">
                    <input type="hidden" name="description"   id="edit_desc_hidden">

                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nom du produit</label>
                                    <input type="text" id="edit_nom" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea id="edit_description" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label">Prix (DA)</label>
                                        <input type="number" name="prix" id="edit_prix" class="form-control" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Stock</label>
                                        <input type="number" name="stock" id="edit_stock" class="form-control" required>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label">Catégorie actuelle</label>
                                    <input type="text" id="edit_categorie_display" class="form-control" readonly
                                           style="background:#f8f9fa;color:#868e96;">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="ai-section">
                                    <div class="ai-section-title">
                                        <i class="bi bi-stars"></i> AI Auto-Categorizer & SEO Tags
                                    </div>
                                    <p style="font-size:12px;color:#868e96;margin-bottom:12px;">
                                        Relancez l'IA pour mettre à jour la catégorie et les tags SEO.
                                    </p>
                                    <button type="button" class="btn-ai" id="btnAnalyzeEdit" onclick="analyzeProduct('edit')">
                                        <i class="bi bi-stars"></i> Ré-analyser avec l'IA
                                    </button>

                                    <div class="ai-result" id="aiResultEdit">
                                        <div class="mb-3">
                                            <div class="ai-result-label">Catégorie suggérée</div>
                                            <div id="aiCategorieEdit" class="categorie-badge" style="font-size:14px;padding:6px 12px;"></div>
                                        </div>
                                        <div>
                                            <div class="ai-result-label">Tags SEO</div>
                                            <div class="tags-preview" id="aiTagsEdit"></div>
                                        </div>
                                        <div class="mt-3" style="font-size:12px;color:#2f9e44;">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Ces valeurs seront enregistrées automatiquement.
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark" onclick="prepareForm('edit')">
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

/* ─────────────────────────────
   Remplir modal Modifier
───────────────────────────── */
document.getElementById('editModal').addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;

    document.getElementById('edit_id').value                 = btn.getAttribute('data-id');
    document.getElementById('edit_nom').value                = btn.getAttribute('data-nom');
    document.getElementById('edit_description').value        = btn.getAttribute('data-description');
    document.getElementById('edit_prix').value               = btn.getAttribute('data-prix');
    document.getElementById('edit_stock').value              = btn.getAttribute('data-stock');
    document.getElementById('edit_categorie_display').value  = btn.getAttribute('data-categorie');

    // pré-remplir les hidden avec les valeurs existantes
    document.getElementById('edit_categorie_hidden').value   = btn.getAttribute('data-categorie');
    document.getElementById('edit_tags_hidden').value        = btn.getAttribute('data-tags');

    // reset AI result
    document.getElementById('aiResultEdit').style.display = 'none';
});

/* ─────────────────────────────
   Préparer le form avant submit
   (copier nom/desc dans hidden)
───────────────────────────── */
function prepareForm(mode) {
    if (mode === 'add') {
        document.getElementById('add_nom_hidden').value  = document.getElementById('add_nom').value;
        document.getElementById('add_desc_hidden').value = document.getElementById('add_description').value;
    } else {
        document.getElementById('edit_nom_hidden').value  = document.getElementById('edit_nom').value;
        document.getElementById('edit_desc_hidden').value = document.getElementById('edit_description').value;
    }
    return true;
}

/* ─────────────────────────────
   AI Analyze (appel Anthropic API)
───────────────────────────── */
async function analyzeProduct(mode) {
    var nom  = document.getElementById(mode === 'add' ? 'add_nom' : 'edit_nom').value.trim();
    var desc = document.getElementById(mode === 'add' ? 'add_description' : 'edit_description').value.trim();

    if (!nom) {
        alert('Veuillez remplir au moins le nom du produit.');
        return;
    }

    var btn = document.getElementById(mode === 'add' ? 'btnAnalyzeAdd' : 'btnAnalyzeEdit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Analyse en cours...';

    try {
        // ── Appel au fichier PHP proxy (sécurisé) ──
        var response = await fetch('ai_analyze.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nom: nom, desc: desc })
        });

        var result = await response.json();

        if (result.error) {
            alert('Erreur IA: ' + result.error);
            throw new Error(result.error);
        }

        // Afficher catégorie
        document.getElementById('aiCategorie' + (mode === 'add' ? 'Add' : 'Edit')).textContent = result.categorie;

        // Afficher tags
        var tagsDiv = document.getElementById('aiTags' + (mode === 'add' ? 'Add' : 'Edit'));
        tagsDiv.innerHTML = '';
        result.tags.forEach(function(tag) {
            var span = document.createElement('span');
            span.className = 'tag-preview-item';
            span.textContent = tag;
            tagsDiv.appendChild(span);
        });

        // Stocker dans les hidden fields
        document.getElementById(mode + '_categorie_hidden').value = result.categorie;
        document.getElementById(mode + '_tags_hidden').value      = result.tags.join(',');

        // Afficher la section résultat
        document.getElementById('aiResult' + (mode === 'add' ? 'Add' : 'Edit')).style.display = 'block';

    } catch (err) {
        alert('Erreur lors de l\'analyse IA. Vérifiez votre connexion.');
        console.error(err);
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-stars"></i> Ré-analyser avec l\'IA';
}

/* ─────────────────────────────
   Recherche en temps réel
───────────────────────────── */
document.getElementById('searchInput').addEventListener('keyup', function () {
    var filter = this.value.toLowerCase();
    document.querySelectorAll('#productTable tbody tr').forEach(function (row) {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});

</script>
</body>
</html>