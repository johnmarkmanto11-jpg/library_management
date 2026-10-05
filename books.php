<?php
require "config.php"; require "layout.php"; require_role('Administrator');
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (isset($_POST['add_book'])) {
        $t = trim($_POST['title']); $a = trim($_POST['author']);
        $cat = trim($_POST['category']); $g = $_POST['grade_level'] ?? '';
        $course = ($g === 'College') ? ($_POST['course'] ?? '') : null;
        $q = (int)$_POST['quantity'];
        $valid = $t !== "" && $a !== "" && $cat !== "" && $q > 0 && in_array($g, $GRADES, true)
                 && ($g !== 'College' || in_array($course, $COURSES, true));
        if ($valid) {
            $s = $conn->prepare("INSERT INTO books (title, author, category, grade_level, course, quantity, available) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $s->bind_param("sssssii", $t, $a, $cat, $g, $course, $q, $q);
            $s->execute();
            $msg = "Book added under " . ($course ? "College - $course" : $g) . ".";
        } else {
            $msg = "Please fill in all fields. College books also need a course.";
        }
    }

    if (isset($_POST['delete_book'])) {
        $id = (int)$_POST['book_id'];
        $s = $conn->prepare("DELETE FROM books WHERE id = ?");
        $s->bind_param("i", $id);
        $s->execute();
        $msg = "Book deleted successfully.";
    }
}

$filter = $_GET['grade'] ?? '';
$books = fetch_books($conn, $filter);

page_start("📚 Manage Books");
flash($msg);
?>
<h3>Add New Book</h3>
<form method="POST">
    <?= csrf_field() ?>
    <label>Grade Level</label>
    <select name="grade_level" id="grade" required>
        <option value="">Select grade level</option>
        <?php foreach ($GRADES as $g): ?><option value="<?= e($g) ?>"><?= e($g) ?></option><?php endforeach; ?>
    </select>

    <div id="courseBox" style="display:none">
        <label>Course (College only)</label>
        <select name="course" id="course">
            <option value="">Select course</option>
            <?php foreach ($COURSES as $c): ?><option value="<?= e($c) ?>"><?= e($c) ?></option><?php endforeach; ?>
        </select>
    </div>

    <label>Book Title</label><input type="text" name="title" required>
    <label>Author</label><input type="text" name="author" required>
    <label>Category / Subject</label><input type="text" name="category" placeholder="e.g. Math, Science, Programming" required>
    <label>Quantity</label><input type="number" name="quantity" min="1" required>
    <button type="submit" name="add_book">Add Book</button>
</form>
<script>
    const grade = document.getElementById('grade'), box = document.getElementById('courseBox'), course = document.getElementById('course');
    grade.addEventListener('change', () => {
        const college = grade.value === 'College';
        box.style.display = college ? 'block' : 'none';
        course.required = college;
        if (!college) course.value = '';
    });
</script>

<h3>Book List</h3>
<form method="GET">
    <label>Show books for</label>
    <select name="grade" onchange="this.form.submit()">
        <option value="">All grade levels</option>
        <?php foreach ($SHELVES as $key => $label): ?>
        <option value="<?= e($key) ?>" <?= $key === $filter ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>
    <noscript><button type="submit">Filter</button></noscript>
</form>
<?php render_books($books, true); page_end();
