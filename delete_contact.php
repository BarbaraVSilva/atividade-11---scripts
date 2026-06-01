<?php
session_start();
require_once 'db.php';

// Access control: enforce active administrator session
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Read URL parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    try {
        // Execute delete transaction
        $stmt = $pdo->prepare("DELETE FROM contatos WHERE id = :id");
        $stmt->execute(['id' => $id]);

        // Redirect with success/fail state based on affected rows
        if ($stmt->rowCount() > 0) {
            header('Location: admin.php?status=deleted');
        } else {
            header('Location: admin.php?status=not_found');
        }
        exit;
    } catch (PDOException $e) {
        // Log error and redirect with error status
        error_log("Erro de exclusão: " . $e->getMessage());
        header('Location: admin.php?status=error');
        exit;
    }
} else {
    // Bad request redirect
    header('Location: admin.php');
    exit;
}
?>
