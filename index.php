<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';
$nouveautes = $pdo->query("SELECT * FROM livres WHERE statut = 'Disponible' ORDER BY id DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="/Bibliothèque/assets/css/index.css">

<div class="container">
    <section class="hero">
        <h1>Bienvenue à la Bibliothèque en Ligne</h1>
        <p>Découvrez, réservez et plongez dans un monde de lectures.</p>
        <a href="/Bibliothèque/livres.php" class="btn">Explorer le catalogue</a>
    </section>

    <h2 class="section-title">Nos Derniers Ajouts</h2>
    <div class="book-grid">
        <?php foreach ($nouveautes as $livre) : ?>
            <div class="book-card">
                 <a href="livre.php?id=<?= $livre['id'] ?>">

                <div class="book-cover-container">
                    <img src="/Bibliothèque/uploads/<?= htmlspecialchars($livre['image']) ?>" alt="Couverture de <?= htmlspecialchars($livre['titre']) ?>">
                </div>

                </a>
                <div class="book-info">
                    <h3><?= htmlspecialchars($livre['titre']) ?></h3>
                    <p>Par <?= htmlspecialchars($livre['auteur']) ?></p>
                    <a href="/Bibliothèque/livre.php?id=<?= $livre['id'] ?>" class="btn">Voir les détails</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>