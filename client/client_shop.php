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
    try {
        if (!isset($_POST['produit_id']) || !is_array($_POST['produit_id'])) {
            throw new Exception("Aucun produit dans le panier");
        }

        $produits  = $_POST['produit_id'];
        $quantites = $_POST['quantite'];
        $prix      = $_POST['prix_unitaire'];

        $pdo->beginTransaction();
        $commandeCount = 0;

        for ($i = 0; $i < count($produits); $i++) {
            if (empty($produits[$i]) || !is_numeric($quantites[$i]) || $quantites[$i] <= 0) {
                continue;
            }
            $result = addCommande($pdo, $client_id, $produits[$i], $quantites[$i], $prix[$i]);
            if ($result === false) {
                throw new Exception("Erreur lors de l'ajout de la commande");
            }
            $commandeCount++;
        }

        $pdo->commit();

        if ($commandeCount > 0) {
            header('Location: client_shop.php?success=1');
            exit();
        } else {
            header('Location: client_shop.php?error=empty_cart');
            exit();
        }

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header('Location: client_shop.php?error=' . urlencode($e->getMessage()));
        exit();
    }
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
    <title>MyShop - Boutique en ligne</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="client_style.css" rel="stylesheet">
</head>
<body>

<!-- ==================== NAVBAR ==================== -->
<div class="topbar">
    <a class="topbar-logo" href="#">
        <i class="bi bi-bag-fill"></i> MyShop
    </a>
    <div class="user-info">
        <div class="user-avatar"><?php echo strtoupper(substr($client_nom, 0, 1)); ?></div>
        <span class="user-name"><?php echo htmlspecialchars($client_nom); ?></span>
        <a href="index.php" class="btn-logout">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>


<!-- ==================== MAIN ==================== -->
<div class="main-layout">

    <!-- ====== PRODUITS ====== -->
    <div class="products-section">

        <div class="section-header">
            <h5>Nos Produits</h5>
            <span class="products-count"><?php echo count($products); ?> produit(s)</span>
        </div>

        <!-- Hero Banner -->
        <div class="hero-banner">
            <div class="hero-content">
                <div class="hero-title">Bienvenue, <?php echo htmlspecialchars($client_nom); ?> ! 👋</div>
                <div class="hero-subtitle">Découvrez nos produits et passez votre commande en quelques clics</div>
            </div>
        </div>

        <!-- Barre de recherche -->
        <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" placeholder="Rechercher un produit...">
        </div>

        <!-- Alertes -->
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4 py-3">
            <i class="bi bi-check-circle-fill"></i> Commande passée avec succès !
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 py-3">
            <i class="bi bi-exclamation-circle-fill"></i>
            <div>
                <strong>Erreur:</strong> 
                <?php 
                $errorMsg = $_GET['error'];
                if ($errorMsg == 'db_error') {
                    echo "Erreur de base de données. Veuillez réessayer.";
                } elseif ($errorMsg == 'empty_cart') {
                    echo "Le panier est vide.";
                } else {
                    echo htmlspecialchars(urldecode($errorMsg));
                }
                ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Grille des produits -->
        <?php if (!empty($products)): ?>
        <div class="products-grid" id="productsGrid">

            <?php foreach ($products as $p): ?>

            <?php
                $emojis = ['📦','📱','💻','🎧','⌨️','🖥️','🖱️','👟','👕','📷'];
                $emoji  = $emojis[($p['produit_id'] - 1) % count($emojis)];
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

                <div class="product-img">
                    <?php echo $emoji; ?>
                    <button class="quick-add" onclick="event.stopPropagation(); addToCart(<?php echo $p['produit_id']; ?>, '<?php echo $nom_js; ?>', <?php echo $p['prix']; ?>, '<?php echo $emoji; ?>');">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>

                <div class="product-body">
                    <div class="product-name"><?php echo htmlspecialchars($p['nom']); ?></div>
                    <div class="product-desc"><?php echo htmlspecialchars($p['description'] ?? 'Aucune description.'); ?></div>

                    <div class="product-footer">
                        <div class="product-price"><?php echo number_format($p['prix'], 0, ',', ' '); ?> DA</div>
                        <?php if ($p['stock'] <= 5 && $p['stock'] > 0): ?>
                            <span class="stock-badge stock-low"><i class="bi bi-exclamation-circle"></i> Stock: <?php echo $p['stock']; ?></span>
                        <?php elseif ($p['stock'] == 0): ?>
                            <span class="stock-badge stock-out"><i class="bi bi-x-circle"></i> Rupture</span>
                        <?php else: ?>
                            <span class="stock-badge stock-ok"><i class="bi bi-check-circle"></i> En stock</span>
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


