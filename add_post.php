<?php
session_start();
require 'config/database.php';
require 'includes/auth.php';
require_admin();

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $image_path = null;

    if ($title === '' || $content === '') {
        $error_message = 'Title and content are required.';
    } else {
        // Obrada upload-a slike, samo ako je fajl poslat
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            $file_type = $_FILES['image']['type'];

            if (!in_array($file_type, $allowed_types)) {
                $error_message = 'Only JPEG, PNG, or WEBP images are allowed.';
            } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) { // 5MB limit
                $error_message = 'Image must be smaller than 5MB.';
            } else {
                // Generišemo jedinstveno ime fajla da izbjegnemo konflikte/prepisivanje
                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('post_', true) . '.' . $extension;
                $destination = 'uploads/' . $filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $image_path = $destination;
                } else {
                    $error_message = 'Failed to upload image.';
                }
            }
        }

        if ($error_message === '') {
            $insert_stmt = $pdo->prepare(
                "INSERT INTO posts (user_id, title, content, image_path) VALUES (:user_id, :title, :content, :image_path)"
            );
            $insert_stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'title' => $title,
                'content' => $content,
                'image_path' => $image_path,
            ]);

            header('Location: index.php');
            exit;
        }
    }
}

require 'includes/header.php';
?>

<h1>New Post</h1>

<?php if ($error_message !== ''): ?>
    <p class="error"><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>

<form method="POST" action="add_post.php" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Post title" required>
    <textarea name="content" placeholder="Write your post..." rows="10" required></textarea>
    <label>
        Image (optional)
        <input type="file" name="image" accept="image/*">
    </label>
    <button type="submit">Publish</button>
</form>

<?php require 'includes/footer.php'; ?>