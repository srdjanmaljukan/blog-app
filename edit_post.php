<?php
session_start();
require 'config/database.php';
require 'includes/auth.php';
require_admin();

$post_id = $_GET['id'] ?? null;

if ($post_id === null) {
    header('Location: index.php');
    exit;
}

$select_stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
$select_stmt->execute(['id' => $post_id]);
$post = $select_stmt->fetch(PDO::FETCH_ASSOC);

if ($post === false) {
    header('Location: index.php');
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $image_path = $post['image_path']; // podrazumjevano zadržavamo staru sliku

    if ($title === '' || $content === '') {
        $error_message = 'Title and content are required.';
    } else {
        // Nova slika je opciona pri editu — samo ako korisnik izabere novi fajl
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            $file_type = $_FILES['image']['type'];

            if (!in_array($file_type, $allowed_types)) {
                $error_message = 'Only JPEG, PNG, or WEBP images are allowed.';
            } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $error_message = 'Image must be smaller than 5MB.';
            } else {
                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('post_', true) . '.' . $extension;
                $destination = 'uploads/' . $filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    // Brišemo staru sliku sa diska da se ne gomilaju nekorišćeni fajlovi
                    if ($post['image_path'] && file_exists($post['image_path'])) {
                        unlink($post['image_path']);
                    }
                    $image_path = $destination;
                } else {
                    $error_message = 'Failed to upload image.';
                }
            }
        }

        if ($error_message === '') {
            $update_stmt = $pdo->prepare(
                "UPDATE posts SET title = :title, content = :content, image_path = :image_path WHERE id = :id"
            );
            $update_stmt->execute([
                'title' => $title,
                'content' => $content,
                'image_path' => $image_path,
                'id' => $post_id,
            ]);

            header('Location: post.php?id=' . $post_id);
            exit;
        }
    }
}

$title_value = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $title : $post['title'];
$content_value = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $content : $post['content'];

require 'includes/header.php';
?>

<h1>Edit Post</h1>

<?php if ($error_message !== ''): ?>
    <p class="error"><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>

<form method="POST" action="edit_post.php?id=<?= $post['id'] ?>" enctype="multipart/form-data">
    <input type="text" name="title" value="<?= htmlspecialchars($title_value) ?>" required>
    <textarea name="content" rows="10" required><?= htmlspecialchars($content_value) ?></textarea>

    <?php if ($post['image_path']): ?>
        <p>Current image:</p>
        <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="" class="current-image">
    <?php endif; ?>

    <label>
        Replace image (optional)
        <input type="file" name="image" accept="image/*">
    </label>

    <button type="submit">Save Changes</button>
</form>

<?php require 'includes/footer.php'; ?>