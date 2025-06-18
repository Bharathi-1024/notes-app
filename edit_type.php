<?php
include 'db.php';
require 'init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $type = trim($_POST['type'] ?? '');

    if (!empty($id) && !empty($type)) {
        $stmt = $conn->prepare("UPDATE notes_type SET type = ? WHERE id = ?");
        $stmt->bind_param("si", $type, $id);

        if ($stmt->execute()) {
            echo "<script>
                window.parent.Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'Note type updated successfully.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.parent.location.reload();
                });
            </script>";
            exit;
        } else {
            $error = "Update failed.";
        }
    } else {
        $error = "All fields required.";
    }
}

// GET existing data
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM notes_type WHERE id = $id");
    $note = $result->fetch_assoc();
    $type = $note['type'] ?? '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Note Type</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4>Edit Note Type</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <input type="text" class="form-control" name="type" value="<?= htmlspecialchars($type) ?>" required>
                    </div>
                     <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
