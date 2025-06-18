<?php
$host = 'localhost';
$user = 'root';
$port = '3306';  
$password = '';
$database = 'notes_app';
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
