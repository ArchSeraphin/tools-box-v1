<?php
session_start();

// Database connection
require_once dirname(__DIR__) . '/config/db.php';

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function redirect($url)
{
    header("Location: $url");
    exit;
}

// Helper to escape output
function h($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>