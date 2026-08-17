<?php
session_start();
require 'config/database.php';
require 'includes/auth.php';

$posts_stmt = $pdo->query(
    "SELECT posts.*, users.username FROM posts
     JOIN users ON posts.user_id = users.id
     ORDER BY posts.created_at DESC"
);
$posts = $posts_stmt->fetchAll(PDO::FETCH_ASSOC);

require 'includes/header.php';
?>

<h1>Latest Posts</h1>

<?php if (count($posts) === 0): ?>
    <p>No posts yet.</p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <article class="post-preview">
            <?php if ($post['image_path']): ?>
                <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="">
            <?php endif; ?>

            <h2><a href="post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
            <p class="meta">by <?= htmlspecialchars($post['username']) ?> on <?= date('M j, Y', strtotime($post['created_at'])) ?></p>
        </article>
    <?php endforeach; ?>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>