<?php
include 'db.php';
require 'init.php';
$viewMode = isset($_GET['view']) ? $_GET['view'] : 'table'; // OLD
if (isset($_GET['delete_id'])) {
    $deleteId = $conn->real_escape_string($_GET['delete_id']);

    // Check if there are any notes using this type
    $checkQuery = "SELECT COUNT(*) AS count FROM notes WHERE type = '$deleteId'";
    $checkResult = $conn->query($checkQuery);
    $checkRow = $checkResult->fetch_assoc();
    
    if ($checkRow['count'] > 0) {
        // Display a message if there are notes using this type
        echo "<script>alert('Cannot delete this type as one or more notes are associated with it.'); window.location.href = 'type.php';</script>";
        exit;
    }

    // Proceed with deletion if no notes are associated
    $deleteQuery = "DELETE FROM notes_type WHERE id = '$deleteId'";
    if ($conn->query($deleteQuery)) {
        echo "<script>window.location.href = 'type.php';</script>";
    } else {
        echo "<script>alert('Error deleting record'); window.location.href = 'type.php';</script>";
    }
    exit;
}

// Handle Bulk Delete Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_ids'])) {
    $selectedIds = $_POST['selected_ids'];
    $escapedIds = array_map(fn($id) => $conn->real_escape_string($id), $selectedIds);
    $idList = implode(',', $escapedIds);

    // Check if any selected ids are being referenced in the notes table
    $checkQuery = "SELECT COUNT(*) AS count FROM notes WHERE type IN ($idList)";
    $checkResult = $conn->query($checkQuery);
    $checkRow = $checkResult->fetch_assoc();

    if ($checkRow['count'] > 0) {
        // Display a message if there are notes using any of the selected types
        echo "<script>alert('Cannot delete one or more types as notes are associated with them.'); window.location.href = 'type.php';</script>";
        exit;
    }

    // Proceed with bulk deletion if no notes are associated
    $bulkDeleteQuery = "DELETE FROM notes_type WHERE id IN ($idList)";
    if ($conn->query($bulkDeleteQuery)) {
        echo "<script>window.location.href = 'type.php';</script>";
    } else {
        echo "<script>alert('Error deleting records'); window.location.href = 'type.php';</script>";
    }
    exit;
}

$typeOptions = [];
$typeQuery = "SELECT DISTINCT type FROM notes_type";
$typeResult = $conn->query($typeQuery);

if ($typeResult && $typeResult->num_rows > 0) {
    while ($row = $typeResult->fetch_assoc()) {
        $typeOptions[] = $row['type'];
    }
}


// Fetch GET parameters for filtering
$slNoFilter = isset($_GET['slNo']) ? $_GET['slNo'] : '';  // We use this as the filter for ID only
$titleFilter = isset($_GET['title']) ? $_GET['title'] : '';
$typeFilter = isset($_GET['type']) ? $_GET['type'] : '';
$dateFilter = isset($_GET['date']) ? $_GET['date'] : '';

// Pagination setup
$rowsPerPage = isset($_GET['rowsPerPage']) ? (int)$_GET['rowsPerPage'] : 10;
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Calculate offset
$offset = ($currentPage - 1) * $rowsPerPage;

// Prepare base query with filters
$baseQuery = "SELECT * FROM notes_type WHERE 1=1";

// Filter by ID (Sl. No)
if (!empty($slNoFilter)) {
    $baseQuery .= " AND id = " . (int)$slNoFilter;  // Only match by exact ID (int)
}

if (!empty($typeFilter)) {
    $baseQuery .= " AND type LIKE '%" . $conn->real_escape_string($typeFilter) . "%'";
}

// Filter by Date (ignoring time part)
if (!empty($dateFilter)) {
    $baseQuery .= " AND DATE(date) = '" . $conn->real_escape_string($dateFilter) . "'";
}

$totalPages = 1;

