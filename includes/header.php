<?php
// Ovaj fajl očekuje da su session_start(), database.php i auth.php već učitani
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Blog</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <nav>
        <a href="index.php" class="logo">My Blog</a>

        <div class="nav-links">
            <?php if (is_logged_in()): ?>
                <span>Hi, <?= htmlspecialchars($_SESSION['username']) ?></span>

                <?php if (is_admin()): ?>
                    <a href="add_post.php">+ New Post</a>
                    <a href="admin_comments.php">Manage Comments</a>
                <?php endif; ?>

                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <main>