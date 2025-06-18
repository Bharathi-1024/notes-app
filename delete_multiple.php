<?php
include 'db.php';
require 'init.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
        $selectedIds = $_POST['selected_ids'];

        // Convert the array of IDs into a comma-separated string for the SQL query
        $idsToDelete = implode(',', array_map('intval', $selectedIds));

        // Delete records from the database
        $query = "DELETE FROM notes WHERE id IN ($idsToDelete)";
        if ($conn->query($query)) {
            // Redirect back to the main page with a success message
            header('Location: notes.php?message=Selected notes deleted successfully');
            exit;
        } else {
            // Redirect back with an error message
            header('Location: notes.php?error=Failed to delete selected notes');
            exit;
        }
    }
}

// If no IDs were selected, redirect back with an error message
header('Location: notes.php?error=No notes selected for deletion');
exit;
?>
