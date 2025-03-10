<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'athlète et de l'épreuve sont fournis dans l'URL
$id_athlete = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);
$id_epreuve = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);

if ($id_athlete === null || $id_epreuve === null) {
    $_SESSION['error'] = "ID de l'athlète ou de l'épreuve manquant.";
    header("Location: manage-results.php");
    exit();
}

// Vérifiez si les ID sont valides
if ($id_athlete === false || $id_epreuve === false) {
    $_SESSION['error'] = "ID de l'athlète ou de l'épreuve invalide.";
    header("Location: manage-results.php");
    exit();
}

try {
    // Préparez et exécutez la requête SQL pour supprimer le résultat
    $sql = "DELETE FROM PARTICIPER WHERE id_athlete = :id_athlete AND id_epreuve = :id_epreuve";
    $statement = $connexion->prepare($sql);
    $statement->bindParam(':id_athlete', $id_athlete, PDO::PARAM_INT);
    $statement->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);
    $statement->execute();

    // Redirigez vers la page de gestion des résultats après la suppression
    header('Location: manage-results.php');
} catch (PDOException $e) {
    $_SESSION['error'] = 'Erreur de base de données : ' . $e->getMessage();
    header("Location: manage-results.php");
}

// Afficher les erreurs en PHP (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
