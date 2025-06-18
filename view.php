<?php include 'db.php'; ?>

<?php
require 'init.php';
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM notes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $note = $result->fetch_assoc();
    $typeStmt = $conn->prepare("SELECT type FROM notes_type WHERE id = ?");
    $typeStmt->bind_param("i", $note['type']); 
    $typeStmt->execute();

    $typeResult = $typeStmt->get_result();
    $typeValue = $typeResult->fetch_assoc();
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
    <title>View Note</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add Font Awesome for the icon -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        display: flex;
        min-height: 100vh;
        background: linear-gradient(to right, #fffde7, #fff);
    }

    .sidebar {
        width: 250px;
        background-color: #002b5c; /* Dark Blue */
        padding-top: 20px;
        color: white;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
    }

    .sidebar a {
        text-decoration: none;
        color: #ffffffcc;
        font-size: 18px;
        padding: 12px 25px;
        display: block;
        border-radius: 4px;
        margin: 8px 15px;
        transition: all 0.3s ease-in-out;
    }

    .sidebar a:hover {
        background-color: #0061a8;
        color: white;
    }

    .sidebar a.active {
        background-color: #004080;
        color: white;
        font-weight: bold;
    }

    .content {
        flex-grow: 1;
        padding: 30px;
        background-color: #fff9c4; /* Light Yellow */
    }

    h1 {
        color: #002b5c;
        margin-bottom: 20px;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        background-color: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .table thead {
        background-color: #004d40; /* Dark Green */
        color: white;
    }

    .table th, .table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ccc;
    }

    .table-striped tbody tr:nth-child(even) {
        background-color: #dcedc8; /* Light Green */
    }

    i {
        color: #002b5c !important;
    }
</style>

</head>
<body class="bg-light">

    <!-- Go Back Icon in the top-left corner (hidden here) -->
    <div class="container mt-5">
        <div class="card shadow-sm mx-auto" style="max-width: 600px;">
            <div class="card-header bg-primary text-white rounded-top d-flex align-items-center">
                <!-- Go Back Icon -->
                <a href="#" onclick="closeParentModal()" class="text-white me-3">
    <i class="fas fa-arrow-left fa-lg"></i>
</a>

<script>
function closeParentModal() {
    // This sends a message to the main page to close the modal
    window.parent.postMessage('close_view_modal', '*');
}
</script>

                <!-- Title -->
                <h1 class="h5 mb-0"><?php echo htmlspecialchars($note['title']); ?></h1>
            </div>
            <div class="card-body">
                <p><strong>Date:</strong> <?php echo htmlspecialchars($note['date']); ?></p>
                <hr class="my-3">
                <p><strong>Type:</strong> <?php echo htmlspecialchars($typeValue['type']); ?></p>
                <hr class="my-3">
                <p><strong>Description:</strong></p>
                <p class="text-muted"><?php echo nl2br(htmlspecialchars($note['description'])); ?></p>
                <hr class="my-3">
                <div class="d-flex justify-content-end">
                  

<script>
function closeParentModal() {
    window.parent.postMessage('close_view_modal', '*');
}
</script>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
