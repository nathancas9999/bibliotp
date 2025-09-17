<?php
session_start();
include 'includes/db.php';
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header('Location: index.php'); exit; }
$livre_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM livres WHERE id = ?");
$stmt->execute([$livre_id]);
$livre = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$livre) { header('Location: livres.php?erreur=not_found'); exit; }
include 'includes/header.php';
?>
<link rel="stylesheet" href="/Bibliothèque/assets/css/livre-detail.css">

<div class="container">
    <div class="livre-detail-grid">
        <div class="livre-cover">
            <img src="/Bibliothèque/uploads/<?= htmlspecialchars($livre['image']) ?>" alt="Couverture de <?= htmlspecialchars($livre['titre']) ?>">
        </div>
        <div class="livre-infos">
            <h1><?= htmlspecialchars($livre['titre']) ?></h1>
            <h2 class="auteur">par <?= htmlspecialchars($livre['auteur']) ?></h2>
            <div class="meta-info">
                <span><strong>Genre :</strong> <?= htmlspecialchars($livre['genre']) ?></span>
                <span><strong>Publié le :</strong> <?= date('d F Y', strtotime($livre['date_publication'])) ?></span>
            </div>
            <div class="synopsis">
                <h3>Synopsis</h3>
                <p><?= nl2br(htmlspecialchars($livre['synopsis'] ?? 'Aucun synopsis disponible.')) ?></p>
            </div>
            <div class="disponibilite">
                <?php if ($livre['stock'] > 0): ?>
                    <span class="stock-dispo">✅ En stock (<?= $livre['stock'] ?> exemplaire(s))</span>
                    <a href="/Bibliothèque/reserver.php?id=<?= $livre['id'] ?>" class="btn">Réserver ce livre</a>
                <?php else: ?>
                    <span class="stock-epuise">❌ Hors stock pour le moment</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>