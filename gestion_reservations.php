<?php
session_start();
if (empty($_SESSION['user']['is_admin'])) { header('Location: /Bibliothèque/index.php'); exit; }
include '../includes/db.php';

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM reservations WHERE id = ?")->execute([$_GET['id']]);
    header('Location: gestion_reservations.php?message=' . urlencode('Entrée supprimée.'));
    exit;
}
$reservations = $pdo->query(
    "SELECT r.id, l.titre, u.nom as nom_client, r.date_reservation, r.statut
     FROM reservations r JOIN livres l ON r.id_livre = l.id
     JOIN utilisateurs u ON r.id_utilisateur = u.id
     ORDER BY r.date_reservation DESC"
)->fetchAll(PDO::FETCH_ASSOC);
$message = $_GET['message'] ?? '';
include '../includes/header.php';
?>
<link rel="stylesheet" href="/Bibliothèque/assets/css/admin.css">
<main class="admin-container">
    <div class="admin-header">
        <h1>Historique des Réservations</h1>
        <a href="dashboard.php" class="btn btn-back-dashboard">Retour</a>
    </div>
    <?php if ($message): ?><div class="message-banner success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <div class="table-container">
        <table class="admin-table">
            <thead><tr><th>Livre</th><th>Client</th><th>Date</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($reservations)): ?>
                <tr><td colspan="5" style="text-align:center;">Aucune réservation.</td></tr>
            <?php else: ?>
                <?php foreach ($reservations as $res): ?>
                    <tr>
                        <td><?= htmlspecialchars($res['titre']) ?></td>
                        <td><?= htmlspecialchars($res['nom_client']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($res['date_reservation'])) ?></td>
                        <td><span class="status-badge <?= $res['statut'] === 'Active' ? 'active' : 'inactive' ?>"><?= htmlspecialchars($res['statut']) ?></span></td>
                        <td class="actions">
                            <?php if ($res['statut'] === 'Active'): ?>
                                <a href="annuler_reservation.php?id=<?= $res['id'] ?>" title="Marquer comme Rendu">✔️ Rendre</a>
                            <?php endif; ?>
                            <a href="?action=delete&id=<?= $res['id'] ?>" onclick="return confirm('Supprimer cette entrée ?')" title="Supprimer" style="color: #c0392b;">🗑️ Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include '../includes/footer.php'; ?>