<?php
session_start();
require 'config/database.php';
require 'includes/auth.php';

$post_id = $_GET['id'] ?? null;

if ($post_id === null) {
    header('Location: index.php');
    exit;
}

$post_stmt = $pdo->prepare(
    "SELECT posts.*, users.username FROM posts
     JOIN users ON posts.user_id = users.id
     WHERE posts.id = :id"
);
$post_stmt->execute(['id' => $post_id]);
$post = $post_stmt->fetch(PDO::FETCH_ASSOC);

if ($post === false) {
    header('Location: index.php');
    exit;
}

// Komentari: obični korisnici vide samo odobrene, admin vidi sve
// (admin treba da vidi neodobrene da bi znao šta čeka na moderaciju)
if (is_admin()) {
    $comments_stmt = $pdo->prepare(
        "SELECT comments.*, users.username FROM comments
         JOIN users ON comments.user_id = users.id
         WHERE post_id = :post_id
         ORDER BY comments.created_at ASC"
    );
} else {
    $comments_stmt = $pdo->prepare(
        "SELECT comments.*, users.username FROM comments
         JOIN users ON comments.user_id = users.id
         WHERE post_id = :post_id AND is_approved = 1
         ORDER BY comments.created_at ASC"
    );
}
$comments_stmt->execute(['post_id' => $post_id]);
$comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);

require 'includes/header.php';
?>

<article class="post-full">
    <?php if ($post['image_path']): ?>
        <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="">
    <?php endif; ?>

    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <p class="meta">by <?= htmlspecialchars($post['username']) ?> on <?= date('M j, Y', strtotime($post['created_at'])) ?></p>

    <div class="content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>

    <?php if (is_admin()): ?>
        <div class="admin-actions">
            <a href="edit_post.php?id=<?= $post['id'] ?>">Edit</a>
            <a href="delete_post.php?id=<?= $post['id'] ?>" onclick="return confirm('Delete this post?')">Delete</a>
        </div>
    <?php endif; ?>
</article>

<section class="comments">
    <h2>Comments</h2>

    <?php if (count($comments) === 0): ?>
        <p>No comments yet.</p>
    <?php else: ?>
        <?php foreach ($comments as $comment): ?>
            <div class="comment <?= !$comment['is_approved'] ? 'pending' : '' ?>">
                <p class="meta">
                    <?= htmlspecialchars($comment['username']) ?>
                    <?php if (!$comment['is_approved']): ?>
                        <span class="badge">Pending approval</span>
                    <?php endif; ?>
                </p>
                <p><?= htmlspecialchars($comment['content']) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (is_logged_in()): ?>
        <form method="POST" action="add_comment.php">
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
            <textarea name="content" placeholder="Write a comment..." rows="3" required></textarea>
            <button type="submit">Post Comment</button>
        </form>
    <?php else: ?>
        <p><a href="login.php">Login</a> to leave a comment.</p>
    <?php endif; ?>
</section>

<?php require 'includes/footer.php'; ?>