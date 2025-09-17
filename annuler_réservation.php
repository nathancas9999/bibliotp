<?php
session_start();
if (empty($_SESSION['user']['is_admin'])) { exit('Accès refusé'); }
include '../includes/db.php';

if (isset($_GET['id'])) {
    $reservation_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT id_livre FROM reservations WHERE id = ? AND statut = 'Active'");
    $stmt->execute([$reservation_id]);
    $reservation = $stmt->fetch();
    if ($reservation) {
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE reservations SET statut = 'Rendu' WHERE id = ?")->execute([$reservation_id]);
            $pdo->prepare("UPDATE livres SET stock = stock + 1, statut = 'Disponible' WHERE id = ?")->execute([$reservation['id_livre']]);
            $pdo->commit();
        } catch (Exception $e) { $pdo->rollBack(); }
    }
}
header('Location: gestion_reservations.php');
exit;