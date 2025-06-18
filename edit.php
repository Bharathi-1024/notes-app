<?php include 'db.php'; ?>

<?php
require 'init.php';
$typeOptions = [];
$typeQuery = "SELECT DISTINCT type FROM notes_type";
$typeResult = $conn->query($typeQuery);

if ($typeResult && $typeResult->num_rows > 0) {
    while ($row = $typeResult->fetch_assoc()) {
        $typeOptions[] = $row['type'];
    }
}
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Fetch the existing note data
    $result = $conn->query("SELECT * FROM notes WHERE id = $id");
    $note = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $type = $_POST['type'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $stmt_type = $conn->prepare("SELECT id FROM notes_type WHERE type = ?");
    $stmt_type->bind_param("s", $type);
    $stmt_type->execute();
    $result = $stmt_type->get_result();
    $row = $result->fetch_assoc();
    $typeId = $row['id'];
    // Prepare the SQL statement without the date field
    $stmt = $conn->prepare("UPDATE notes SET type = ?, title = ?, description = ? WHERE id = ?");
    $stmt->bind_param("issi", $typeId, $title, $description, $id);
    $stmt->execute();
    echo "<script>
      window.parent.postMessage({ status: 'updated' }, '*');
    </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Note</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h1 class="h4 mb-0">Edit Note</h1>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($note['id']); ?>">

                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="" disabled selected>Select a type</option>
                            <?php foreach ($typeOptions as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>">
                                    <?php echo htmlspecialchars($type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($note['title']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($note['description']); ?></textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Update</button>
                        
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
