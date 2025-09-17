<?php
session_start();
if (empty($_SESSION['user']['is_admin'])) { header('Location: /Bibliothèque/index.php'); exit; }
include '../includes/db.php';
$message = null; $error = null;

// --- PARTIE 1 : Gérer le téléversement d'une nouvelle image ---
if (isset($_POST['upload_image'])) {
    if (isset($_FILES['nouvelle_image']) && $_FILES['nouvelle_image']['error'] == 0) {
        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
        
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['nouvelle_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowedExts)) {
            $file_name = uniqid() . '_' . basename($_FILES['nouvelle_image']['name']);
            if (move_uploaded_file($_FILES['nouvelle_image']['tmp_name'], $upload_dir . $file_name)) {
                $message = "Image téléversée avec succès.";
            } else { $error = "Erreur de téléversement. Vérifiez les permissions du dossier 'uploads'."; }
        } else { $error = "Format de fichier non autorisé."; }
    } else { $error = "Aucun fichier ou erreur lors de l'envoi."; }
    header('Location: gestion_livres.php?message=' . urlencode($message ?? '') . '&error=' . urlencode($error ?? ''));
    exit;
}

// --- PARTIE 2 : Ajouter ou Modifier un livre ---
if (isset($_POST['save_livre'])) {
    $id = $_POST['id'] ?? null;
    $fields = ['titre', 'auteur', 'date_publication', 'genre', 'image', 'statut', 'synopsis', 'stock'];
    foreach($fields as $field) { $$field = $_POST[$field]; }
    if ($id) {
        $sql = "UPDATE livres SET titre=?, auteur=?, date_publication=?, genre=?, image=?, statut=?, synopsis=?, stock=? WHERE id=?";
        $params = [$titre, $auteur, $date_publication, $genre, $image, $statut, $synopsis, $stock, $id];
        $message = "Livre modifié.";
    } else {
        $sql = "INSERT INTO livres (titre, auteur, date_publication, genre, image, statut, synopsis, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [$titre, $auteur, $date_publication, $genre, $image, $statut, $synopsis, $stock];
        $message = "Livre ajouté.";
    }
    $pdo->prepare($sql)->execute($params);
    header('Location: gestion_livres.php?message=' . urlencode($message));
    exit;
}

// --- PARTIE 3 : Supprimer un livre ---
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM livres WHERE id = ?")->execute([$_GET['delete']]);
    header('Location: gestion_livres.php?message=' . urlencode('Livre supprimé.'));
    exit;
}

// --- PARTIE 4 : Récupération des données pour l'affichage ---
$livres = $pdo->query("SELECT * FROM livres ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$images = array_diff(scandir('../uploads/'), ['.', '..']);
$livre_a_modifier = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM livres WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $livre_a_modifier = $stmt->fetch(PDO::FETCH_ASSOC);
}
$message_from_url = $_GET['message'] ?? '';
$error_from_url = $_GET['error'] ?? '';
?>
<?php include '../includes/header.php'; ?>
<link rel="stylesheet" href="/Bibliothèque/assets/css/admin.css">
<main class="admin-container">
    <div class="admin-header"><h1>📚 Gestion des Livres</h1><a href="dashboard.php" class="btn btn-back-dashboard">Retour</a></div>
    <?php if ($message_from_url): ?><div class="message-banner success"><?= htmlspecialchars($message_from_url) ?></div><?php endif; ?>
    <?php if ($error_from_url): ?><div class="message-banner error"><?= htmlspecialchars($error_from_url) ?></div><?php endif; ?>
    <div class="admin-grid">
        <div class="table-container">
            <h3>Catalogue Actuel</h3>
            <table class="admin-table">
                <thead><tr><th>Couverture</th><th>Titre</th><th>Auteur</th><th>Stock</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($livres as $livre): ?>
                    <tr>
                        <td><img src="/Bibliothèque/uploads/<?= htmlspecialchars($livre['image']) ?>" alt="Couverture"></td>
                        <td><?= htmlspecialchars($livre['titre']) ?></td>
                        <td><?= htmlspecialchars($livre['auteur']) ?></td>
                        <td><strong><?= htmlspecialchars($livre['stock']) ?></strong></td>
                        <td class="actions">
                            <a href="?edit=<?= $livre['id'] ?>#form-livre" title="Modifier">✏️</a>
                            <a href="?delete=<?= $livre['id'] ?>" onclick="return confirm('Supprimer ce livre ?')" title="Supprimer">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <aside class="admin-form-container" id="form-livre">
            <h2><?= $livre_a_modifier ? 'Modifier le livre' : 'Ajouter un livre' ?></h2>
            <div class="card-section">
                <h4>1. Téléverser une couverture</h4>
                <form method="POST" enctype="multipart/form-data" class="upload-form"><input type="file" name="nouvelle_image" required><button type="submit" name="upload_image" class="btn">Envoyer</button></form>
            </div>
            <form method="POST" class="admin-form">
                <input type="hidden" name="id" value="<?= $livre_a_modifier['id'] ?? '' ?>">
                <div class="form-group"><label>Titre</label><input type="text" name="titre" value="<?= htmlspecialchars($livre_a_modifier['titre'] ?? '') ?>" required></div>
                <div class="form-group"><label>Auteur</label><input type="text" name="auteur" value="<?= htmlspecialchars($livre_a_modifier['auteur'] ?? '') ?>" required></div>
                <div class="form-group"><label>2. Choisir une couverture</label><input type="text" name="image" id="imageInput" value="<?= htmlspecialchars($livre_a_modifier['image'] ?? 'default_cover.jpg') ?>" required readonly></div>
                <div class="image-gallery">
                    <?php foreach ($images as $img): ?>
                    <div class="thumb" onclick="selectImage('<?= $img ?>', this)"><img src="/Bibliothèque/uploads/<?= $img ?>"><span><?= htmlspecialchars(substr($img, 14, 10)).'...' ?></span></div>
                    <?php endforeach; ?>
                </div>
                <div class="form-group"><label>Synopsis</label><textarea name="synopsis" rows="4"><?= htmlspecialchars($livre_a_modifier['synopsis'] ?? '') ?></textarea></div>
                <div class="form-group"><label>Stock</label><input type="number" name="stock" value="<?= htmlspecialchars($livre_a_modifier['stock'] ?? 1) ?>" min="0"></div>
                <div class="form-group"><label>Genre</label><input type="text" name="genre" value="<?= htmlspecialchars($livre_a_modifier['genre'] ?? '') ?>"></div>
                <div class="form-group"><label>Date de publication</label><input type="date" name="date_publication" value="<?= htmlspecialchars($livre_a_modifier['date_publication'] ?? '') ?>"></div>
                <div class="form-group"><label>Statut</label>
                    <select name="statut">
                        <option value="Disponible" <?= ($livre_a_modifier['statut'] ?? '') == 'Disponible' ? 'selected' : '' ?>>Disponible</option>
                        <option value="Réservé" <?= ($livre_a_modifier['statut'] ?? '') == 'Réservé' ? 'selected' : '' ?>>Réservé</option>
                    </select>
                </div>
                <button type="submit" name="save_livre" class="btn-submit">3. Enregistrer</button>
            </form>
        </aside>
    </div>
</main>
<script>
function selectImage(filename, element) {
    document.getElementById('imageInput').value = filename;
    document.querySelectorAll('.thumb').forEach(thumb => thumb.classList.remove('selected'));
    element.classList.add('selected');
}
</script>
<?php include '../includes/footer.php'; ?>