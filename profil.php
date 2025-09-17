<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: /Bibliothèque/auth/login.php'); exit; }
include 'includes/db.php';
include 'includes/header.php';
$user_id = $_SESSION['user']['id'];
$stmt = $pdo->prepare(
    "SELECT r.id, l.id as id_livre, l.titre, l.auteur, r.date_reservation, r.statut
     FROM reservations r JOIN livres l ON r.id_livre = l.id
     WHERE r.id_utilisateur = ? ORDER BY r.date_reservation DESC"
);
$stmt->execute([$user_id]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="/Bibliothèque/assets/css/profil.css">
<div class="container">
    <div class="profil-header">
        <h1>Bonjour, <?= htmlspecialchars($_SESSION['user']['nom']) ?> !</h1>
        <p>Voici l'historique de vos réservations.</p>
    </div>
    <div class="card">
        <h2>📖 Mon Historique</h2>
        <?php if (empty($reservations)) : ?>
            <p style="text-align:center;">Vous n'avez encore réservé aucun livre.</p>
        <?php else : ?>
            <table class="reservations-table">
                <thead><tr><th>Livre</th><th>Date de Réservation</th><th>Statut</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($reservations as $res) : ?>
                    <tr>
                        <td>
                            <div class="livre-info">
                                <img src="/Bibliothèque/uploads/<?= htmlspecialchars($res['image']) ?>" alt="Couverture">
                            </div>
                                    <strong><?= htmlspecialchars($res['titre']) ?></strong><br>
                                    <small>de <?= htmlspecialchars($res['auteur']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= date('d F Y', strtotime($res['date_reservation'])) ?></td>
                        <td><span class="statut-badge <?= $res['statut'] === 'Active' ? 'statut-active' : 'statut-rendu' ?>"><?= htmlspecialchars($res['statut']) ?></span></td>
                        <td>
                            <?php if ($res['statut'] == 'Active') : ?>
                                <a href="annuler_reservation_user.php?id=<?= $res['id'] ?>" class="btn btn-annuler" onclick="return confirm('Rendre ce livre ?')">Rendre</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>