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
    // Sécuriser les données
$nom_lieu = filter_input(INPUT_POST, 'nom_lieu', FILTER_SANITIZE_SPECIAL_CHARS);
$adresse_lieu = filter_input(INPUT_POST, 'adresse_lieu', FILTER_SANITIZE_SPECIAL_CHARS);
$cp_lieu = filter_input(INPUT_POST, 'cp_lieu', FILTER_SANITIZE_SPECIAL_CHARS);
$ville_lieu = filter_input(INPUT_POST, 'ville_lieu', FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Token CSRF invalide.";
        header("Location: add-places.php");
        exit();
    }

    // Vérifiez si les champs sont vides
    if (empty($nom_lieu) || empty($adresse_lieu) || empty($cp_lieu) || empty($ville_lieu)) {
        $_SESSION['error'] = "Tous les champs sont obligatoires.";
        header("Location: add-places.php");
        exit();
    }

    try {
        // Vérifiez si le lieu existe déjà
        $queryCheck = "SELECT id_lieu FROM lieu WHERE nom_lieu = :nom_lieu";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nom_lieu", $nom_lieu, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Le lieu existe déjà.";
            header("Location: add-places.php");
            exit();
        }
    
        // Insertion dans la base de données
        $queryInsert = "INSERT INTO lieu (nom_lieu, adresse_lieu, cp_lieu, ville_lieu) VALUES (:nom_lieu, :adresse_lieu, :cp_lieu, :ville_lieu)";
        $statementInsert = $connexion->prepare($queryInsert);
        $statementInsert->bindParam(":nom_lieu", $nom_lieu , PDO::PARAM_STR);
        $statementInsert->bindParam(":adresse_lieu", $adresse_lieu, PDO::PARAM_STR);
        $statementInsert->bindParam(":cp_lieu", $cp_lieu, PDO::PARAM_STR);
        $statementInsert->bindParam(":ville_lieu", $ville_lieu, PDO::PARAM_STR);
        $statementInsert->execute();

        $_SESSION['success'] = "Le lieu a été ajouté avec succès.";
        header("Location: add-places.php"); // Redirection vers add-users.php
        exit(); // Assurez-vous d'ajouter cette ligne
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de l'ajout du lieu : " . $e->getMessage();
        header("Location: add-places.php");
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
    <title>Ajouter un utilisateur</title>
</head>
<body>
    <header>
        <nav>
            <!-- Ajoutez votre navigation ici -->
        </nav>
    </header>

    <main>
        <h1>Ajouter un lieu</h1>
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
        <form action="add-places.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter ce lieu ?')">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <label for="nom_lieu">Nom Lieu :</label>
            <input type="text" name="nom_lieu" id="nom_lieu" required><br>

            <label for="adresse_lieu">Adresse Lieu:</label>
            <input type="text" name="adresse_lieu" id="adresse_lieu" required><br>

            <label for="cp_lieu ">CP Lieu :</label>
            <input type="text" name="cp_lieu" id="cp_lieu" required><br>

            <label for="ville_lieu">Ville lieux :</label>
            <input type="text" name="ville_lieu" id="ville_lieu" required><br>

            <input type="submit" value="Ajouter le lieu">
        </form>
        <p>
            <a href="manage-places.php">Retour à la gestion des utilisateurs</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>
</html>
