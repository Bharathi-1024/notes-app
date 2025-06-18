<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>EmailJS with Quill & ImgBB</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- AOS Animation CSS -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <!-- Quill Editor CSS -->
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
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
      width: 252px;
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

    .form-container {
      max-width: 800px;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    .ql-editor {
      min-height: 150px;
    }

    .sidebar img {
      height: 70px;
      border-radius: 10px;
      padding: 5px;
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

<!-- Sidebar -->


<!-- Main Content -->
<div class="content container" data-aos="fade-up">
  <div class="form-container mx-auto">
    <h2 class="text-center mb-4">Send Email by EmailJS</h2>
    <form id="emailForm">
      <div class="mb-3">
        <label for="to" class="form-label">To</label>
        <input type="email" class="form-control" id="to" name="to_email" required />
      </div>
      <div class="mb-3">
        <label for="cc" class="form-label">CC</label>
        <input type="email" class="form-control" id="cc" name="cc_email" />
      </div>
      <div class="mb-3">
        <label for="bcc" class="form-label">BCC</label>
        <input type="email" class="form-control" id="bcc" name="bcc_email" />
      </div>
      <div class="mb-3">
        <label for="subject" class="form-label">Subject</label>
        <input type="text" class="form-control" id="subject" name="subject" />
      </div>

      <!-- Image Upload Section
      <div class="mb-3">
        <label for="imageInput" class="form-label">Upload Image (via ImgBB)</label>
        <input type="file" id="imageInput" accept="image/*" class="form-control" />
        <button type="button" id="uploadBtn" class="btn btn-success mt-2">Upload Image</button>
        <div id="preview" class="mt-2"></div>
      </div>
 -->
      <!-- Message Editor -->
      <div class="mb-3">
        <label class="form-label">Message (styled)</label>
        <div id="quillEditor"></div>
        <input type="hidden" name="message" id="hiddenMessage">
      </div>

      <button type="submit" class="btn btn-primary w-100">Send</button>
    </form>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> 
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script> 
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script> 

<script>
  AOS.init();

  const SERVICE_ID = "service_9rsju8p";
  const TEMPLATE_ID = "template_hpmg2b8";
  const PUBLIC_KEY = "07x_0A2vbrgOqxUZK";
  const IMG_BB_API_KEY = "0e0283772538ab4f4f29a449cce3ad33";

  emailjs.init(PUBLIC_KEY);

  const quill = new Quill('#quillEditor', {
    theme: 'snow',
    placeholder: 'Type your styled message here...'
  });

  document.getElementById("emailForm").addEventListener("submit", function (e) {
    e.preventDefault();
    document.getElementById('hiddenMessage').value = quill.root.innerHTML;

    emailjs.sendForm(SERVICE_ID, TEMPLATE_ID, this)
      .then(() => {
        alert("✅ Email sent successfully!");
        quill.setContents([]);
        this.reset();
        document.getElementById("preview").innerHTML = "";
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("❌ Failed to send email.");
      });
  });

 

  document.getElementById("uploadBtn").addEventListener("click", () => {
    const fileInput = document.getElementById("imageInput");
    const preview = document.getElementById("preview");

    if (!fileInput.files.length) {
      alert("Please select an image first.");
      return;
    }

    const formData = new FormData();
    formData.append("image", fileInput.files[0]);

    fetch(`https://api.imgbb.com/1/upload?key=${IMG_BB_API_KEY}`, {
      method: "POST",
      body: formData,
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const imageUrl = data.data.url;

        // Show URL only (no image)
        preview.innerHTML = `
          <p><strong>Image URL:</strong><br><code>${imageUrl}</code></p>
        `;

        // Add to Subject field
        const subjectField = document.getElementById("subject");
        subjectField.value += (subjectField.value ? " | " : "") + imageUrl;
      } else {
        alert("❌ Failed to upload image.");
      }
    })
    .catch(err => {
      console.error("Upload error:", err);
      alert("❌ Upload error.");
    });
  });
</script>

</body>
</html>
