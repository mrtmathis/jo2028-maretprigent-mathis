<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Générer un token CSRF si ce n'est pas déjà fait
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sécuriser les données
    $nom_pays = filter_input(INPUT_POST, 'nom_pays', FILTER_SANITIZE_SPECIAL_CHARS);
  

    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Token CSRF invalide.";
        header("Location: add-countries.php");
        exit();
    }

    // Vérifiez si les champs sont vides
    if (empty($nom_pays) ) {
        $_SESSION['error'] = "Tous les champs sont obligatoires.";
        header("Location: add-countries.php");
        exit();
    }

    try {
        // Vérifiez si le pays existe déjà
        $queryCheck = "SELECT id_pays FROM pays WHERE nom_pays = :nom_pays";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nom_pays", $nom_pays, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Le pays existe déjà.";
            header("Location: add-countries.php");
            exit();
        }

       
        // Insertion dans la base de données
        $queryInsert = "INSERT INTO pays (nom_pays) VALUES (:nom_pays)";
        $statementInsert = $connexion->prepare($queryInsert);
        $statementInsert->bindParam(":nom_pays", $nom_pays, PDO::PARAM_STR);
        $statementInsert->execute();

        $_SESSION['success'] = "le pays ajouté avec succès.";
        header("Location: add-countries.php"); // Redirection vers add-countries.php
        exit(); // Assurez-vous d'ajouter cette ligne
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de l'ajout du pays : " . $e->getMessage();
        header("Location: add-countries.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../css/normalize.css">
    <link rel="stylesheet" href="../../../css/styles-computer.css">
    <link rel="stylesheet" href="../../../css/styles-responsive.css">
    <link rel="shortcut icon" href="../../../img/favicon-jo-2024.ico" type="image/x-icon">
    <title>Ajouter un Athlète - Jeux Olympiques 2024</title>
    <title>Ajouter un pays</title>
</head>
<body>
    <header>
        <nav>
            <!-- Ajoutez votre navigation ici -->
        </nav>
    </header>

    <main>
        <h1>Ajouter un pays</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p style="color: green;">' . htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['success']);
        }
        ?>
       <form action="add-countries.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter ce pays ?')">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    
    <label for="nom_pays">Nom Pays :</label>
    <input type="text" name="nom_pays" id="nom_pays" required><br>

    <!-- Ajoutez le bouton de soumission ici -->
    <button type="submit">Ajouter le pays</button>
</form>
        <p>
            <a href="manage-countries.php">Retour à la gestion des pays</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>
</html>
