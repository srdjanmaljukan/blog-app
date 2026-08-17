<?php
session_start();
require 'config/database.php';
require 'includes/auth.php';
require_admin();

$post_id = $_GET['id'] ?? null;

if ($post_id !== null) {
    // Prvo povlačimo post da znamo putanju slike (ako postoji) prije brisanja reda
    $select_stmt = $pdo->prepare("SELECT image_path FROM posts WHERE id = :id");
    $select_stmt->execute(['id' => $post_id]);
    $post = $select_stmt->fetch(PDO::FETCH_ASSOC);

    if ($post && $post['image_path'] && file_exists($post['image_path'])) {
        unlink($post['image_path']);
    }

    $delete_stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
    $delete_stmt->execute(['id' => $post_id]);
}

header('Location: index.php');
exit;