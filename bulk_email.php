<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer path
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

$sent = 0;
$failed = 0;
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    $file = $_FILES['email_file']['tmp_name'];

    if (!$file) {
        $errorMsg = "Please upload a file.";
    } else {
        // Read CSV line by line
        $fileData = fopen($file, "r");
        while (($row = fgetcsv($fileData)) !== false) {
            $email = trim($row[0]);

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';  // change if needed
                    $mail->SMTPAuth = true;
                    $mail->Username = 'bharathiraj1024@gmail.com';  // your Gmail
                    $mail->Password = 'vrdq sjbd eajq yfiv';     // use App Password
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('your_email@gmail.com', 'Your Name');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body = $message;


                    $mail->send();
                    $sent++;
                } catch (Exception $e) {
                    $failed++;
                }
            }
        }
        fclose($fileData);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bulk Email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Quill CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

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

/* === Custom Quill Editor Styling to Match Yellow-Themed App === */

.ql-toolbar {
    background-color: #f1f8e9; /* Soft green tone for toolbar */
    border: 1px solid #ced4da;
    border-radius: 0.5rem 0.5rem 0 0;
    padding: 8px;
}

.ql-container {
    background-color: #fff9c4; /* Light yellow background to match app */
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 0.5rem 0.5rem;
}

.ql-editor {
    background-color: #fff9c4;  /* Match .content background */
    min-height: 200px;
    font-size: 1rem;
    padding: 10px;
    color: #212529; /* Dark text */
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Placeholder text */
.ql-editor::before {
    color: #9e9e9e;
    font-style: italic;
}

/* Focus style (optional, mimics Bootstrap inputs) */
.ql-editor:focus,
#editor:focus-within {
    outline: 2px solid #80bdff;
    outline-offset: -2px;
    background-color: #fffde7;
}



</style>
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>
<div class="container mt-5">
    <h2 class="mb-4">Send Bulk Email</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Upload CSV file (email list)</label>
            <input type="file" name="email_file" class="form-control" accept=".csv" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Message</label>
           <label class="form-label">Message</label>
<div id="editor" style="height: 200px;"></div>
<input type="hidden" name="message" id="message">

        </div>
        <button type="submit" class="btn btn-primary">Send Bulk Emails</button>
    </form>

    <?php if ($sent || $failed || $errorMsg): ?>
        <div class="alert alert-info mt-4">
            <?= $errorMsg ?: "Emails Sent: $sent, Failed: $failed" ?>
        </div>
    <?php endif; ?>
    <!-- Bootstrap 5 Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Quill JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
  // Initialize Quill editor
  var quill = new Quill('#editor', {
    theme: 'snow',
    placeholder: 'Type your message here...',
    modules: {
      toolbar: [
        [{ header: [1, 2, false] }],
        ['bold', 'italic', 'underline'],
        ['link', 'image'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['clean']
      ]
    }
  });

  // On form submit, set Quill HTML content to hidden input
  document.querySelector('form').onsubmit = function () {
    document.querySelector('#message').value = quill.root.innerHTML;
  };
</script>

</div>
</body>
</html>
