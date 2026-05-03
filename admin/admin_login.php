<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = $_POST['name'];
    $password = $_POST['password'];

    if ($name == "azzeddine" && $password == "123") {
        header("Location: admin_page.php");
        exit();
    } else {
        header("Location: admin_login.php");
    }
}
?>
<!-- <!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"> -->

    <!-- Bootstrap CSS -->
<!-- </head>

<body> -->

    <!-- <div class="form-login">
        <div class="container d-flex justify-content-center flex-column w-25">
            <h2 class="text-center">Login</h2>

            <form class="d-flex flex-column" method="POST">
                <div class="mb-3">
                    <input type="text" name="name" class="form-control" placeholder="username">
                </div>

                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="password">
                </div>

                <button class="btn btn-primary">Login</button>
            </form>
        </div>
    </div> -->


<!--     

</body>

</html>



 -->




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
        <h1>Admin login</h1>
        <p class="subtitle">Entrez vos informations pour continuer</p>

        <!-- Formulaire -->
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

            <!-- Nom -->
            <div class="mb-3">
                <label class="form-label">Nom admin</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="name" class="form-control" placeholder="Votre nom" required>
                </div>
            </div>

           

            <div class="mb-4">
                <label class="form-label">password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="votre password" required>
                </div>
            </div>

            <!-- Bouton -->
            <div class="btnContainer">
                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-n-right me-2"></i>Se connecter
                </button>
            </div>

        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