<!-- ==================== CHATBOT WIDGET ==================== -->
<div class="chat-widget" id="chatWidget">
    <button class="chat-toggle" id="chatToggle" onclick="toggleChat()">
        <i class="bi bi-chat-dots-fill"></i>
        <span class="notification-dot"></span>
    </button>

    <div class="chat-box" id="chatBox">
        <div class="chat-header">
            <div class="chat-avatar">🤖</div>
            <div class="chat-header-info">
                <div class="chat-header-title">Assistant MyShop</div>
                <div class="chat-header-status">
                    <span class="status-dot"></span>
                    En ligne
                </div>
            </div>
            <button class="chat-close" onclick="toggleChat()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="chat-body" id="chatBody">
            <div class="chat-message bot">
                <div>👋 Bonjour <?php echo htmlspecialchars($client_nom); ?> ! Je suis votre assistant virtuel. Comment puis-je vous aider aujourd'hui ?</div>
                <div class="chat-message-time"><?php echo date('H:i'); ?></div>
            </div>
        </div>

        <div class="suggested-panel">
            <div class="suggested-title">Questions fréquentes</div>
            <div class="quick-replies" id="quickReplies">
                <button class="quick-reply" onclick="sendQuickReply('Comment commander ?')">🛒 Comment commander ?</button>
                <button class="quick-reply" onclick="sendQuickReply('Délai de livraison')">🚚 Délai de livraison</button>
                <button class="quick-reply" onclick="sendQuickReply('Modes de paiement')">💳 Paiement</button>
                <button class="quick-reply" onclick="sendQuickReply('Comment suivre ma commande ?')">📦 Suivi commande</button>
                <button class="quick-reply" onclick="sendQuickReply('Politique de retour')">↩️ Retours</button>
                <button class="quick-reply" onclick="sendQuickReply('Horaires d\'ouverture')">🕐 Horaires</button>
            </div>
        </div>

        <div class="chat-footer">
            <input type="text" class="chat-input" id="chatInput" placeholder="Écrivez votre message..." onkeypress="handleChatKey(event)">
            <button class="chat-send" id="chatSend" onclick="sendChatMessage()">
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
    </div>
</div>


<!-- Toast container -->
<div class="toast-wrap" id="toastWrap"></div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

/* ============================================
   CART SYSTEM
   ============================================ */
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

/* ============================================
   MODAL SYSTEM
   ============================================ */
var _mId=0, _mNom='', _mPrix=0, _mEmoji='', _mQty=1;

