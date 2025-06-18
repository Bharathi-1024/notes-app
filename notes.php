<?php
include 'db.php';
require 'init.php';
$viewMode = isset($_GET['view']) ? $_GET['view'] : 'table'; // OLD

// Your page-specific code

if (isset($_GET['delete_id'])) {
    $deleteId = $conn->real_escape_string($_GET['delete_id']);
    $deleteQuery = "DELETE FROM notes WHERE id = '$deleteId'";
    $conn->query($deleteQuery); // No alert box, just process the deletion
    echo "<script>window.location.href = 'notes.php';</script>";
    exit;
}

// Handle Bulk Delete Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_ids'])) {
    $selectedIds = $_POST['selected_ids'];
    $escapedIds = array_map(fn($id) => $conn->real_escape_string($id), $selectedIds);
    $idList = implode(',', $escapedIds);
    $bulkDeleteQuery = "DELETE FROM notes WHERE id IN ($idList)";
    $conn->query($bulkDeleteQuery); // No alert box, just process the deletion
    echo "<script>window.location.href = 'notes.php';</script>";
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
$baseQuery = "SELECT notes.id,notes.date as date, notes_type.type AS type,
                notes.title, notes.description,notes.username FROM notes JOIN notes_type 
                ON notes.type = notes_type.id WHERE 1=1";

// Filter by ID (Sl. No)
if (!empty($slNoFilter)) {
    $baseQuery .= " AND notes.id = " . (int)$slNoFilter;  // Only match by exact ID (int)
}

if (!empty($titleFilter)) {
    $baseQuery .= " AND notes.title LIKE '%" . $conn->real_escape_string($titleFilter) . "%'";
}
if (!empty($typeFilter)) {
    $baseQuery .= " AND notes_type.type LIKE '%" . $conn->real_escape_string($typeFilter) . "%'";
}

// Filter by Date (ignoring time part)
if (!empty($dateFilter)) {
    $baseQuery .= " AND DATE(notes.date) = '" . $conn->real_escape_string($dateFilter) . "'";
}

$totalPages = 1;

// Get total records for pagination
$countQuery = "SELECT COUNT(*) AS count FROM notes WHERE 1=1";
if (!empty($slNoFilter)) {
    $countQuery .= " AND id = " . (int)$slNoFilter; // Count filter by exact ID
}
if (!empty($titleFilter)) {
    $countQuery .= " AND title LIKE '%" . $conn->real_escape_string($titleFilter) . "%'";
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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
    <h1 class="mb-0">Notes</h1>
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
                <input type="text" name="title" class="form-control w-auto" placeholder="Search by title"
                    value="<?php echo htmlspecialchars($titleFilter); ?>">
                <button type="submit" class="btn btn-success" title="Search">
                    <i class="bi bi-search"></i>
                </button>
                <a href="notes.php" class="btn btn-light" title="Clear Filters">
                    <i class="bi bi-x-circle"></i>
                </a>
               <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
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
                <div class="table-responsive mt-4">
                    <table class="table table-striped">
                        <thead class="bg-primary">
                            <tr>
                                <th><input type="checkbox" id="selectAll" /></th>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Username</th> 
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td style="padding-right: 10px;"><input type="checkbox" name="selected_ids[]" value="<?= $row['id'] ?>" /></td>
                                        <td><?= htmlspecialchars($row['id']) ?></td>
                                        <td><?= date("d-m-Y", strtotime($row['date'])) ?></td>

                                        <td><?= htmlspecialchars($row['type']) ?></td>
                                        <td><?= htmlspecialchars($row['title']) ?></td>
                                        <td><?= strlen($row['description']) > 50 ? substr($row['description'], 0, 50) . "..." : htmlspecialchars($row['description']) ?></td>
                                        <td><?= htmlspecialchars($row['username']) ?></td> 
                                        <td>
                                           <button type="button"
        class="btn btn-sm btn-outline-info"
        data-bs-toggle="modal"
        data-bs-target="#viewModal"
        onclick="openViewModal(<?= $row['id'] ?>)">
    <i class="bi bi-eye"></i>
</button>

                                            <a href="javascript:void(0);" onclick="openEditModal(<?= $row['id'] ?>)" class="btn btn-sm btn-outline-warning" title="Edit">
  <i class="bi bi-pencil"></i>
</a>

                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $row['id'] ?>" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center">No records found</td></tr> <!-- Update colspan to 8 -->
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <!-- Box View -->
                    <div class="box-view">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <div class="note-box">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" name="selected_ids[]" value="<?= $row['id'] ?>" class="form-check-input">
                                        </div>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-info" onclick="openViewModal(<?= $row['id'] ?>)" title="View">
    <i class="bi bi-eye"></i>
</button>

                                            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $row['id'] ?>" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="title"><?= htmlspecialchars($row['title']) ?></div>
                                    <div class="description">
                                        <?= htmlspecialchars($row['description']) ?>
                                    </div>
                                    <div class="text-muted small">
                                        <div>Type: <?= htmlspecialchars($row['type']) ?></div>
                                        <div>Date: <?= htmlspecialchars($row['date']) ?></div>
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
                        <a class="page-link" href="?page=<?= $i ?>&title=<?= urlencode($titleFilter) ?>&type=<?= urlencode($typeFilter) ?>&date=<?= urlencode($dateFilter) ?>&rowsPerPage=<?= $rowsPerPage ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">View Note</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0" style="height: 500px;">
        <iframe id="viewIframe" style="border: none; width: 100%; height: 100%;" src=""></iframe>
      </div>
    </div>
  </div>
</div>





    <script>
       document.addEventListener('DOMContentLoaded', function() {
    // Ensure the 'selectAll' checkbox exists before attaching an event listener
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
    }

    // Ensure the 'deleteSelectedBtn' exists before attaching the event listener
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    if (deleteSelectedBtn) {
        deleteSelectedBtn.addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('input[name="selected_ids[]"]:checked'))
                                    .map(checkbox => checkbox.value);
            if (selectedIds.length === 0) {
                alert('Please select at least one note to delete.');
                return;
            }
            const currentPage = document.querySelector('.pagination .active')?.dataset.page || 1; // Default to page 1 if not found
            const currentView = document.querySelector('.view-toggle.active')?.dataset.view || 'box'; // Default to box view
            if (confirm('Are you sure you want to delete the selected notes?')) {
                const form = document.getElementById('deleteForm');
                form.action = `notes.php?page=${currentPage}&view=${currentView}`;
                form.submit();
            }
        });
    }

    // Ensure the '.delete-btn' elements exist before attaching event listeners
    const deleteButtons = document.querySelectorAll('.delete-btn');
    if (deleteButtons.length > 0) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const currentPage = document.querySelector('.pagination .active')?.dataset.page || 1; // Default to page 1 if not found
                const currentView = document.querySelector('.view-toggle.active')?.dataset.view || 'box'; // Default to box view
                if (confirm('Are you sure you want to delete this note?')) {
                    window.location.href = `?delete_id=${id}&page=${currentPage}&view=${currentView}`;
                }
            });
        });
    }

    // New code for view toggle
    const viewToggles = document.querySelectorAll('.view-toggle');
    if (viewToggles.length > 0) {
        viewToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                viewToggles.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    // Set correct view on page load based on URL parameters
    const params = new URLSearchParams(window.location.search);
    const view = params.get('view') || 'box'; // Default to 'box'
    const boxView = document.getElementById('box-view');
    const tableView = document.getElementById('table-view');
    if (view === 'box') {
        boxView.style.display = 'block';
        tableView.style.display = 'none';
    } else {
        boxView.style.display = 'none';
        tableView.style.display = 'block';
    }
});



