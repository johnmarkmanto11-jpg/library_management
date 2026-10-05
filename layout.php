<?php
// Shared page header (navbar) and footer, so each page stays short.
function page_start($title) {
    $right = isset($_SESSION['user_id'])
        ? '<a href="dashboard.php">Dashboard</a> | ' . e($_SESSION['name']) . ' (' . e($_SESSION['role']) . ') | <a href="logout.php">Logout</a>'
        : '';
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . e($title) . '</title><link rel="stylesheet" href="style.css"></head><body>'
       . '<nav class="navbar"><div><b>📚 Library System</b></div><div>' . $right . '</div></nav>'
       . '<div class="container"><h2>' . e($title) . '</h2>';
}
function page_end() { echo '</div></body></html>'; }
function flash($m) { if ($m) echo '<p class="msg">' . e($m) . '</p>'; }

// Prints books grouped under a heading per grade (College is split by course).
function render_books($res, $admin = false) {
    if ($res->num_rows === 0) { echo '<p>No books found.</p>'; return; }
    $cur = null;
    while ($b = $res->fetch_assoc()) {
        $shelf = shelf_of($b);
        if ($shelf !== $cur) {
            if ($cur !== null) echo '</table>';
            $cur = $shelf;
            echo '<h3>' . e($shelf) . '</h3><table><tr><th>ID</th><th>Title</th><th>Author</th><th>Category</th>'
               . ($admin ? '<th>Quantity</th>' : '') . '<th>Available</th>' . ($admin ? '<th>Action</th>' : '') . '</tr>';
        }
        echo '<tr><td>' . $b['id'] . '</td><td>'
           . ($admin ? e($b['title']) : '<a href="book_details.php?id=' . $b['id'] . '">' . e($b['title']) . '</a>')
           . '</td><td>' . e($b['author']) . '</td><td>' . e($b['category']) . '</td>'
           . ($admin ? '<td>' . $b['quantity'] . '</td>' : '') . '<td>' . $b['available'] . '</td>';
        if ($admin) {
            echo '<td><form method="POST">' . csrf_field() . '<input type="hidden" name="book_id" value="' . $b['id'] . '">'
               . '<button type="submit" name="delete_book" onclick="return confirm(\'Delete this book and its borrowing records?\')">Delete</button></form></td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}
