<?php
session_start();
require_once("../../../database/database.php");

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Traitement du formulaire soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Filtrage des données
    $idAthlete = filter_input(INPUT_POST, 'idAthlete', FILTER_VALIDATE_INT);
    $idEpreuve = filter_input(INPUT_POST, 'idEpreuve', FILTER_VALIDATE_INT);
    $resultat = filter_input(INPUT_POST, 'resultat', FILTER_SANITIZE_STRING);

    // Vérification des champs obligatoires
    if (empty($idAthlete) || empty($idEpreuve) || empty($resultat)) {
        $_SESSION['error'] = "Veuillez remplir les champs.";
        header("Location: add-results.php");
        exit();
    }

    try {
        // Préparation de la requête pour ajouter un résultat
        $queryAddResult = "INSERT INTO participer (id_athlete, id_epreuve, resultat) VALUES (:idAthlete, :idEpreuve, :resultat)";
        $statementAddResult = $connexion->prepare($queryAddResult);
        $statementAddResult->bindParam(":idAthlete", $idAthlete, PDO::PARAM_INT);
        $statementAddResult->bindParam(":idEpreuve", $idEpreuve, PDO::PARAM_INT);
        $statementAddResult->bindParam(":resultat", $resultat, PDO::PARAM_STR);

        // Exécution de la requête
        if ($statementAddResult->execute()) {
            header("Location: manage-results.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du résultat.";
            header("Location: add-results.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-results.php");
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
    <title>Ajouter un Résultat - Jeux Olympiques 2024</title>
    <style>
        /* Ajoutez votre style CSS ici */
    </style>
</head>

<body>
    <main>
        <h1>Ajouter un Résultat</h1>
        <?php if (isset($_SESSION['error'])): ?>
            <p style="color: red;"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <form action="add-results.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter ce résultat?')">
            <label for="idAthlete">Athlète :</label>
            <select name="idAthlete" id="idAthlete" required>
                <?php
                $queryAthleteList = "SELECT id_athlete, nom_athlete, prenom_athlete FROM ATHLETE";
                $statementAthleteList = $connexion->query($queryAthleteList);
                while ($rowAthlete = $statementAthleteList->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?= $rowAthlete['id_athlete']; ?>">
                        <?= htmlspecialchars($rowAthlete['nom_athlete'] . ' ' . $rowAthlete['prenom_athlete']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="idEpreuve">Épreuve :</label>
            <select name="idEpreuve" id="idEpreuve" required>
               
               <?php
                $queryEpreuveList = "SELECT id_epreuve, nom_epreuve FROM EPREUVE";
                $statementEpreuveList = $connexion->query($queryEpreuveList);
                while ($rowEpreuve = $statementEpreuveList->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?= $rowEpreuve['id_epreuve']; ?>">
                        <?= htmlspecialchars($rowEpreuve['nom_epreuve']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="resultat">Résultat :</label>
            <input type="text" name="resultat" id="resultat" required>

            <input type="submit" value="Ajouter le Résultat">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-results.php">Retour à la gestion des Résultats</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>
