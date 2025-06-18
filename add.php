<?php
include 'db.php';
require 'init.php';

$typeOptions = [];
$typeQuery = "SELECT DISTINCT type FROM notes_type";
$typeResult = $conn->query($typeQuery);

if ($typeResult && $typeResult->num_rows > 0) {
    while ($row = $typeResult->fetch_assoc()) {
        $typeOptions[] = $row['type'];
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = isset($_POST['type']) ? trim($_POST['type']) : '';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    if (empty($type) || empty($title) || empty($description)) {
        $error = "All fields are required.";
    } else {
        // Fetch the id of the selected type
        $stmt = $conn->prepare("SELECT id FROM notes_type WHERE type = ?");
        $stmt->bind_param("s", $type);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $typeId = $row['id'];
            $username = $_SESSION['username'];
            
            // Insert the note with the fetched type id
            $stmt = $conn->prepare("INSERT INTO notes (type, title, description,username,date) VALUES (?, ?, ?,?, NOW())");
            $stmt->bind_param("isss", $typeId, $title, $description, $username);


            

            if ($stmt->execute()) {
               echo "<script>
        window.parent.postMessage('note_added', '*');
    </script>";
    exit;
            } else {
                $error = "Failed to add note. Please try again.";
            }
        } else {
            $error = "Invalid type selected.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Note</title>
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
    .custom-header {
    background: linear-gradient(to right, #007bff, #0056b3); /* Blue gradient */
    color: white;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    padding: 15px 20px;
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
<body>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header text-white">
           <div class="card-header custom-header">
    <h1 class="h4 mb-0 text-white">Add Note</h1>
</div>




            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="add.php">
                    <div class="mb-3">
                        <label for="type" class="form-label">Type:</label>
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
                        <label for="title" class="form-label">Title:</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description:</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Description" required></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" onclick="closeParentModal()">Cancel</button>

<script>
function closeParentModal() {
    window.parent.postMessage('close_add_modal', '*');
}
</script>

                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
