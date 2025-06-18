<?php
include 'db.php';
require 'init.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = isset($_POST['type']) ? trim($_POST['type']) : '';
    
    if (empty($type)) {
        $error = "All fields are required.";
    } else {
        // Check if the type already exists
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM notes_type WHERE type = ?");
        $checkStmt->bind_param("s", $type);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            // If type exists, show error message
            $error = "This type already exists.";
        } else {
            // If type doesn't exist, insert it
            $username = $_SESSION['username'];
            $stmt = $conn->prepare("INSERT INTO notes_type (type,username) VALUES (?,?)");
            $stmt->bind_param("ss", $type,$username);

            if ($stmt->execute()) {
                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        window.parent.Swal.fire({
            icon: 'success',
            title: 'Added!',
            text: 'Note type added successfully.',
            timer: 1500,
            showConfirmButton: false
        }).then(() => {
            window.parent.location.reload();
        });
    </script>";
    exit;
            } else {
                $error = "Failed to add note. Please try again.";
            }
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
    <h1 class="h4 mb-0 text-white">Add Note Type</h1>
</div>

            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="add_type.php">
                    <div class="mb-3">
                        <label for="type" class="form-label">Type:</label>
                        <input type="text" class="form-control" id="type" name="type" placeholder="Type (e.g., Personal, Work)" required>
                    </div>
                    
                    </div>
                    <div class="text-end p-3">
                        <button type="submit" class="btn btn-primary">Save</button>
              
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
