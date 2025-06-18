<?php
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php'); // Redirect to the login page
    exit;
}
?>
