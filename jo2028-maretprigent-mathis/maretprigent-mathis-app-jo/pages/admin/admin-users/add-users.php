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
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère un token CSRF sécurisé
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nom_utilisateur = filter_input(INPUT_POST, 'nom_utilisateur', filter: FILTER_SANITIZE_SPECIAL_CHARS);
    $prenom_utilisateur = filter_input(INPUT_POST, 'prenom_utilisateur', filter: FILTER_SANITIZE_SPECIAL_CHARS);
    $login = filter_input(INPUT_POST, 'login', filter: FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', filter: FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Token CSRF invalide.";
        header("Location: add-users.php");
        exit();
    }

    // Vérifiez si le nom du sport est vide
    if (empty($nom_utilisateur) || empty($prenom_utilisateur) || empty($login) || empty($password)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
        header("Location: add-users.php");
        exit();
    }

    try {
        // Vérifiez si l'utilisateur existe déjà
        $queryCheck = "SELECT id_utilisateur FROM utilisateur WHERE login = :login";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":login", $login, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'utilisateur' existe déjà.";
            header("Location: add-users.php");
            exit();
        } 
        
        // Hachage du mot de passe
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insertion dans la base de données
        $queryInsert = "INSERT INTO utilisateur (nom_utilisateur, prenom_utilisateur, login, password) VALUES (:nom_utilisateur, :prenom_utilisateur, :login, :password)";
        $statementInsert = $connexion->prepare($queryInsert);
        $statementInsert->bindParam("password", $hashed_password, PDO:: PARAM_STR);
        $statementInsert->bindParam(":login", $login, PDO::PARAM_STR);
        $statementInsert->bindParam("nom_utilisateur", $nom_utilisateur, PDO::PARAM_STR);
        $statementInsert->bindParam("prenom_utilisateur", $prenom_utilisateur, PDO::PARAM_STR);
        $statementInsert->execute();
        
        
        
    } 

        else {
            // Requête pour ajouter un sport
            $query = "INSERT INTO utilisateur (nom_utilisateur) VALUES (:nom_utilisateur)";
            $statement = $connexion->prepare($query);
            $statement->bindParam(":nom_utilisateur", $nom_utilisateur, PDO::PARAM_STR);

            // Exécutez la requête
            if ($statement->execute()) {
                $_SESSION['success'] = "L'utilisateur' a été ajouté avec succès.";
                header("Location: manage-users.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout de l'utilisateur.";
                header("Location: add-users.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header("Location: add-users.php");
        exit();
    }


// Afficher les erreurs en PHP (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../css/normalize.css">
    <link rel="stylesheet" href="../../../css/styles-computer.css">
    <link rel="stylesheet" href="../../../css/styles-responsive.css">
    <link rel="shortcut icon" href="../../../img/favicon.ico" type="image/x-icon">
    <title>Ajouter un Utilisateur - Jeux Olympiques - Los Angeles 2028</title>
    <style>
        /* Ajoutez votre style CSS ici */
    </style>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="manage-sports.php">Gestion Sports</a></li>
                <li><a href="manage-places.php">Gestion Lieux</a></li>
                <li><a href="manage-countries.php">Gestion Pays</a></li>
                <li><a href="manage-events.php">Gestion Calendrier</a></li>
                <li><a href="manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Ajouter un Utilisateur</h1>
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
        <form action="add-users.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cet utilisateur ?')">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <label for="nom_utilisateur">Nom de l'Utilisateur :</label>
            <input type="text" name="nom_utilisateur" id="nom_utilisateur" required>
            <label for="prenom_utilisateur">Prénom de l'Utilisateur :</label>
            <input type="text" name="prenom_utilisateur" id="prenom_utilisateur" required><br>
            <label for="login">Login Utilisateur:</label>
            <input type="text" name="login" id="login" required><br>
            <label for="password">Mot de passe :</label>
            <input type="password" name="password" id="password" required><br>
            <input type="submit" value="Ajouter l'Utilisateur'">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-users.php">Retour à la gestion des utilisateurs</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>

</body>

</html>
