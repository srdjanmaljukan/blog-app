<?php
session_start();
require 'config/database.php';
require 'includes/auth.php';
require_admin();

// Odobravanje ili brisanje komentara — obrađujemo prije prikaza liste
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = $_POST['comment_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($comment_id !== null) {
        if ($action === 'approve') {
            $approve_stmt = $pdo->prepare("UPDATE comments SET is_approved = 1 WHERE id = :id");
            $approve_stmt->execute(['id' => $comment_id]);
        } elseif ($action === 'delete') {
            $delete_stmt = $pdo->prepare("DELETE FROM comments WHERE id = :id");
            $delete_stmt->execute(['id' => $comment_id]);
        }
    }

    header('Location: admin_comments.php');
    exit;
}

// Prikazujemo samo komentare koji čekaju odobrenje
$pending_stmt = $pdo->query(
    "SELECT comments.*, users.username, posts.title AS post_title FROM comments
     JOIN users ON comments.user_id = users.id
     JOIN posts ON comments.post_id = posts.id
     WHERE is_approved = 0
     ORDER BY comments.created_at ASC"
);
$pending_comments = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);

require 'includes/header.php';
?>

<h1>Pending Comments</h1>

<?php if (count($pending_comments) === 0): ?>
    <p>No comments waiting for approval.</p>
<?php else: ?>
    <?php foreach ($pending_comments as $comment): ?>
        <div class="pending-comment">
            <p class="meta">
                <?= htmlspecialchars($comment['username']) ?>
                on <a href="post.php?id=<?= $comment['post_id'] ?>"><?= htmlspecialchars($comment['post_title']) ?></a>
            </p>
            <p><?= htmlspecialchars($comment['content']) ?></p>

            <div class="actions">
                <form method="POST" action="admin_comments.php">
                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="approve">Approve</button>
                </form>

                <form method="POST" action="admin_comments.php">
                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="reject" onclick="return confirm('Delete this comment?')">Delete</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>