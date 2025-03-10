<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Fonction pour vérifier le token CSRF
function checkCSRFToken() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('Token CSRF invalide.');
        }
    }
}

// Générer un token CSRF si ce n'est pas déjà fait
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère un token CSRF
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
    <link rel="shortcut icon" href="../../../img/favicon.ico" type="image/x-icon">
    <title>Liste des athlètes - Jeux Olympiques - Los Angeles 2028</title>
</head>

<body>
    <header>
    <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="../admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="../admin-places/manage-places.php">Gestion Lieux</a></li>
                <li><a href="../admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-events/manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
        <style>
                .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .action-buttons button {
            background-color: #1b1b1b;
            color: #d7c378;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        </style>
    </header>

    <main>
        <h1>Liste des athlètes</h1>
        <div class="action-buttons">
            <button onclick="openAddathleteForm()">Ajouter un athlète</button>
        </div>
        
        <!-- Tableau des athlètes -->
        <?php
        require_once("../../../database/database.php");

        try {
            // Requête pour récupérer la liste des athlètes depuis la base de données
            $query = "
                SELECT a.*, p.nom_pays, g.nom_genre 
                FROM athlete a
                LEFT JOIN pays p ON a.id_pays = p.id_pays
                LEFT JOIN genre g ON a.id_genre = g.id_genre
                ORDER BY a.nom_athlete";
            $statement = $connexion->prepare($query);
            $statement->execute();

            // Vérifier s'il y a des résultats
            if ($statement->rowCount() > 0) {
                echo "<table><tr>
                        <th>Nom de l'athlète</th>
                        <th>Prénom de l'athlète</th>
                        <th>Nationalité</th>
                        <th>Genre</th>
                        <th>Modifier</th>
                        <th>Supprimer</th>
                      </tr>";

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nom_athlete'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['prenom_athlete'], ENT_QUOTES, 'UTF-8') . "</td>";
                    
                    // Afficher les noms des lieux et des sports
                    echo "<td>" . htmlspecialchars($row['nom_pays'] ?? 'Non spécifié', ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_genre'] ?? 'Non spécifié', ENT_QUOTES, 'UTF-8') . "</td>";

                    echo "<td><button onclick='openModifyathleteForm({$row['id_athlete']})'>Modifier</button></td>";
                    echo "<td><button onclick='deleteathleteConfirmation({$row['id_athlete']})'>Supprimer</button></td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucun athlète trouvé.</p>";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
        ?>
        
        <p class="paragraph-link">
            <a class="link-home" href="../admin.php">Accueil administration</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>

    <script>
        function openAddathleteForm() {
            window.location.href = 'add-athletes.php';  // Assurez-vous que le fichier existe
        }

        function openModifyathleteForm(id_athlete) {
            window.location.href = 'modify-athletes.php?id_athlete=' + id_athlete;
        }

        function deleteathleteConfirmation(id_athlete) {
            if (confirm("Êtes-vous sûr de vouloir supprimer cet athlète ?")) {
                window.location.href = 'delete-athletes.php?id_athlete=' + id_athlete;
            }
        }
    </script>
</body>

</html>
