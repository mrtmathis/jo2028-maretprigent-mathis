<?php
session_start();
require_once("../../../database/database.php");

// Protection CSRF
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Token CSRF invalide.";
        header('Location: ../../../index.php');
        exit();
    }
}

// Vérifiez si l'epreuveest connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Génération du token CSRF si ce n'est pas déjà fait
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère un token CSRF sécurisé
}

// Vérifiez si l'ID de l"epreuve est fourni dans l'URL
if (!isset($_GET['id_epreuve'])) {
    $_SESSION['error'] = "ID de l'epreuve manquant.";
    header("Location: manage-events.php");
    exit();
} else {
    $id_epreuve = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);

    // Vérifiez si l'ID de l'epreuve est un entier valide
    if ($id_epreuve === false) {
        $_SESSION['error'] = "ID de l'epreuve invalide.";
        header("Location:manage-events.php");
        exit();
    } else {
        try {
            // Préparez la requête SQL pour supprimer l'epreuve
            $sql = "DELETE FROM epreuve WHERE id_epreuve = :id_epreuve";
            // Exécutez la requête SQL avec le paramètre
            $statement = $connexion->prepare($sql);
            $statement->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);
            $statement->execute();

            // Message de succès
            $_SESSION['success'] = "L'epreuve a été supprimé avec succès.";

            // Redirigez vers la page précédente après la suppression
            header('Location: manage-events.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la suppression de l'epreuve : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            header('Location: manage-events.php');
            exit();
        }
    }
}

?>