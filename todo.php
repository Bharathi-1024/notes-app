<?php
$conn = new mysqli("localhost", "root", "", "notes_app");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);



// Create
if (isset($_POST['add_task'])) {
    $task = $_POST['task'];
    $deadline = $_POST['deadline'];
    $priority_type_id = (int)$_POST['priority_type_id'];

    $stmt = $conn->prepare("INSERT INTO todo (task, status, priority_type_id, deadline, created_at) VALUES (?, 'pending', ?, ?, NOW())");
    $stmt->bind_param("sis", $task, $priority_type_id, $deadline);
    $stmt->execute();
    echo "<script>window.location.href='todo.php';</script>";
}

// Update
if (isset($_POST['update_task'])) {
    $id = (int)$_POST['task_id'];
    $task = $conn->real_escape_string(trim($_POST['task']));
    $status = $_POST['status'] === 'done' ? 'done' : 'pending';
    $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
    $priority_type_id = (int)$_POST['priority_type_id']; // Fixed variable name

    // Use prepared statement for update
    $stmt = $conn->prepare("UPDATE todo SET task=?, status=?, deadline=?, priority_type_id=? WHERE id=?");
    $stmt->bind_param("sssii", $task, $status, $deadline, $priority_type_id, $id);
    $stmt->execute();
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'Task updated!',
                showConfirmButton: false,
                timer: 1500,
                toast: true
            });
        });
    </script>";
}

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM todo WHERE id=$id");
    echo "<script>window.location.href='todo.php?deleted=1';</script>";
    exit;
}

// Toggle Status
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    $conn->query("UPDATE todo SET status = IF(status='done', 'pending', 'done') WHERE id=$id");
    echo "<script>window.location.href='todo.php';</script>";
    exit;
}

// Read

$query = "SELECT todo.*, pt.name AS priority_name, pt.color AS priority_color 
          FROM todo 
          LEFT JOIN priority_type pt ON todo.priority_type_id = pt.id 
          ORDER BY todo.created_at DESC";
$result = $conn->query($query);


// Debug if query fails
if (!$result) {
    die("SQL Error: " . $conn->error);
}





