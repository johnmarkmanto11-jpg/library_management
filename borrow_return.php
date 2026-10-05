<?php
require "config.php"; require "layout.php"; require_role('Administrator');
$msg = "";

if (isset($_POST['return_book'])) {
    csrf_check();
    $id = (int)$_POST['borrowing_id'];

    $conn->begin_transaction();
    try {
        // Mark returned only if still borrowed
        $s = $conn->prepare("UPDATE borrowings SET returned_at = NOW(), status = 'Returned' WHERE id = ? AND status = 'Borrowed'");
        $s->bind_param("i", $id);
        $s->execute();

        if ($s->affected_rows === 0) {
            $conn->rollback();
            $msg = "This record was not found or is already returned.";
        } else {
            $s = $conn->prepare("UPDATE books SET available = available + 1 WHERE id = (SELECT book_id FROM borrowings WHERE id = ?)");
            $s->bind_param("i", $id);
            $s->execute();
            $conn->commit();
            $msg = "Book returned successfully.";
        }
    } catch (Exception $ex) {
        $conn->rollback();
        $msg = "Error returning book.";
    }
}

$records = $conn->query(
    "SELECT br.id, u.name, b.title, br.borrowed_at, br.returned_at, br.status
     FROM borrowings br
     JOIN users u ON br.user_id = u.id
     JOIN books b ON br.book_id = b.id
     ORDER BY br.id DESC"
);
page_start("🔄 Borrow / Return");
flash($msg);
?>
<table>
    <tr><th>ID</th><th>Student</th><th>Book</th><th>Borrow Date</th><th>Return Date</th><th>Status</th><th>Action</th></tr>
    <?php while ($r = $records->fetch_assoc()): ?>
    <tr>
        <td><?= $r['id'] ?></td>
        <td><?= e($r['name']) ?></td>
        <td><?= e($r['title']) ?></td>
        <td><?= e($r['borrowed_at']) ?></td>
        <td><?= e($r['returned_at'] ?? '-') ?></td>
        <td><?= e($r['status']) ?></td>
        <td>
            <?php if ($r['status'] === 'Borrowed'): ?>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="borrowing_id" value="<?= $r['id'] ?>">
                <button type="submit" name="return_book">Return</button>
            </form>
            <?php else: ?>Returned<?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
<?php page_end();
