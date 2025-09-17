<?php
session_start();
require_once('includes/db.php');
include 'includes/header.php';
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM livres";
$params = [];
if ($search) {
    $sql .= " WHERE (titre LIKE ? OR auteur LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY titre ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$livres = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="/Bibliothèque/assets/css/index.css">
<link rel="stylesheet" href="/Bibliothèque/assets/css/catalogue.css">

<div class="container">
    <div class="catalogue-header">
        <h1>Catalogue des Livres</h1>
    </div>
    
    <form method="GET" class="search-bar">
        <input type="text" name="search" placeholder="Rechercher un titre ou un auteur..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn">Rechercher</button>
    </form>

    <div class="book-grid">
        <?php if (empty($livres)) : ?>
            <p class="no-results">Aucun livre ne correspond à votre recherche.</p>
        <?php else : ?>
            <?php foreach ($livres as $livre) : ?>
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
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>