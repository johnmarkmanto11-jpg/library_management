<?php
require "config.php"; require "layout.php"; require_role('Student');

$uid = $_SESSION['user_id'];
$s = $conn->prepare(
    "SELECT br.id, b.title, br.borrowed_at, br.returned_at, br.status
     FROM borrowings br JOIN books b ON br.book_id = b.id
     WHERE br.user_id = ? ORDER BY br.id DESC"
);
$s->bind_param("i", $uid);
$s->execute();
$records = $s->get_result();

page_start("🧾 My Borrowing Records");
?>
<table>
    <tr><th>ID</th><th>Book</th><th>Borrow Date</th><th>Return Date</th><th>Status</th></tr>
    <?php if ($records->num_rows > 0): while ($r = $records->fetch_assoc()): ?>
    <tr>
        <td><?= $r['id'] ?></td>
        <td><?= e($r['title']) ?></td>
        <td><?= e($r['borrowed_at']) ?></td>
        <td><?= e($r['returned_at'] ?? '-') ?></td>
        <td><?= e($r['status']) ?></td>
    </tr>
    <?php endwhile; else: ?>
    <tr><td colspan="5">No borrowing records yet. <a href="search.php">Find a book to borrow</a>.</td></tr>
    <?php endif; ?>
</table>
<?php page_end();
