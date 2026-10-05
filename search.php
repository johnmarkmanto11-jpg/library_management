<?php
require "config.php"; require "layout.php"; require_role();

$q = trim($_GET['search'] ?? '');
$filter = $_GET['grade'] ?? '';
$books = fetch_books($conn, $filter, $q);

page_start("🔎 Browse Books by Grade");
?>
<form method="GET">
    <label>Grade level</label>
    <select name="grade">
        <option value="">All grade levels</option>
        <?php foreach ($SHELVES as $key => $label): ?>
        <option value="<?= e($key) ?>" <?= $key === $filter ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>
    <label>Search (optional)</label>
    <input type="text" name="search" placeholder="Title, author, or category" value="<?= e($q) ?>">
    <button type="submit">Show books</button>
</form>
<?php render_books($books); page_end();
