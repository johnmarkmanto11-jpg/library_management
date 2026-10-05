<?php
require "config.php"; require "layout.php"; require_role('Student');
$msg = "";

if (isset($_POST['borrow'])) {
    csrf_check();
    $book_id = (int)$_POST['book_id'];
    $user_id = $_SESSION['user_id'];

    $conn->begin_transaction();
    try {
        // 1. Block borrowing the same book twice
        $s = $conn->prepare("SELECT COUNT(*) c FROM borrowings WHERE user_id = ? AND book_id = ? AND status = 'Borrowed'");
        $s->bind_param("ii", $user_id, $book_id);
        $s->execute();
        if ($s->get_result()->fetch_assoc()['c'] > 0) throw new RuntimeException("You have already borrowed this book.");

        // 2. Take one copy only if one is left (atomic, so stock never goes below zero)
        $s = $conn->prepare("UPDATE books SET available = available - 1 WHERE id = ? AND available > 0");
        $s->bind_param("i", $book_id);
        $s->execute();
        if ($s->affected_rows === 0) throw new RuntimeException("This book is not available.");

        // 3. Record the borrowing (borrowed_at fills in automatically)
        $s = $conn->prepare("INSERT INTO borrowings (user_id, book_id, status) VALUES (?, ?, 'Borrowed')");
        $s->bind_param("ii", $user_id, $book_id);
        $s->execute();

        $conn->commit();
        $msg = "Book borrowed successfully.";
    } catch (RuntimeException $ex) {
        $conn->rollback();
        $msg = $ex->getMessage();
    } catch (Exception $ex) {
        $conn->rollback();
        $msg = "Error borrowing book.";
    }
}

$books = fetch_books($conn);
page_start("📚 Borrow Book");
flash($msg);
?>
<form method="POST">
    <?= csrf_field() ?>
    <select name="book_id" required>
        <option value="">Select a book</option>
        <?php $cur = null; while ($b = $books->fetch_assoc()):
            if ($b['available'] <= 0) continue;
            $shelf = shelf_of($b);
            if ($shelf !== $cur) { if ($cur !== null) echo '</optgroup>'; echo '<optgroup label="' . e($shelf) . '">'; $cur = $shelf; } ?>
        <option value="<?= $b['id'] ?>"><?= e($b['title']) ?> - <?= $b['available'] ?> available</option>
        <?php endwhile; if ($cur !== null) echo '</optgroup>'; ?>
    </select>
    <button type="submit" name="borrow">Borrow Book</button>
</form>
<?php page_end();
