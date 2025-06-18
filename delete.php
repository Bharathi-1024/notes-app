<?php include 'db.php'; ?>

<?php
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("DELETE FROM notes WHERE id = $id");
    header("Location: notes.php");
}
?>