?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --high-priority: #ff6b6b;
            --medium-priority: #ffd166;
            --low-priority: #06d6a0;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        

        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .task-card {
            margin-bottom: 1rem;
        }
        
        .task-high {
            border-left: 4px solid var(--high-priority);
        }
        
        .task-medium {
            border-left: 4px solid var(--medium-priority);
        }
        
        .task-low {
            border-left: 4px solid var(--low-priority);
        }
        
        .priority-high {
            background-color: var(--high-priority);
            color: white;
        }
        
        .priority-medium {
            background-color: var(--medium-priority);
        }
        
        .priority-low {
            background-color: var(--low-priority);
            color: white;
        }
        
        .task-done {
            opacity: 0.7;
            background-color: #f1f1f1;
        }
        
        .task-done .task-title {
            text-decoration: line-through;
            color: #6c757d !important;
        }
        
        .add-task-btn {
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
        }
        
        .action-btn {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border-radius: 10px;
        }
        
        .deadline-warning {
            color: #dc3545;
            font-weight: bold;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box .bi-search {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .search-box input {
            padding-left: 40px;
        }
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
    <div class="container animate__animated animate__fadeIn">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold text-primary">
                    <i class="bi bi-check2-circle"></i> TaskMaster
                </h1>
                <p class="text-muted">Organize your work and boost productivity</p>
            </div>
           
        </div>

        <!-- Stats Cards -->
        <?php
        $total_tasks = $conn->query("SELECT COUNT(*) as count FROM todo")->fetch_assoc()['count'];
        $completed_tasks = $conn->query("SELECT COUNT(*) as count FROM todo WHERE status='done'")->fetch_assoc()['count'];
        $pending_tasks = $total_tasks - $completed_tasks;
        ?>
        <div class="row mb-4">
    <!-- Total Tasks -->
    <div class="col-md-4 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-white-50">Total Tasks</h6>
                        <h2 class="mb-0"><?= $total_tasks ?></h2>
                    </div>
                    <div class="p-3 rounded-circle d-flex align-items-center justify-content-center" style="background-color: #ffc107;">
                        <i class="bi bi-list-task fs-4 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Completed Tasks -->
    <div class="col-md-4 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-white-50">Completed</h6>
                        <h2 class="mb-0"><?= $completed_tasks ?></h2>
                    </div>
                    <div class="p-3 rounded-circle d-flex align-items-center justify-content-center" style="background-color: #28a745;">
                        <i class="bi bi-check-circle fs-4 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Tasks -->
    <div class="col-md-4 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-white-50">Pending</h6>
                        <h2 class="mb-0"><?= $pending_tasks ?></h2>
                    </div>
                    <div class="p-3 rounded-circle d-flex align-items-center justify-content-center" style="background-color: #dc3545;">
                        <i class="bi bi-exclamation-circle fs-4 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


        <!-- Add Task Form -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title"><i class="bi bi-plus-circle"></i> Add New Task</h5>
        <form method="POST" class="row g-3 align-items-end">
            <!-- Task Input -->
            <div class="col-md-4">
                <div class="form-floating">
                    <input type="text" name="task" class="form-control" id="taskInput" placeholder="Task name" required>
                    <label for="taskInput"><i class="bi bi-card-text"></i> Task Name</label>
                </div>
            </div>

            <!-- Deadline Input -->
            <div class="col-md-3">
                <div class="form-floating">
                    <input type="date" name="deadline" class="form-control" id="deadlineInput" placeholder="Deadline">
                    <label for="deadlineInput"><i class="bi bi-calendar"></i> Deadline</label>
                </div>
            </div>

            <!-- Priority Type Select -->
            <div class="col-md-3">
                <div class="form-floating">
                    <select name="priority_type_id" class="form-select" id="priorityTypeSelect" required>
                        <option value="" disabled selected>Select Priority</option>
                        <?php
                        $priorityTypes = $conn->query("SELECT * FROM priority_type ORDER BY id ASC");
                        while ($row = $priorityTypes->fetch_assoc()):
                        ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <label for="priorityTypeSelect"><i class="bi bi-flag"></i> Priority Type</label>
                </div>
            </div>

            <!-- Add Button -->
            <div class="col-md-2 d-grid">
                <button type="submit" name="add_task" class="btn btn-primary add-task-btn h-100">
                    <i class="bi bi-plus-lg"></i> Add Task
                </button>
            </div>
        </form>
    </div>
</div>


        <!-- Search Box -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control" placeholder="Search tasks..." id="searchInput">
                </div>
            </div>
        </div>

        <!-- Tasks List -->
        <div class="row">
            <div class="col-12">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                     $priority_class = '';
$priority_badge = '';
switch (strtolower($row['priority_name'])) {  // Fixed to use priority_name
    case 'high':
        $priority_class = 'task-high';
        $priority_badge = '<span class="badge priority-high me-2"><i class="bi bi-exclamation-triangle"></i> High</span>';
        break;
    case 'medium':
        $priority_class = 'task-medium';
        $priority_badge = '<span class="badge priority-medium me-2"><i class="bi bi-exclamation-circle"></i> Medium</span>';
        break;
    case 'low':
        $priority_class = 'task-low';
        $priority_badge = '<span class="badge priority-low me-2"><i class="bi bi-arrow-down-circle"></i> Low</span>';
        break;
}
                        
                        $is_done = $row['status'] === 'done';
                        $deadline_warning = '';
                        
                        if ($row['deadline'] && !$is_done) {
                            $deadline = new DateTime($row['deadline']);
                            $today = new DateTime();
                            $interval = $today->diff($deadline);
                            
                            if ($deadline < $today) {
                                $deadline_warning = '<span class="deadline-warning"><i class="bi bi-exclamation-triangle"></i> Overdue</span>';
                            } elseif ($interval->days <= 2) {
                                $deadline_warning = '<span class="text-warning"><i class="bi bi-clock"></i> Due soon</span>';
                            }
                        }
                        ?>
                        
                        <div class="card task-card mb-3 <?= $priority_class ?> <?= $is_done ? 'task-done' : '' ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <?= $priority_badge ?>
                                            <h5 class="card-title task-title mb-0 <?= $is_done ? 'text-muted' : 'text-dark' ?>">
                                                <?= htmlspecialchars($row['task']) ?>
                                            </h5>
                                        </div>
                                        
                                        <div class="d-flex flex-wrap align-items-center text-muted">
                                            <small class="me-3"><i class="bi bi-calendar"></i> Created: <?= date("M d, Y", strtotime($row['created_at'])) ?></small>
                                            
                                            <?php if ($row['deadline']): ?>
                                                <small class="me-3"><i class="bi bi-clock"></i> Deadline: <?= date("M d, Y", strtotime($row['deadline'])) ?></small>
                                            <?php endif; ?>
                                            
                                            <?php if ($deadline_warning): ?>
                                                <small><?= $deadline_warning ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex">
    <form method="POST" class="d-flex">
        <input type="hidden" name="task_id" value="<?= $row['id'] ?>">
        <input type="hidden" name="task" value="<?= htmlspecialchars($row['task']) ?>">
        <input type="hidden" name="status" value="<?= $is_done ? 'pending' : 'done' ?>">
        <input type="hidden" name="deadline" value="<?= $row['deadline'] ?>">
        <input type="hidden" name="priority_type_id" value="<?= $row['priority_type_id'] ?? '' ?>">
        
        <button type="submit" name="update_task" class="btn btn-sm action-btn <?= $is_done ? 'btn-outline-success' : 'btn-success' ?> me-2" title="<?= $is_done ? 'Mark Pending' : 'Mark Done' ?>">
            <i class="bi <?= $is_done ? 'bi-arrow-counterclockwise' : 'bi-check-lg' ?>"></i>
        </button>
    </form>

    <button class="btn btn-sm btn-primary action-btn me-2 edit-task-btn" 
            data-id="<?= $row['id'] ?>" 
            data-task="<?= htmlspecialchars($row['task']) ?>" 
            data-status="<?= $row['status'] ?>" 
            data-deadline="<?= $row['deadline'] ?>" 
            data-priority_type_id="<?= $row['priority_type_id'] ?? '' ?>"
            title="Edit">
        <i class="bi bi-pencil"></i>
    </button>

    <button class="btn btn-sm btn-danger action-btn delete-btn" data-id="<?= $row['id'] ?>" title="Delete">
        <i class="bi bi-trash"></i>
    </button>
</div>

                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-check2-all display-1 text-muted mb-3"></i>
                            <h3 class="text-muted">No tasks found</h3>
                            <p class="text-muted">Add your first task using the form above</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="task_id" id="editTaskId">
                    
                    <div class="mb-3">
                        <label for="editTaskName" class="form-label">Task Name</label>
                        <input type="text" class="form-control" id="editTaskName" name="task" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editTaskStatus" class="form-label">Status</label>
                            <select class="form-select" id="editTaskStatus" name="status">
                                <option value="pending">Pending</option>
                                <option value="done">Done</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Fixed priority dropdown -->
                    <div class="mb-3">
                        <label for="editTaskPriority" class="form-label">Priority</label>
                        <select name="priority_type_id" class="form-select" id="editTaskPriority" required>
                            <option value="">Select Priority</option>
                            <?php
                            $priorityTypes = $conn->query("SELECT * FROM priority_type ORDER BY id ASC");
                            while ($type = $priorityTypes->fetch_assoc()):
                            ?>
                                <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editTaskDeadline" class="form-label">Deadline</label>
                        <input type="date" class="form-control" id="editTaskDeadline" name="deadline">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_task" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

  


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
    
    <?php if (isset($_GET['deleted'])): ?>
    <script>
        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Task deleted!',
            showConfirmButton: false,
            timer: 1500,
            toast: true
        });
    </script>
    <?php endif; ?>

    <script>
        // Initialize edit modal
        document.querySelectorAll('.edit-task-btn').forEach(button => {
            button.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('editTaskModal'));
                document.getElementById('editTaskId').value = this.getAttribute('data-id');
                document.getElementById('editTaskName').value = this.getAttribute('data-task');
                document.getElementById('editTaskStatus').value = this.getAttribute('data-status');
                document.getElementById('editTaskDeadline').value = this.getAttribute('data-deadline');
                document.getElementById('editTaskPriority').value = this.getAttribute('data-priority');
                modal.show();
            });
        });

        // Delete confirmation
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                Swal.fire({
                    title: 'Delete Task?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '?delete=' + id;
                    }
                });
            });
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.task-card').forEach(card => {
                const taskText = card.querySelector('.task-title').textContent.toLowerCase();
                if (taskText.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Make tasks sortable
        new Sortable(document.querySelector('.row .col-12'), {
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function() {
                // You could add AJAX here to save the new order to the database
            }
        });
         document.querySelectorAll('.edit-task-btn').forEach(button => {
        button.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('editTaskModal'));
            document.getElementById('editTaskId').value = this.getAttribute('data-id');
            document.getElementById('editTaskName').value = this.getAttribute('data-task');
            document.getElementById('editTaskStatus').value = this.getAttribute('data-status');
            document.getElementById('editTaskDeadline').value = this.getAttribute('data-deadline');
            
            // Fixed priority setting
            document.getElementById('editTaskPriority').value = this.getAttribute('data-priority-type-id');
            modal.show();
        });
    });
    </script>
</body>
</html>