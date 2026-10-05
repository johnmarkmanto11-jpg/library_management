<?php
require "config.php"; require "layout.php"; require_role();

$id = (int)($_GET['id'] ?? 0);
$s = $conn->prepare("SELECT * FROM books WHERE id = ?");
$s->bind_param("i", $id);
$s->execute();
$book = $s->get_result()->fetch_assoc();

page_start("📖 Book Details");
if ($book): ?>
    <div class="card">
        <h3><?= e($book['title']) ?></h3>
        <p><b>Author:</b> <?= e($book['author']) ?></p>
        <p><b>Category:</b> <?= e($book['category']) ?></p>
        <p><b>Grade Level:</b> <?= e(shelf_of($book)) ?></p>
        <p><b>Total Quantity:</b> <?= $book['quantity'] ?></p>
        <p><b>Available:</b> <?= $book['available'] ?></p>

        <?php if ($_SESSION['role'] === 'Student' && $book['available'] > 0): ?>
        <form method="POST" action="borrow.php">
            <?= csrf_field() ?>
            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
            <button type="submit" name="borrow">Borrow this book</button>
        </form>
        <?php endif; ?>
    </div>
<?php else: ?>
    <p>Book not found. Go to <a href="search.php">Search Books</a> and select a book.</p>
<?php endif;
page_end();
