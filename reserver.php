<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: /Bibliothèque/auth/login.php');
    exit;
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /Bibliothèque/livres.php?erreur=id_invalide');
    exit;
}
$livreId = $_GET['id'];
$userId = $_SESSION['user']['id'];

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("SELECT stock, statut FROM livres WHERE id = ? FOR UPDATE");
    $stmt->execute([$livreId]);
    $livre = $stmt->fetch();

    if (!$livre || $livre['stock'] < 1 || $livre['statut'] !== 'Disponible') {
        $pdo->rollBack();
        header('Location: /Bibliothèque/livres.php?erreur=non_disponible');
        exit;
    }

    $newStock = $livre['stock'] - 1;
    $newStatus = ($newStock == 0) ? 'Réservé' : 'Disponible';

    $stmtUpdate = $pdo->prepare("UPDATE livres SET stock = ?, statut = ? WHERE id = ?");
    $stmtUpdate->execute([$newStock, $newStatus, $livreId]);
    $stmtInsert = $pdo->prepare("INSERT INTO reservations (id_livre, id_utilisateur, statut) VALUES (?, ?, 'Active')");
    $stmtInsert->execute([$livreId, $userId]);
    $pdo->commit();
    header('Location: /Bibliothèque/livres.php?succes=reservation_reussie');
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: /Bibliothèque/livres.php?erreur=transaction_echouee');
    exit;
}