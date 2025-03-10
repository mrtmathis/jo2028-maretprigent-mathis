<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Générer un token CSRF si ce n'est pas déjà fait
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Connexion à la base de données
require_once("../../../database/database.php");

// Traitement de l'ajout de l'athlète si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
        $nom_athlete = $_POST['nom_athlete'] ?? '';
        $prenom_athlete = $_POST['prenom_athlete'] ?? '';
        $id_pays = $_POST['id_pays'] ?? '';
        $id_genre = $_POST['id_genre'] ?? '';

        // Prépare et exécute la requête d'insertion
        $query = "INSERT INTO athlete (nom_athlete, prenom_athlete, id_pays, id_genre) VALUES (:nom_athlete, :prenom_athlete, :id_pays, :id_genre)";
        $stmt = $connexion->prepare($query);
        
        $stmt->bindParam(':nom_athlete', $nom_athlete);
        $stmt->bindParam(':prenom_athlete', $prenom_athlete);
        $stmt->bindParam(':id_pays', $id_pays);
        $stmt->bindParam(':id_genre', $id_genre);

        if ($stmt->execute()) {
            // Redirige vers la page de gestion des athlètes après un ajout réussi
            header('Location: manage-athletes.php');
            exit();
        } else {
            echo "<p>Erreur lors de l'ajout de l'athlète.</p>";
        }
    } else {
        echo "<p>Token CSRF invalide.</p>";
    }
}

// Récupération des pays
$query_countries = "SELECT id_pays, nom_pays FROM pays ORDER BY nom_pays";
$stmt_countries = $connexion->prepare($query_countries);
$stmt_countries->execute();

// Récupération des genres
$query_genres = "SELECT id_genre, nom_genre FROM genre ORDER BY nom_genre";
$stmt_genres = $connexion->prepare($query_genres);
$stmt_genres->execute();
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
    <title>Ajouter un athlète - Jeux Olympiques - Los Angeles 2028</title>
</head>

<body>
    <header>
        <nav>
            <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Ajouter un athlète</h1>
        <form action="" method="POST">
            <!-- Ajoutez le token CSRF au formulaire -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form-group">
                <label for="nom_athlete">Nom :</label>
                <input type="text" id="nom_athlete" name="nom_athlete" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="prenom_athlete">Prénom :</label>
                <input type="text" id="prenom_athlete" name="prenom_athlete" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="id_pays">Nationalité :</label>
                <input type="text" id="id_pays" name="id_pays_name" class="form-control" list="pays-list" required>
                <datalist id="pays-list">
                    <?php while ($row = $stmt_countries->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?= htmlspecialchars($row['nom_pays']) ?>" data-id="<?= htmlspecialchars($row['id_pays']) ?>">
                    <?php endwhile; ?>
                </datalist>
                <input type="hidden" id="id_pays_hidden" name="id_pays">
            </div>

            <div class="form-group">
                <label for="id_genre">Genre :</label>
                <input type="text" id="id_genre" name="id_genre_name" class="form-control" list="genre-list" required>
                <datalist id="genre-list">
                    <?php while ($row = $stmt_genres->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?= htmlspecialchars($row['nom_genre']) ?>" data-id="<?= htmlspecialchars($row['id_genre']) ?>">
                    <?php endwhile; ?>
                </datalist>
                <input type="hidden" id="id_genre_hidden" name="id_genre">
            </div>

            <button type="submit" class="btn-submit">Ajouter l'athlète</button>
        </form>
        
        <p><a href="../admin.php">Retour à l'accueil administration</a></p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>

    <script>
        // Capture les sélections de nationalité et de genre pour les champs cachés
        document.getElementById('id_pays').addEventListener('input', function() {
            let selected = Array.from(document.getElementById('pays-list').options).find(option => option.value === this.value);
            document.getElementById('id_pays_hidden').value = selected ? selected.getAttribute('data-id') : '';
        });

        document.getElementById('id_genre').addEventListener('input', function() {
            let selected = Array.from(document.getElementById('genre-list').options).find(option => option.value === this.value);
            document.getElementById('id_genre_hidden').value = selected ? selected.getAttribute('data-id') : '';
        });
    </script>
</body>
</html>
