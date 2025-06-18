<div class="sidebar">
  <div class="text-center my-3">
    <img src="tostotlogo.jpg" alt="Tostot Logo" style="height: 70px; background-color: transparent; border-radius: 10px; padding: 5px;">
  </div>
  <h3 class="text-center">Menu</h3>

  <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>

    <div class="mb-2">
        <a class="text-white d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#notesMenu" role="button" aria-expanded="false" aria-controls="notesMenu" style="text-decoration: none;">
            <span><i class="bi bi-journal-text me-2"></i>Notes</span>
            <i class="bi bi-chevron-down"></i>
        </a>
        <div class="collapse ps-4 mt-1" id="notesMenu">
          <a href="type.php" class="d-block text-white"><i class="bi bi-tags me-2"></i> Types</a>
            <a href="notes.php" class="d-block text-white mb-1"><i class="bi bi-file-earmark-text me-2"></i>All Notes</a>
            
        </div>
    </div>
   <div class="mb-2">
        <a class="text-white d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#todoMenu" role="button" aria-expanded="false" aria-controls="todoMenu" style="text-decoration: none;">
            <span><i class="bi bi-list-task me-2"></i>To-Do</span>
            <i class="bi bi-chevron-down"></i>
        </a>
        <div class="collapse ps-4 mt-1" id="todoMenu">
           <a href="priority_type.php" class="d-block text-white"><i class="bi bi-flag me-2"></i>Priorities</a>
            <a href="todo.php" class="d-block text-white mb-1"><i class="bi bi-check2-square me-2"></i>To-Do List</a>
           
        </div>
    </div>
<div class="mb-2">
    <a class="text-white d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#emailMenu" role="button" aria-expanded="false" aria-controls="emailMenu" style="text-decoration: none;">
        <span><i class="bi bi-envelope me-2"></i>Email</span>
        <i class="bi bi-chevron-down"></i>
    </a>

    <div class="collapse ps-4 mt-1" id="emailMenu">
        <a href="mail.php" class="d-block text-white"><i class="bi bi-send me-2"></i>Send Mail</a>
        <a href="bulk_email.php" class="d-block text-white mb-1"><i class="bi bi-people-fill me-2"></i>Bulk Mail</a>
    </div>
</div>


  <hr>
 <a href="logout.php" class="logout">
    <i class="bi bi-box-arrow-right"></i> Logout
</a>

  
</div>
