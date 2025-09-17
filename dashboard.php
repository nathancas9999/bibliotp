<?php
session_start();
if (empty($_SESSION['user']['is_admin'])) { header('Location: /Bibliothèque/index.php'); exit; }
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $confirm_text = $_POST['confirm_text'] ?? '';
    if ($confirm_text === 'SUPPRIMER') {
        if ($action === 'delete_all_reservations') { $pdo->query("TRUNCATE TABLE reservations"); }
        if ($action === 'delete_all_users') { $pdo->prepare("DELETE FROM utilisateurs WHERE id != ?")->execute([$_SESSION['user']['id']]); }
        if ($action === 'delete_all_books') { $pdo->query("TRUNCATE TABLE livres"); }
        header('Location: dashboard.php?message=action_ok');
        exit;
    } else {
        header('Location: dashboard.php?error=confirmation_failed');
        exit;
    }
}
include '../includes/header.php';
?>
<link rel="stylesheet" href="/Bibliothèque/assets/css/admin.css">
<main class="admin-container">
    <div class="admin-header"><h1>Panel d'Administration</h1></div>
    <p>Bienvenue dans l'espace de gestion de la bibliothèque.</p>
    <div class="dashboard-grid">
        <a href="gestion_livres.php" class="dashboard-card">Gérer les Livres</a>
        <a href="gestion_utilisateurs.php" class="dashboard-card">Gérer les Utilisateurs</a>
        <a href="gestion_reservations.php" class="dashboard-card">Voir les Réservations</a>
    </div>
    <div class="danger-zone">
        <h2>⚠️ Zone de Danger</h2>
        <p>Les actions ci-dessous sont irréversibles. Pour confirmer, écrivez "SUPPRIMER".</p>
        <form method="POST" onsubmit="return confirm('Êtes-vous certain ?');"><div class="danger-action"><span>Vider l'historique des réservations.</span><input type="hidden" name="action" value="delete_all_reservations"><input type="text" name="confirm_text" placeholder="SUPPRIMER" required><button type="submit" class="btn-danger">Vider</button></div></form>
        <form method="POST" onsubmit="return confirm('Êtes-vous certain ?');"><div class="danger-action"><span>Supprimer TOUS les utilisateurs (sauf vous).</span><input type="hidden" name="action" value="delete_all_users"><input type="text" name="confirm_text" placeholder="SUPPRIMER" required><button type="submit" class="btn-danger">Supprimer</button></div></form>
        <form method="POST" onsubmit="return confirm('Êtes-vous certain ?');"><div class="danger-action"><span>Supprimer TOUS les livres du catalogue.</span><input type="hidden" name="action" value="delete_all_books"><input type="text" name="confirm_text" placeholder="SUPPRIMER" required><button type="submit" class="btn-danger">Supprimer</button></div></form>
    </div>
</main>
<?php include '../includes/footer.php'; ?>