function openModal(id, nom, emoji, prix, stock, desc) {
    _mId=id; _mNom=nom; _mPrix=prix; _mEmoji=emoji; _mQty=1;

    document.getElementById('modalEmoji').textContent  = emoji;
    document.getElementById('modalName').textContent   = nom;
    document.getElementById('modalDesc').textContent   = desc || 'Aucune description.';
    document.getElementById('modalQtyVal').textContent = 1;
    document.getElementById('modalPrice').textContent  = prix.toLocaleString('fr-DZ') + ' DA';

    var stockEl = document.getElementById('modalStock');
    if (stock <= 5 && stock > 0) {
        stockEl.className = 'modal-stock-low';
        stockEl.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>Stock limité: ' + stock;
    } else if (stock == 0) {
        stockEl.className = 'modal-stock-low';
        stockEl.innerHTML = '<i class="bi bi-x-circle me-1"></i>Rupture de stock';
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

/* ============================================
   SEARCH SYSTEM
   ============================================ */
document.getElementById('searchInput').addEventListener('keyup', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('.product-col').forEach(function(col) {
        col.style.display = col.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

/* ============================================
   TOAST SYSTEM
   ============================================ */
function showToast(msg) {
    var wrap = document.getElementById('toastWrap');
    var t = document.createElement('div');
    t.className = 'toast-item';
    t.innerHTML = '<i class="bi bi-cart-check"></i>' + msg;
    wrap.appendChild(t);
    setTimeout(function(){ t.remove(); }, 2500);
}

/* ============================================
   CHATBOT SYSTEM - Assistant Virtuel
   ============================================ */

var chatbotKnowledge = {
    'bonjour': 'Bonjour ! 😊 Je suis ravi de vous aider. Posez-moi vos questions sur nos produits, commandes, ou livraisons.',
    'salut': 'Salut ! 👋 Comment puis-je vous assister aujourd\'hui ?',
    'hello': 'Hello ! 🌟 Bienvenue sur MyShop. Que puis-je faire pour vous ?',

    'commander': 'Pour commander :\n1️⃣ Parcourez nos produits\n2️⃣ Cliquez sur "Ajouter" ou l\'icône ➕\n3️⃣ Ajustez les quantités dans le panier\n4️⃣ Cliquez sur "Commander"\n\nC\'est simple et rapide ! 🛒',
    'comment commander': 'Pour commander :\n1️⃣ Parcourez nos produits\n2️⃣ Cliquez sur "Ajouter" ou l\'icône ➕\n3️⃣ Ajustez les quantités dans le panier\n4️⃣ Cliquez sur "Commander"\n\nC\'est simple et rapide ! 🛒',
    'passer commande': 'Pour commander :\n1️⃣ Parcourez nos produits\n2️⃣ Cliquez sur "Ajouter" ou l\'icône ➕\n3️⃣ Ajustez les quantités dans le panier\n4️⃣ Cliquez sur "Commander"\n\nC\'est simple et rapide ! 🛒',

    'livraison': '🚚 Nos délais de livraison :\n• Alger : 24-48h\n• Wilayas principales : 2-3 jours\n• Autres wilayas : 3-5 jours\n\nLivraison gratuite à partir de 10,000 DA !',
    'délai': '🚚 Nos délais de livraison :\n• Alger : 24-48h\n• Wilayas principales : 2-3 jours\n• Autres wilayas : 3-5 jours\n\nLivraison gratuite à partir de 10,000 DA !',
    'délai de livraison': '🚚 Nos délais de livraison :\n• Alger : 24-48h\n• Wilayas principales : 2-3 jours\n• Autres wilayas : 3-5 jours\n\nLivraison gratuite à partir de 10,000 DA !',

    'paiement': '💳 Modes de paiement acceptés :\n• Paiement à la livraison (Cash)\n• Carte Edahabia\n• Carte CCP\n• Virement bancaire\n\nToutes les transactions sont sécurisées ! 🔒',
    'payement': '💳 Modes de paiement acceptés :\n• Paiement à la livraison (Cash)\n• Carte Edahabia\n• Carte CCP\n• Virement bancaire\n\nToutes les transactions sont sécurisées ! 🔒',
    'modes de paiement': '💳 Modes de paiement acceptés :\n• Paiement à la livraison (Cash)\n• Carte Edahabia\n• Carte CCP\n• Virement bancaire\n\nToutes les transactions sont sécurisées ! 🔒',

    'suivi': '📦 Pour suivre votre commande :\n1. Connectez-vous à votre compte\n2. Allez dans "Mes Commandes"\n3. Cliquez sur la commande concernée\n\nVous recevrez aussi des SMS à chaque étape !',
    'suivre': '📦 Pour suivre votre commande :\n1. Connectez-vous à votre compte\n2. Allez dans "Mes Commandes"\n3. Cliquez sur la commande concernée\n\nVous recevrez aussi des SMS à chaque étape !',
    'comment suivre ma commande': '📦 Pour suivre votre commande :\n1. Connectez-vous à votre compte\n2. Allez dans "Mes Commandes"\n3. Cliquez sur la commande concernée\n\nVous recevrez aussi des SMS à chaque étape !',

    'retour': '↩️ Politique de retour :\n• Délai : 7 jours après réception\n• Produit doit être intact\n• Contactez-nous via le support\n\nRemboursement sous 5 jours ouvrés.',
    'retours': '↩️ Politique de retour :\n• Délai : 7 jours après réception\n• Produit doit être intact\n• Contactez-nous via le support\n\nRemboursement sous 5 jours ouvrés.',
    'politique de retour': '↩️ Politique de retour :\n• Délai : 7 jours après réception\n• Produit doit être intact\n• Contactez-nous via le support\n\nRemboursement sous 5 jours ouvrés.',

    'horaire': '🕐 Nos horaires d\'ouverture :\n• Lundi - Jeudi : 8h30 - 17h30\n• Vendredi : 8h30 - 12h00\n• Samedi : 9h00 - 13h00\n• Dimanche : Fermé',
    'horaires': '🕐 Nos horaires d\'ouverture :\n• Lundi - Jeudi : 8h30 - 17h30\n• Vendredi : 8h30 - 12h00\n• Samedi : 9h00 - 13h00\n• Dimanche : Fermé',
    'heure': '🕐 Nos horaires d\'ouverture :\n• Lundi - Jeudi : 8h30 - 17h30\n• Vendredi : 8h30 - 12h00\n• Samedi : 9h00 - 13h00\n• Dimanche : Fermé',

    'contact': '📞 Contactez-nous :\n• Téléphone : 0234 XX XX XX\n• Email : support@myshop.dz\n• WhatsApp : +213 5XX XX XX XX\n\nNous répondons sous 24h !',
    'téléphone': '📞 Contactez-nous :\n• Téléphone : 0234 XX XX XX\n• Email : support@myshop.dz\n• WhatsApp : +213 5XX XX XX XX\n\nNous répondons sous 24h !',
    'email': '📞 Contactez-nous :\n• Téléphone : 0234 XX XX XX\n• Email : support@myshop.dz\n• WhatsApp : +213 5XX XX XX XX\n\nNous répondons sous 24h !',

    'prix': '💰 Les prix affichés sont en Dinars Algériens (DA) et incluent la TVA. Des promotions sont régulièrement disponibles !',
    'promotion': '🎉 Consultez nos promotions dans la section "Offres Spéciales" ! Inscrivez-vous à la newsletter pour ne rien manquer.',
    'promo': '🎉 Consultez nos promotions dans la section "Offres Spéciales" ! Inscrivez-vous à la newsletter pour ne rien manquer.',

    'stock': '📦 Le stock est affiché sur chaque produit :\n• Vert = En stock\n• Orange = Stock limité\n• Rouge = Rupture de stock\n\nLes quantités sont mises à jour en temps réel.',
    'disponible': '📦 Le stock est affiché sur chaque produit :\n• Vert = En stock\n• Orange = Stock limité\n• Rouge = Rupture de stock\n\nLes quantités sont mises à jour en temps réel.',

    'compte': '👤 Gestion de compte :\n• Modifiez vos infos dans "Profil"\n• Changez le mot de passe\n• Consultez l\'historique des commandes\n\nBesoin d\'aide ? Contactez le support.',
    'profil': '👤 Gestion de compte :\n• Modifiez vos infos dans "Profil"\n• Changez le mot de passe\n• Consultez l\'historique des commandes\n\nBesoin d\'aide ? Contactez le support.',

    'aide': '❓ Je peux vous aider avec :\n• Comment commander\n• Délai de livraison\n• Modes de paiement\n• Suivi de commande\n• Politique de retour\n• Horaires d\'ouverture\n\nPosez votre question !',
    'help': '❓ Je peux vous aider avec :\n• Comment commander\n• Délai de livraison\n• Modes de paiement\n• Suivi de commande\n• Politique de retour\n• Horaires d\'ouverture\n\nPosez votre question !',
    'merci': 'Avec plaisir ! 😊 N\'hésitez pas si vous avez d\'autres questions. Bonne shopping ! 🛍️',
    'thank': 'Avec plaisir ! 😊 N\'hésitez pas si vous avez d\'autres questions. Bonne shopping ! 🛍️',

    'bye': 'Au revoir ! 👋 Passez une excellente journée et à bientôt sur MyShop !',
    'au revoir': 'Au revoir ! 👋 Passez une excellente journée et à bientôt sur MyShop !',
    'adieu': 'Au revoir ! 👋 Passez une excellente journée et à bientôt sur MyShop !'
};

function findBotResponse(userMessage) {
    var msg = userMessage.toLowerCase().trim();
    if (chatbotKnowledge[msg]) {
        return chatbotKnowledge[msg];
    }
    for (var keyword in chatbotKnowledge) {
        if (msg.includes(keyword)) {
            return chatbotKnowledge[keyword];
        }
    }
    var defaultResponses = [
        "Je n'ai pas bien compris. 😅 Pouvez-vous reformuler ou choisir une question suggérée ci-dessous ?",
        "Hmm, je ne suis pas sûr de comprendre. 🤔 Essayez l'une des questions fréquentes ci-dessous !",
        "Désolé, je n'ai pas la réponse à cette question. 😔 Contactez notre support pour plus d'aide."
    ];
    return defaultResponses[Math.floor(Math.random() * defaultResponses.length)];
}

function toggleChat() {
    var chatBox = document.getElementById('chatBox');
    var chatToggle = document.getElementById('chatToggle');
    if (chatBox.classList.contains('active')) {
        chatBox.classList.remove('active');
        chatToggle.style.display = 'flex';
    } else {
        chatBox.classList.add('active');
        chatToggle.style.display = 'none';
        document.getElementById('chatInput').focus();
    }
}

function sendChatMessage() {
    var input = document.getElementById('chatInput');
    var message = input.value.trim();
    if (!message) return;
    addUserMessage(message);
    input.value = '';
    showTypingIndicator();
    setTimeout(function() {
        hideTypingIndicator();
        var response = findBotResponse(message);
        addBotMessage(response);
    }, 800 + Math.random() * 600);
}

function sendQuickReply(message) {
    addUserMessage(message);
    showTypingIndicator();
    setTimeout(function() {
        hideTypingIndicator();
        var response = findBotResponse(message);
        addBotMessage(response);
    }, 600 + Math.random() * 400);
}

function addUserMessage(text) {
    var chatBody = document.getElementById('chatBody');
    var time = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    var msgDiv = document.createElement('div');
    msgDiv.className = 'chat-message user';
    msgDiv.innerHTML = '<div>' + escapeHtml(text) + '</div><div class="chat-message-time">' + time + '</div>';
    chatBody.appendChild(msgDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
}

function addBotMessage(text) {
    var chatBody = document.getElementById('chatBody');
    var time = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    var msgDiv = document.createElement('div');
    msgDiv.className = 'chat-message bot';
    msgDiv.innerHTML = '<div>' + text.replace(/\n/g, '<br>') + '</div><div class="chat-message-time">' + time + '</div>';
    chatBody.appendChild(msgDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
}

function showTypingIndicator() {
    var chatBody = document.getElementById('chatBody');
    var typingDiv = document.createElement('div');
    typingDiv.className = 'typing-indicator';
    typingDiv.id = 'typingIndicator';
    typingDiv.innerHTML = '<span></span><span></span><span></span>';
    chatBody.appendChild(typingDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
}

function hideTypingIndicator() {
    var typing = document.getElementById('typingIndicator');
    if (typing) typing.remove();
}

function handleChatKey(event) {
    if (event.key === 'Enter') {
        sendChatMessage();
    }
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    if (!localStorage.getItem('chatbotVisited')) {
        setTimeout(function() {
            if (!document.getElementById('chatBox').classList.contains('active')) {
                toggleChat();
            }
        }, 5000);
        localStorage.setItem('chatbotVisited', 'true');
    }
});

</script>
</body>
</html>