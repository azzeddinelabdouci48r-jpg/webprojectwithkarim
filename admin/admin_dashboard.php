<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="admin_page_style.css" rel="stylesheet">
</head>
<body>

<div class="p-4">

    <!-- === STATISTIQUES === -->
    <div class="row g-3 mb-4">

        <!-- Total Produits -->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase">Total Produits</p>
                        <h3 class="fw-bold mb-1">124</h3>
                        <small class="text-success"><i class="bi bi-arrow-up"></i> +12 ce mois</small>
                    </div>
                    <div class="icon-box icon-blue"><i class="bi bi-box-seam-fill"></i></div>
                </div>
            </div>
        </div>

        <!-- Clients Actifs -->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase">Clients Actifs</p>
                        <h3 class="fw-bold mb-1">58</h3>
                        <small class="text-success"><i class="bi bi-arrow-up"></i> +5 nouveaux</small>
                    </div>
                    <div class="icon-box icon-purple"><i class="bi bi-people-fill"></i></div>
                </div>
            </div>
        </div>

        <!-- Commandes -->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase">Commandes</p>
                        <h3 class="fw-bold mb-1">312</h3>
                        <small class="text-success"><i class="bi bi-arrow-up"></i> +34 cette semaine</small>
                    </div>
                    <div class="icon-box icon-green"><i class="bi bi-cart-fill"></i></div>
                </div>
            </div>
        </div>

        <!-- En Attente -->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase">En Attente</p>
                        <h3 class="fw-bold mb-1">7</h3>
                        <small class="text-danger"><i class="bi bi-exclamation-circle"></i> A traiter</small>
                    </div>
                    <div class="icon-box icon-orange"><i class="bi bi-hourglass-split"></i></div>
                </div>
            </div>
        </div>

    </div>


    <!-- === TABLEAU DES COMMANDES === -->
    <div class="card table-card mb-4">

        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4 border-0">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-receipt me-2 text-primary"></i>Dernieres Commandes
            </h6>
            <a href="#" class="btn btn-primary btn-sm"
               onclick="parent.document.querySelector('[data-page=\'gestion_commande.php\']').click(); return false;">
                Voir tout <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Client</th>
                            <th>Produit</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-muted fw-semibold">#00312</td>
                            <td>Yacine Bensalem</td>
                            <td>Laptop Pro X</td>
                            <td class="fw-bold">85 000 DA</td>
                            <td><span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Livre</span></td>
                            <td><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></button></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">#00311</td>
                            <td>Sara Medjdoub</td>
                            <td>Ecouteurs BT 500</td>
                            <td class="fw-bold">12 500 DA</td>
                            <td><span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">En attente</span></td>
                            <td><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></button></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">#00310</td>
                            <td>Karim Touati</td>
                            <td>Clavier Mecanique</td>
                            <td class="fw-bold">9 200 DA</td>
                            <td><span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Livre</span></td>
                            <td><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></button></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">#00309</td>
                            <td>Nadia Ferhat</td>
                            <td>Souris Gamer RGB</td>
                            <td class="fw-bold">4 800 DA</td>
                            <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Annule</span></td>
                            <td><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></button></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">#00308</td>
                            <td>Amine Belaid</td>
                            <td>Ecran 27 pouces 4K</td>
                            <td class="fw-bold">62 000 DA</td>
                            <td><span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">En attente</span></td>
                            <td><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>


   



</body>
</html>
