<?php
$currentPage = basename($_SERVER['PHP_SELF']);

// Handle form submission
$to = $_POST['to'] ?? '';
$cc = $_POST['cc'] ?? '';
$bcc = $_POST['bcc'] ?? '';
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';

$mailtoLink = '';
if (!empty($to)) {
    $mailtoLink = 'mailto:' . urlencode($to);
    $params = [];

    if (!empty($cc)) $params[] = 'cc=' . urlencode($cc);
    if (!empty($bcc)) $params[] = 'bcc=' . urlencode($bcc);
    if (!empty($subject)) $params[] = 'subject=' . urlencode($subject);
    if (!empty($message)) $params[] = 'body=' . urlencode($message);

    if (!empty($params)) {
        $mailtoLink .= '?' . implode('&', $params);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Mail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
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
            background-color: #002b5c;
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
            background-color: #fff9c4;
        }

        h1 {
            color: #002b5c;
            margin-bottom: 20px;
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
    </style>
</head>
<body>

<!-- Sidebar -->

<?php include 'header.php'; ?>

<!-- Content -->
<div class="content container" data-aos="fade-up">
    <h1>Send Email</h1>
    <form method="POST" class="card p-4 shadow rounded bg-white">
        <div class="mb-3">
            <label class="form-label">To</label>
            <input type="email" name="to" class="form-control" required placeholder="recipient@example.com">
        </div>
        <div class="mb-3">
            <label class="form-label">CC</label>
            <input type="email" name="cc" class="form-control" placeholder="cc@example.com">
        </div>
        <div class="mb-3">
            <label class="form-label">BCC</label>
            <input type="email" name="bcc" class="form-control" placeholder="bcc@example.com">
        </div>
        <div class="mb-3">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" class="form-control" placeholder="Subject">
        </div>
        <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea name="message" class="form-control" rows="5" placeholder="Your message..."></textarea>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <button type="submit" class="btn btn-primary">Generate Mail Link</button>
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($mailtoLink)): ?>
                <a href="<?= $mailtoLink ?>" class="btn btn-success" target="_blank">
                    📧 Open Mail App
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init();
</script>

</body>
</html>
