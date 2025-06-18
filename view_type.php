<?php include 'db.php'; ?>

<?php
require 'init.php';
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM notes_type WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $note = $result->fetch_assoc();

    if (!$note) {
        echo "Note not found!";
        exit;
    }
} else {
    echo "Invalid request!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Note Type</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add Font Awesome for the icon -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Go Back Icon in the top-left corner (hidden here) -->
    <div class="container mt-5">
        <div class="card shadow-sm mx-auto" style="max-width: 600px;">
            <div class="card-header bg-primary text-white rounded-top d-flex align-items-center">
                <!-- Go Back Icon -->
                <a href="index.php" class="text-white me-3">
                    <i class="fas fa-arrow-left fa-lg"></i>
                </a>
                <!-- Title -->
                <h1 class="h5 mb-0"><?php echo htmlspecialchars($note['type']); ?></h1>
            </div>
            <div class="card-body">
                <p><strong>Date:</strong> <?php echo htmlspecialchars($note['date']); ?></p>
                <hr class="my-3">
                <div class="d-flex justify-content-end">
                   
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