function openViewModal(id) {
    const iframe = document.getElementById('viewIframe');

    // Add timestamp to avoid caching
    const url = 'view.php?id=' + id + '&t=' + new Date().getTime();

    // First clear the iframe to fix blank issue
    iframe.src = '';
    setTimeout(() => {
        iframe.src = url;
    }, 100); // Delay helps ensure browser loads new content

    const modal = new bootstrap.Modal(document.getElementById('viewModal'));
    modal.show();
}


    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  window.addEventListener('message', function(event) {
    if (event.data === 'note_added') {
      // Close the modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('addModal'));
      modal.hide();

      // Show SweetAlert
      Swal.fire({
        title: 'Success!',
        text: 'Note added successfully.',
        icon: 'success',
        confirmButtonText: 'OK'
      });

   setTimeout(() => {
        location.reload();
      }, 2000);
    }
    
  });
  
  function openViewModal(id) {
    const iframe = document.getElementById('viewIframe');
    iframe.src = 'view.php?id=' + id;
  }

  // Optional: clear iframe src on close to free memory and avoid stale content
  const viewModal = document.getElementById('viewModal');
  viewModal.addEventListener('hidden.bs.modal', function () {
    document.getElementById('viewIframe').src = '';
  });



window.addEventListener('message', function(event) {
    if (event.data === 'close_view_modal') {
        const modal = bootstrap.Modal.getInstance(document.getElementById('viewModal'));
        if (modal) {
            modal.hide();

            // 👇 Remove the backdrop manually (fixes blur issue)
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(el => el.remove());

            // 👇 Also remove "modal-open" class from body
            document.body.classList.remove('modal-open');
            document.body.style = '';
        }
    }
});


window.addEventListener('message', function(event) {
    if (event.data === 'close_add_modal') {
        const modal = bootstrap.Modal.getInstance(document.getElementById('addModal'));
        if (modal) {
            modal.hide();
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style = '';
        }
    }
});

function openEditModal(id) {
  const container = document.getElementById('editIframeContainer');
  container.innerHTML = ''; // Clear previous iframe

  const iframe = document.createElement('iframe');
  iframe.src = 'edit.php?id=' + id + '&t=' + Date.now(); // cache buster
  iframe.width = '100%';
  iframe.height = '500';
  iframe.style.border = 'none';
  container.appendChild(iframe);

  const modal = new bootstrap.Modal(document.getElementById('editModal'));
  modal.show();
}


window.addEventListener('message', function(event) {
  if (event.data.status === 'updated') {
    const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
    modal.hide();
    Swal.fire({
      icon: 'success',
      title: 'Updated!',
      text: 'Note updated successfully.',
      timer: 2000,
      showConfirmButton: false
    });
    setTimeout(() => location.reload(), 2000); // reload page
  } else if (event.data.status === 'error') {
    Swal.fire({
      icon: 'error',
      title: 'Update Failed',
      text: 'There was a problem updating the note.'
    });
  }
});


</script>

    <!-- Modal with iframe loading add.php -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg"> <!-- large modal -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addModalLabel">Add Note</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <iframe src="add.php" style="border:none; width:100%; height:500px;"></iframe>
      </div>
    </div>
  </div>
</div>


 <!-- Modal with iframe loading add.php -->
<div class="modal fade" id="addModalview" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg"> <!-- large modal -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addModalLabel">View Note</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <iframe src="view.php" style="border:none; width:100%; height:500px;"></iframe>
      </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Edit Note</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div id="editIframeContainer" style="width:100%; height:500px;"></div>
      </div>
    </div>
  </div>
</div>


<!-- Bootstrap CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>