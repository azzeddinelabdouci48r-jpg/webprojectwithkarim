<?php
include 'init.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $email = $_POST['email'];

    $stmt = $pdo->prepare('INSERT INTO CLIENT (nom, adresse, email) VALUES (:nom, :adresse, :email)');
    $stmt->bindParam(':nom', $name);
    $stmt->bindParam(':adresse', $address);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $id_client = $pdo->lastInsertId();
    $_SESSION['idClient'] = $id_client;

    header('Location: client_page.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../design/login_style.css" rel="stylesheet">
</head>

<body>

    <div class="login-card">

        <!-- Icone + titre -->
        <div class="login-icon"><i class="fa fa-user-lock"></i></div>
        <h1>Login</h1>
        <p class="subtitle">Entrez vos informations pour continuer</p>

        <!-- Formulaire -->
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

            <!-- Nom -->
            <div class="mb-3">
                <label class="form-label">Nom d'utilisateur</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="name" class="form-control" placeholder="Votre nom" required>
                </div>
            </div>

            <!-- Adresse -->
            <div class="mb-3">
                <label class="form-label">Adresse</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                    <input type="text" name="address" class="form-control" placeholder="Votre adresse" required>
                </div>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="exemple@email.com" required>
                </div>
            </div>

            <!-- Bouton -->
            <div class="btnContainer">
                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                </button>
            </div>

        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php include 'include/template/footer.php'; ?>