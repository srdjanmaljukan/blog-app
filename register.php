<?php
session_start(); // mora biti prva linija (prije bilo kakvog HTML izlaza) da bi sesije radile
require 'config/database.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($username === '' || $email === '' || $password === '') {
        $error_message = 'All fields are required.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters.';
    } else {
        // Provjeravamo da li username ili email već postoje
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $check_stmt->execute(['username' => $username, 'email' => $email]);

        if ($check_stmt->fetch()) {
            $error_message = 'Username or email already taken.';
        } else {
            // password_hash — NIKAD ne čuvamo lozinku kao čist tekst
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert_stmt = $pdo->prepare(
                "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, 'reader')"
            );
            $insert_stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $hashed_password,
            ]);

            header('Location: login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Register</h1>

    <?php if ($error_message !== ''): ?>
        <p class="error"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
    </form>

    <a href="login.php">Already have an account? Login</a>
</body>
</html>