<?php
session_start();
if (empty($_SESSION['user']['is_admin'])) { exit('Accès refusé'); }
include '../includes/db.php';

if (isset($_GET['id'])) {
    $reservation_id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT id_livre FROM reservations WHERE id = ?");
    $stmt->execute([$reservation_id]);
    $reservation = $stmt->fetch();

    if ($reservation) {
        $pdo->beginTransaction();
        try {
            // Mettre à jour le statut de la réservation
            $pdo->prepare("UPDATE reservations SET statut = 'Rendu' WHERE id = ?")->execute([$reservation_id]);
            // Rendre le livre à nouveau disponible
            $pdo->prepare("UPDATE livres SET statut = 'Disponible' WHERE id = ?")->execute([$reservation['id_livre']]);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
        }
    }
}
header('Location: gestion_reservations.php');
exit;