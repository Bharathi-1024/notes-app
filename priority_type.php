<?php
include 'db.php';
require 'init.php';

// Handle Add
if (isset($_POST['add_type'])) {
    $name = $_POST['name'] ?? null;
    $color = $_POST['color'] ?? null;

    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO priority_type (name, color, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $name, $color);
        $stmt->execute();
        // Refresh to show new entry
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle Edit
if (isset($_POST['edit_type'])) {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? null;
    $color = $_POST['color'] ?? null;

    if (!empty($name) && !empty($id)) {
        $stmt = $conn->prepare("UPDATE priority_type SET name = ?, color = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $color, $id);
        $stmt->execute();
        // Refresh to show changes
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM priority_type WHERE id = $id");
    // Refresh to show changes
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$types = $conn->query("SELECT * FROM priority_type ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Priority Types</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { display: flex; min-height: 100vh; background: linear-gradient(to right, #fffde7, #fff); }
        .sidebar { width: 250px; background-color: #002b5c; padding-top: 20px; color: white; box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2); }
        .sidebar a { text-decoration: none; color: #ffffffcc; font-size: 18px; padding: 12px 25px; display: block; border-radius: 4px; margin: 8px 15px; transition: all 0.3s ease-in-out; }
        .sidebar a:hover { background-color: #0061a8; color: white; }
        .sidebar a.active { background-color: #004080; color: white; font-weight: bold; }
        .content { flex-grow: 1; padding: 30px; background-color: #fff9c4; }
        h1 { color: #002b5c; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; background-color: white; border-radius: 10px; overflow: hidden; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .table thead { background-color: #004d40; color: white; }
        .table th, .table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ccc; }
        .table-striped tbody tr:nth-child(even) { background-color: #dcedc8; }
        i { color: #002b5c !important; }
        a.active { background-color: #0d6efd; color: white !important; font-weight: bold; border-radius: 5px; }
        .color-preview { 
            width: 20px; 
            height: 20px; 
            display: inline-block; 
            border-radius: 50%; 
            margin-right: 10px;
            border: 1px solid #ccc;
        }
        .sidebar .logout {
    color: #ffcdd2;
    font-size: 18px;
    padding: 12px 25px;
    display: block;
    border-radius: 4px;
    margin: 20px 15px 10px 15px;
    background-color: #b71c1c;
    text-align: center;
    transition: all 0.3s ease-in-out;
    font-weight: bold;
}

.sidebar .logout:hover {
    background-color: #f44336;
    color: white;
    text-decoration: none;
}

.sidebar .logout i {
    margin-right: 8px;
    color: #ffffffcc !important;
}

.sidebar .logout:hover i {
    color: white !important;
}
.sidebar-btn {
    background: none;
    border: none;
    color: #ffffffcc;
    font-size: 18px;
    padding: 12px 25px;
    text-align: left;
    width: 100%;
    cursor: pointer;
    transition: background 0.3s;
}

.sidebar-btn:hover {
    background-color: #004080;
    color: white;
}

.sidebar .collapse a {
    display: block;
    padding-left: 40px;
    font-size: 16px;
    color: #ffffffaa;
}

.sidebar .collapse a:hover {
    background-color: #0061a8;
    color: white;
}

    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container-fluid py-4 px-3">
    <!-- Section Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-auto">
                    <h3 class="text-primary fw-bold m-0">Manage Priority Types</h3>
                </div>
                <div class="col-md d-flex flex-wrap gap-2 justify-content-md-end">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search priority..." style="max-width: 200px;">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus"></i> Add Priority Type
                    </button>
                </div>
            </div>
        </div>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Type</th>
                <th>Color</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $types->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td>
                        <span class="color-preview" style="background-color: <?= $row['color'] ?>"></span>
                        <?= $row['color'] ?>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $row['id'] ?>)" title="Delete">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="post">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="edit_type" value="1">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Priority Type</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label>Name</label>
                                        <input type="text" name="name" class="form-control" 
                                            value="<?= htmlspecialchars($row['name']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label>Color</label>
                                        <input type="color" name="color" class="form-control form-control-color" 
                                            value="<?= htmlspecialchars($row['color']) ?>" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-success">Update</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="post">
                <input type="hidden" name="add_type" value="1">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Priority Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Color</label>
                            <input type="color" name="color" class="form-control form-control-color" value="#ffc107" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary">Add</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete this priority type?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'priority_type.php?delete_id=' + id;
            }
        });
    }
    
    document.getElementById('searchInput').addEventListener('keyup', function () {
        const input = this.value.toLowerCase();
        const rows = document.querySelectorAll('table tbody tr');
        
        rows.forEach(row => {
            const nameCell = row.querySelector('td:nth-child(3)'); // Search by Type column
            if (nameCell && nameCell.textContent.toLowerCase().includes(input)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html>