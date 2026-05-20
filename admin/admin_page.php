<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="style/admin_page_style.css" rel="stylesheet">
</head>

<body>

    <!-- Overlay (mobile) -->
    <div class="overlay" id="overlay">
    </div>


    <!-- ==================== SIDEBAR ==================== -->
    <aside id="sidebar">

        <!-- Logo -->
        <div class="sidebar-brand d-flex align-items-center gap-3">
            <div class="brand-icon">&#x26A1;</div>
            <div class="fw-bold text-white fs-5">AdminPanel</div>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav">

            <div class="sidebar-section-label">Tableau de bord</div>

            <!-- data-page = le fichier à charger dans l'iframe -->

            <a href="#" class="nav-link-item active" data-page="admin_dashboard.php">
                <i class="bi bi-grid-1x2-fill"></i>
                Dashboard
            </a>

            <div class="sidebar-section-label">Gestion</div>

            <a href="#" class="nav-link-item" data-page="gestion_product.php">
                <i class="bi bi-box-seam-fill"></i>
                Gestion Produits
                <span class="badge bg-success ms-auto">124</span>
            </a>

            <a href="#" class="nav-link-item" data-page="gestion_client.php">
                <i class="bi bi-people-fill"></i>
                Gestion Clients
                <span class="badge bg-primary ms-auto">58</span>
            </a>

            <a href="#" class="nav-link-item" data-page="gestion_commande.php">
                <i class="bi bi-cart-fill"></i>
                Gestion Commandes
                <span class="badge bg-danger ms-auto">7</span>
            </a>

            <a href="#" class="nav-link-item" data-page="gestion_expedition.php">
                <i class="bi bi-truck"></i>
                Gestion Expédition
                <span class="badge bg-danger ms-auto">7</span>
            </a>

            <div class="sidebar-section-label">Rapports</div>

            <a href="#" class="nav-link-item" data-page="rapport_ventes.php">
                <i class="bi bi-bar-chart-fill"></i>
                Ventes par Catégorie
            </a>

            <a href="#" class="nav-link-item" data-page="commandes_attente.php">
                <i class="bi bi-hourglass-split"></i>
                Commandes en Attente
                <span class="badge bg-warning text-dark ms-auto">7</span>
            </a>

        </nav>

        <a href="admin_login.php" class="nav-link-item">
            <i class="bi bi-box-arrow-left"></i>
            Deconnexion
        </a>

    </aside>


    <!-- ==================== MAIN ==================== -->
    <div class="main-content">

        <!-- Topbar -->
        <div class="topbar">
            <button onclick="toggleSidebar()" id="sidebarToggle" class="btn btn-light">
                <i class="bi bi-list"></i>
            </button>

            <h2>Bienvenue, Azzeddine</h2>
        </div>

        <!-- Zone de contenu = iframe -->
        <iframe id="contentFrame" src="admin_dashboard.php" name="contentFrame"></iframe>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin_dashbord.js"></script>
</body>

</html>