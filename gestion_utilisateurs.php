<?php
session_start();
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) { exit('Accès refusé'); }
include '../includes/db.php';
include '../includes/header.php';

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if ((int)$_GET['id'] !== $_SESSION['user']['id']) {
        $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?")->execute([$_GET['id']]);
    }
    header('Location: gestion_utilisateurs.php');
    exit;
}
$users = $pdo->query("SELECT * FROM utilisateurs ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="/Bibliothèque/assets/css/admin.css">
<main class="admin-container">
    <div class="admin-header">
        <h1>Gestion des Utilisateurs</h1>
        <a href="dashboard.php" class="btn btn-back-dashboard">Retour</a>
    </div>
    <div class="table-container">
        <table class="admin-table">
            <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['nom']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= $user['is_admin'] ? '👑 Admin' : '👤 Client' ?></td>
                    <td class="actions">
                        <?php if ($_SESSION['user']['id'] != $user['id']): ?>
                        <a href="?action=delete&id=<?= $user['id'] ?>" onclick="return confirm('Supprimer cet utilisateur ?')">🗑️ Supprimer</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include '../includes/footer.php'; ?>