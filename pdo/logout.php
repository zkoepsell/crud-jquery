<?php
require_once "pdo.php";
session_start();

// If the user requested logout go back to index.php
if (isset($_SESSION['name'])) {
    session_start();
    unset($_SESSION['name']);
    unset($_SESSION['user_id']);
    session_destroy();
    header('Location: index.php');
    return;
}
