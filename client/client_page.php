<?php
session_start();

// التحقق من الجلسة
if (!isset($_SESSION['idClient'])) {
    header('Location: index.php');
    exit();
}

include 'func_client.php';
include '../connect_pdo.php';

$client_id  = $_SESSION['idClient'];
$client_nom = $_SESSION['nomClient'] ?? ('Client #' . $client_id);

// ============================================
// تأكيد الطلب (POST من السلة)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm_cart') {
    $produits  = $_POST['produit_id'];
    $quantites = $_POST['quantite'];
    $prix      = $_POST['prix_unitaire'];

    for ($i = 0; $i < count($produits); $i++) {
        addCommande($pdo, $client_id, $produits[$i], $quantites[$i], $prix[$i]);
    }
    header('Location: client_shop.php?success=1');
    exit();
}

// ============================================
// جلب المنتجات من قاعدة البيانات
// ============================================
$products = getAllProducts($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="client_page_style.css" rel="stylesheet">
</head>
<body>

<!-- ==================== NAVBAR ==================== -->
<div class="topbar">
    <a class="topbar-logo" href="#">
        <i class="bi bi-bag-fill"></i> MyShop
    </a>
    <a href="index.php" class="btn-logout">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>


<!-- ==================== MAIN ==================== -->
<div class="main-layout">

    <!-- ====== PRODUITS ====== -->
    <div class="products-section">

        <div class="section-header">
            <h5>Nos Produits</h5>
            <span class="products-count"><?php echo count($products); ?> produit(s)</span>
        </div>

        <!-- Barre de recherche -->
        <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" placeholder="Rechercher un produit...">
        </div>

        <!-- Toast succès -->
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4 py-2" style="border-radius:10px;font-size:14px;">
            <i class="bi bi-check-circle-fill"></i> Commande passée avec succès !
        </div>
        <?php endif; ?>

        <!-- Grille des produits -->
        <?php if (!empty($products)): ?>
        <div class="products-grid" id="productsGrid">

            <?php foreach ($products as $p): ?>

            <?php
                // Emoji selon catégorie (à adapter selon vos catégories)
                $emojis = ['📦','📱','💻','🎧','⌨️','🖥️','🖱️','👟','👕','📷'];
                $emoji  = $emojis[($p['produit_id'] - 1) % count($emojis)];

                // Nom encodé pour JS
                $nom_js  = addslashes(htmlspecialchars($p['nom'], ENT_QUOTES));
                $desc_js = addslashes(htmlspecialchars($p['description'] ?? '', ENT_QUOTES));
            ?>

            <div class="product-card product-col"
                 onclick="openModal(
                     <?php echo $p['produit_id']; ?>,
                     '<?php echo $nom_js; ?>',
                     '<?php echo $emoji; ?>',
                     <?php echo $p['prix']; ?>,
                     <?php echo $p['stock']; ?>,
                     '<?php echo $desc_js; ?>'
                 )">

                <div class="product-img"><?php echo $emoji; ?></div>

                <div class="product-body">
                    <div class="product-name"><?php echo htmlspecialchars($p['nom']); ?></div>
                    <div class="product-desc"><?php echo htmlspecialchars($p['description'] ?? 'Aucune description.'); ?></div>

                    <div class="product-footer">
                        <div class="product-price"><?php echo number_format($p['prix'], 0, ',', ' '); ?> DA</div>
                        <?php if ($p['stock'] <= 5): ?>
                            <span class="stock-low">Stock: <?php echo $p['stock']; ?></span>
                        <?php else: ?>
                            <span class="stock-ok">En stock</span>
                        <?php endif; ?>
                    </div>

                    <button class="btn-add"
                            onclick="event.stopPropagation();
                                     addToCart(<?php echo $p['produit_id']; ?>,
                                               '<?php echo $nom_js; ?>',
                                               <?php echo $p['prix']; ?>,
                                               '<?php echo $emoji; ?>')">
                        <i class="bi bi-cart-plus"></i> Ajouter
                    </button>
                </div>

            </div>
            <?php endforeach; ?>

        </div>

        <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-box-seam"></i>
            <p>Aucun produit disponible pour le moment.</p>
        </div>
        <?php endif; ?>

    </div>


    <!-- ====== CART PANEL ====== -->
    <div class="cart-panel">

        <div class="cart-header">
            <div class="cart-title">
                <i class="bi bi-cart3"></i> Panier
                <span class="cart-badge" id="cartCount">0</span>
            </div>
        </div>

        <div class="cart-body" id="cartBody">
            <div class="cart-empty">
                <i class="bi bi-cart-x"></i>
                <p>Votre panier est vide</p>
            </div>
        </div>

        <div class="cart-footer">
            <div class="total-row">
                <span class="total-label">Total</span>
                <span class="total-val" id="cartTotal">0 DA</span>
            </div>
            <form method="POST" action="client_shop.php" id="cartForm">
                <input type="hidden" name="action" value="confirm_cart">
                <div id="cartFormFields"></div>
                <button type="submit" class="btn-order" id="btnOrder" disabled>
                    <i class="bi bi-check-lg"></i> Commander
                </button>
            </form>
        </div>

    </div>

</div>


