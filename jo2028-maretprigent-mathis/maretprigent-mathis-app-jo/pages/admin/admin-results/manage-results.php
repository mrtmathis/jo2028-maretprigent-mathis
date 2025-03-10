<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
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
    <title>Liste des Résultats - Jeux Olympiques 2024</title>
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

        .action-buttons button:hover {
            background-color: #d7c378;
            color: #1b1b1b;
        }
    </style>
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
    </header>
    <main>
        <h1>Liste des résultats</h1>
        <div class="action-buttons">
            <button onclick="openAddResultsForm()">Ajouter un résultat</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Nom de l'athlète</th>
                    <th>Prénom de l'athlète</th>
                    <th>Nom du pays</th>
                    <th>ID de l'épreuve</th>
                    <th>Résultat</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $query = "SELECT ATHLETE.nom_athlete, ATHLETE.prenom_athlete, PAYS.nom_pays, PARTICIPER.id_epreuve, PARTICIPER.resultat, PARTICIPER.id_athlete
                              FROM PARTICIPER
                              JOIN ATHLETE ON PARTICIPER.id_athlete = ATHLETE.id_athlete
                              JOIN PAYS ON ATHLETE.id_pays = PAYS.id_pays
                              ORDER BY ATHLETE.nom_athlete, PARTICIPER.id_epreuve";
                    $statement = $connexion->prepare($query);
                    $statement->execute();

                    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['nom_athlete']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['prenom_athlete']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nom_pays']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['id_epreuve']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['resultat']) . "</td>";
                        echo "<td><button onclick='openModifyResultsForm(" . $row['id_athlete'] . "," . $row['id_epreuve'] . ")'>Modifier</button></td>";
                        echo "<td><button onclick='deleteResultsConfirmation(" . $row['id_athlete'] . "," . $row['id_epreuve'] . ")'>Supprimer</button></td>";
                        echo "</tr>";
                    }
                } catch (PDOException $e) {
                    echo '<tr><td colspan="7" style="color: red;">Erreur de base de données : ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
        <p class="paragraph-link">
            <a class="link-home" href="../admin.php">Accueil administration</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
    <script>
        function openAddResultsForm() {
            window.location.href = 'add-results.php';
        }

        function openModifyResultsForm(id_athlete, id_epreuve) {
            window.location.href = 'modify-results.php?id_athlete=' + id_athlete + '&id_epreuve=' + id_epreuve;
        }

        function deleteResultsConfirmation(id_athlete, id_epreuve) {
            if (confirm("Êtes-vous sûr de vouloir supprimer ce résultat?")) {
                window.location.href = 'delete-results.php?id_athlete=' + id_athlete + '&id_epreuve=' + id_epreuve;
            }
        }
    </script>
</body>

</html>