// Get total records for pagination
$countQuery = "SELECT COUNT(*) AS count FROM notes_type WHERE 1=1";
if (!empty($slNoFilter)) {
    $countQuery .= " AND id = " . (int)$slNoFilter; // Count filter by exact ID
}
if (!empty($typeFilter)) {
    $countQuery .= " AND type LIKE '%" . $conn->real_escape_string($typeFilter) . "%'";
}
if (!empty($dateFilter)) {
    $countQuery .= " AND DATE(date) = '" . $conn->real_escape_string($dateFilter) . "'";
}
$totalRecordsResult = $conn->query($countQuery);
$totalRecords = $totalRecordsResult->fetch_assoc()['count'] ?? 0;

// Calculate total pages
$totalPages = ceil($totalRecords / $rowsPerPage);

// Final paginated query
$query = $baseQuery . " ORDER BY id ASC LIMIT $offset, $rowsPerPage";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes App</title>
    <!-- Add SweetAlert2 script if not already added -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

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
    
label[for="rowsPerPage"] {
  white-space: nowrap;
}



    i {
        color: #002b5c !important;
    }
    a.active {
    background-color: #0d6efd;
    color: white !important;
    font-weight: bold;
    border-radius: 5px;
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

</style>


</head>
<body class="bg-light">
<?php include 'header.php'; ?>
  <div class="container mt-4">
       <div class="d-flex align-items-center justify-content-between ms-4 mb-4">
    <h1 class="mb-0">Note types</h1>
</div>
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this record?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Div 2 -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <form method="GET" action="" class="d-flex align-items-center gap-2 w-100">
                <input type="text" name="slNo" class="form-control w-auto" placeholder="Search by ID"
                    value="<?php echo htmlspecialchars($slNoFilter); ?>">
                <input type="date" name="date" class="form-control w-auto" placeholder="Date"
                    value="<?php echo htmlspecialchars($dateFilter); ?>">
                <select name="type" class="form-select w-auto">
                    <option value="">Select Type</option>
                    <?php foreach ($typeOptions as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= $typeFilter == $type ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-success" title="Search">
                    <i class="bi bi-search"></i>
                </button>
                <a href="type.php" class="btn btn-light" title="Clear Filters">
                    <i class="bi bi-x-circle"></i>
                </a>
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTypeModal">
    Add
</button>

                

                
            </form>
            <form method="GET" action="" class="d-flex align-items-center">
                <label for="rowsPerPage" class="me-2 mb-0">Rows per page:</label>
                <select name="rowsPerPage" id="rowsPerPage" class="form-select w-auto" onchange="this.form.submit()">
                    <?php foreach ([10, 20, 25] as $option): ?>
                        <option value="<?= $option ?>" <?= $rowsPerPage == $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

          <div class="d-flex justify-content-end mb-3">
            <div class="btn-group">
                <a href="?view=table<?= isset($_GET['page']) ? '&page=' . $_GET['page'] : '' ?><?= isset($_GET['rowsPerPage']) ? '&rowsPerPage=' . $_GET['rowsPerPage'] : '' ?>" 
                class="btn btn-outline-primary <?= $viewMode === 'table' ? 'active' : '' ?>" 
                title="Table View">
                    <i class="bi bi-layout-three-columns"></i>
                </a>
                <a href="?view=box<?= isset($_GET['page']) ? '&page=' . $_GET['page'] : '' ?><?= isset($_GET['rowsPerPage']) ? '&rowsPerPage=' . $_GET['rowsPerPage'] : '' ?>" 
                class="btn btn-outline-primary <?= $viewMode === 'box' ? 'active' : '' ?>" 
                title="Box View">
                    <i class="bi bi-grid-3x3-gap"></i>
                </a>
            </div>
        </div>
  <form method="POST" action="" id="deleteForm">
<?php if ($viewMode === 'table'): ?>
    <!-- TABLE VIEW -->
    <div class="table-responsive mt-4">
        <table class="table table-striped">
            <thead class="bg-primary text-white">
                <tr>
                    <th><input type="checkbox" id="selectAll" /></th>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Username</th> 
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><input type="checkbox" name="selected_ids[]" value="<?= $row['id'] ?>" /></td>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= date("d-m-Y", strtotime($row['date'])) ?></td>
                            <td><?= htmlspecialchars($row['type']) ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td>
                               <button type="button" 
        class="btn btn-sm btn-outline-info viewBtn" 
        data-bs-toggle="modal" 
        data-bs-target="#viewTypeModal" 
        data-id="<?= $row['id'] ?>">
    <i class="bi bi-eye"></i>
</button>

                               <button type="button"
        class="btn btn-sm btn-outline-primary"
        data-bs-toggle="modal"
        data-bs-target="#editTypeModal"
        onclick="loadEditType(<?= $row['id'] ?>)">
    <i class="bi bi-pencil"></i>
</button>

                                <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $row['id'] ?>" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">No records found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php elseif ($viewMode === 'box'): ?>
    <!-- BOX VIEW -->
    <div class="row mt-4">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-primary">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['type']) ?></h5>
                            <p class="card-text mb-1"><strong>ID:</strong> <?= htmlspecialchars($row['id']) ?></p>
                            <p class="card-text mb-1"><strong>Date:</strong> <?= date("d-m-Y", strtotime($row['date'])) ?></p>
                            <p class="card-text mb-2"><strong>Username:</strong> <?= htmlspecialchars($row['username']) ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="selected_ids[]" value="<?= $row['id'] ?>">
                                </div>
                                <div class="btn-group">
                                    <a href="view_type.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="bi bi-eye"></i></a>
                                    <a href="edit_type.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $row['id'] ?>" title="Delete"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center">No records found</div>
        <?php endif; ?>
    </div>
<?php endif; ?>
    <button type="button" class="btn btn-danger mt-3" id="deleteSelectedBtn">Delete Selected</button>
</form>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&type=<?= urlencode($typeFilter) ?>&date=<?= urlencode($dateFilter) ?>&rowsPerPage=<?= $rowsPerPage ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    


<!-- Add Type Modal -->
<div class="modal fade" id="addTypeModal" tabindex="-1" aria-labelledby="addTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="height: 90vh;">
      <div class="modal-header">
        <h5 class="modal-title" id="addTypeModalLabel">Add Note Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <iframe src="add_type.php" style="width: 100%; height: 100%; border: none;"></iframe>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="viewTypeModal" tabindex="-1" aria-labelledby="viewTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 900px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewTypeModalLabel">View Type Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0" style="height: 600px;">
        <iframe id="viewTypeIframe" src="" style="width: 100%; height: 100%; border: none;"></iframe>
      </div>
    </div>
  </div>
</div>

<!-- EDIT MODAL with IFRAME -->
<div class="modal fade" id="editTypeModal" tabindex="-1" aria-labelledby="editTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content" style="height: 90vh;">
      <div class="modal-header">
        <h5 class="modal-title" id="editTypeModalLabel">Edit Note Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <iframe id="editTypeIframe" src="" style="width:100%; height:100%; border:none;"></iframe>
      </div>
    </div>
  </div>
</div>



    <script>
        document.getElementById('selectAll').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });

        document.getElementById('deleteSelectedBtn').addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('input[name="selected_ids[]"]:checked'))
                                      .map(checkbox => checkbox.value);
            if (selectedIds.length === 0) {
                alert('Please select at least one note to delete.');
                return;
            }
           
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                if (confirm('Are you sure you want to delete this note?')) {
                    window.location.href = '?delete_id=' + id;
                }
            });
        });
         document.getElementById('deleteSelectedBtn').addEventListener('click', function () {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete the selected notes!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteForm').submit();
            }
        });
    });


document.addEventListener('DOMContentLoaded', () => {
  const viewButtons = document.querySelectorAll('.viewBtn');
  const iframe = document.getElementById('viewTypeIframe');
  const modal = document.getElementById('viewTypeModal');

  viewButtons.forEach(button => {
    button.addEventListener('click', () => {
      const id = button.getAttribute('data-id');
      // Set iframe src with ID param
      iframe.src = 'view_type.php?id=' + encodeURIComponent(id);
    });
  });

  // Clear iframe src when modal closes (to stop loading)
  modal.addEventListener('hidden.bs.modal', () => {
    iframe.src = '';
  });
});


function loadEditType(id) {
    document.getElementById('editTypeIframe').src = 'edit_type.php?id=' + id;
}




    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
