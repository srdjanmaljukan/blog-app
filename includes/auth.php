<?php
// Ovaj fajl očekuje da je session_start() već pozvan u fajlu koji ga uključuje

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function is_admin(): bool
{
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

// Poziva se na vrhu stranica koje zahtijevaju prijavu (npr. add_comment.php)
function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

// Poziva se na vrhu stranica koje smiju da vide/koriste samo admini (npr. add_post.php)
function require_admin(): void
{
    if (!is_admin()) {
        header('Location: index.php');
        exit;
    }
}