<!-- ==================== MODAL DÉTAIL PRODUIT ==================== -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-product-img" id="modalEmoji">📦</div>

            <div class="modal-body p-4">

                <div class="d-flex justify-content-between align-items-start mb-1">
                    <h5 class="fw-bold mb-0" id="modalName"></h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="mb-3" id="modalStock"></div>

                <p class="text-muted mb-3" style="font-size:14px;" id="modalDesc"></p>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <span class="modal-price" id="modalPrice"></span>
                    <div class="modal-qty-wrap">
                        <button class="modal-qty-btn" onclick="modalQty(-1)">−</button>
                        <span class="modal-qty-val" id="modalQtyVal">1</span>
                        <button class="modal-qty-btn" onclick="modalQty(1)">+</button>
                    </div>
                </div>

                <button class="btn-modal-add" onclick="addFromModal()">
                    <i class="bi bi-cart-plus me-2"></i> Ajouter au panier
                </button>

            </div>
        </div>
    </div>
</div>


<!-- Toast container -->
<div class="toast-wrap" id="toastWrap"></div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

/* ─────────────────────────────
   CART
───────────────────────────── */
var cart = [];

function addToCart(id, nom, prix, emoji) {
    var ex = cart.find(function(i){ return i.id === id; });
    if (ex) { ex.qty++; }
    else     { cart.push({ id:id, nom:nom, prix:prix, emoji:emoji, qty:1 }); }
    renderCart();
    showToast(emoji + ' ' + nom + ' ajouté !');
}

function changeQty(id, delta) {
    var item = cart.find(function(i){ return i.id === id; });
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) cart = cart.filter(function(i){ return i.id !== id; });
    renderCart();
}

function renderCart() {
    var body       = document.getElementById('cartBody');
    var countEl    = document.getElementById('cartCount');
    var totalEl    = document.getElementById('cartTotal');
    var formFields = document.getElementById('cartFormFields');
    var btnOrder   = document.getElementById('btnOrder');

    var totalQty    = cart.reduce(function(s,i){ return s + i.qty; }, 0);
    var totalAmount = cart.reduce(function(s,i){ return s + i.qty * i.prix; }, 0);

    countEl.textContent = totalQty;
    totalEl.textContent = totalAmount.toLocaleString('fr-DZ') + ' DA';

    if (cart.length === 0) {
        body.innerHTML = '<div class="cart-empty"><i class="bi bi-cart-x"></i><p>Votre panier est vide</p></div>';
        formFields.innerHTML = '';
        btnOrder.disabled = true;
        return;
    }

    var html     = '';
    var formHtml = '';

    cart.forEach(function(item) {
        var sub = (item.prix * item.qty).toLocaleString('fr-DZ');
        html += '<div class="cart-item">'
              +   '<span class="ci-emoji">' + item.emoji + '</span>'
              +   '<div class="ci-info">'
              +     '<div class="ci-name">'  + item.nom + '</div>'
              +     '<div class="ci-price">' + sub + ' DA</div>'
              +   '</div>'
              +   '<div class="qty-controls">'
              +     '<button class="qty-btn minus" onclick="changeQty(' + item.id + ',-1)">−</button>'
              +     '<span class="qty-val">' + item.qty + '</span>'
              +     '<button class="qty-btn" onclick="changeQty(' + item.id + ',1)">+</button>'
              +   '</div>'
              + '</div>';

        formHtml += '<input type="hidden" name="produit_id[]"    value="' + item.id   + '">'
                  + '<input type="hidden" name="quantite[]"      value="' + item.qty  + '">'
                  + '<input type="hidden" name="prix_unitaire[]" value="' + item.prix + '">';
    });

    body.innerHTML   = html;
    formFields.innerHTML = formHtml;
    btnOrder.disabled    = false;
}

/* ─────────────────────────────
   MODAL
───────────────────────────── */
var _mId=0, _mNom='', _mPrix=0, _mEmoji='', _mQty=1;

function openModal(id, nom, emoji, prix, stock, desc) {
    _mId=id; _mNom=nom; _mPrix=prix; _mEmoji=emoji; _mQty=1;

    document.getElementById('modalEmoji').textContent  = emoji;
    document.getElementById('modalName').textContent   = nom;
    document.getElementById('modalDesc').textContent   = desc || 'Aucune description.';
    document.getElementById('modalQtyVal').textContent = 1;
    document.getElementById('modalPrice').textContent  = prix.toLocaleString('fr-DZ') + ' DA';

    var stockEl = document.getElementById('modalStock');
    if (stock <= 5) {
        stockEl.className = 'modal-stock-low';
        stockEl.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>Stock limité: ' + stock;
    } else {
        stockEl.className = 'modal-stock-ok';
        stockEl.innerHTML = '<i class="bi bi-check-circle me-1"></i>En stock (' + stock + ')';
    }

    new bootstrap.Modal(document.getElementById('productModal')).show();
}

function modalQty(delta) {
    _mQty = Math.max(1, _mQty + delta);
    document.getElementById('modalQtyVal').textContent = _mQty;
}

function addFromModal() {
    var ex = cart.find(function(i){ return i.id === _mId; });
    if (ex) { ex.qty += _mQty; }
    else     { cart.push({ id:_mId, nom:_mNom, prix:_mPrix, emoji:_mEmoji, qty:_mQty }); }
    renderCart();
    bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
    showToast(_mEmoji + ' ' + _mNom + ' ajouté !');
}

/* ─────────────────────────────
   SEARCH
───────────────────────────── */
document.getElementById('searchInput').addEventListener('keyup', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('.product-col').forEach(function(col) {
        col.style.display = col.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

/* ─────────────────────────────
   TOAST
───────────────────────────── */
function showToast(msg) {
    var wrap = document.getElementById('toastWrap');
    var t = document.createElement('div');
    t.className = 'toast-item';
    t.innerHTML = '<i class="bi bi-cart-check"></i>' + msg;
    wrap.appendChild(t);
    setTimeout(function(){ t.remove(); }, 2500);
}

</script>
</body>
</html>