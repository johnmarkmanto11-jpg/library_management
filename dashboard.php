<?php
require "config.php"; require "layout.php"; require_role();

$role = $_SESSION['role'];
$one = fn($sql) => $conn->query($sql)->fetch_assoc()['c'];
$book_count = $one("SELECT COUNT(*) c FROM books");
$available  = $one("SELECT COALESCE(SUM(available),0) c FROM books");
$borrowed   = $one("SELECT COUNT(*) c FROM borrowings WHERE status='Borrowed'");
$user_count = $one("SELECT COUNT(*) c FROM users");

page_start($role . " Dashboard");
?>
<div class="cards">
    <div class="card"><h3><?= $book_count ?></h3><p>Total Books</p></div>
    <div class="card"><h3><?= $available ?></h3><p>Available Copies</p></div>
    <div class="card"><h3><?= $borrowed ?></h3><p>Borrowed Books</p></div>
    <?php if ($role === 'Administrator'): ?>
    <div class="card"><h3><?= $user_count ?></h3><p>Total Users</p></div>
    <?php endif; ?>
</div>

<div class="menu-grid">
<?php if ($role === 'Administrator'): ?>
    <a class="menu" href="books.php">📚 Manage Books</a>
    <a class="menu" href="users.php">👥 Manage Users</a>
    <a class="menu" href="borrow_return.php">🔄 Borrow / Return</a>
    <a class="menu" href="reports.php">📊 Reports</a>
<?php else: ?>
    <a class="menu" href="search.php">🔎 Browse Books by Grade</a>
    <a class="menu" href="borrow.php">📚 Borrow Book</a>
    <a class="menu" href="records.php">🧾 My Records</a>
<?php endif; ?>
</div>
<?php page_end();
