<?php
session_start();
require 'config/database.php';
require 'includes/auth.php';
require_login(); // samo ulogovani korisnici mogu komentarisati

$post_id = $_POST['post_id'] ?? null;
$content = trim($_POST['content'] ?? '');

if ($post_id !== null && $content !== '') {
    $insert_stmt = $pdo->prepare(
        "INSERT INTO comments (post_id, user_id, content, is_approved) VALUES (:post_id, :user_id, :content, 0)"
    );
    $insert_stmt->execute([
        'post_id' => $post_id,
        'user_id' => $_SESSION['user_id'],
        'content' => $content,
    ]);
}

header('Location: post.php?id=' . $post_id);
exit;