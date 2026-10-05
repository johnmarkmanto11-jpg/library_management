<?php
// Database connection + shared helpers. Every page includes this first.
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli("localhost", "root", "", "library_management");
    $conn->set_charset("utf8mb4");
} catch (Exception $ex) {
    die("Database connection failed. Start MySQL in XAMPP, then run install.php once.");
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function require_role($role = null) {
    if (!isset($_SESSION['user_id']) || ($role && $_SESSION['role'] !== $role)) {
        header("Location: index.php");
        exit;
    }
}

function csrf_field() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}

function csrf_check() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        die("Invalid request. Go back, refresh the page, and try again.");
    }
}

// Grade levels used for books (Grade 1 to Grade 12, then College)
$GRADES = [];
for ($i = 1; $i <= 12; $i++) $GRADES[] = "Grade $i";
$GRADES[] = "College";

// College courses
$COURSES = ["BSIT", "BSCRIM", "BEED", "BSOAD"];

// Shelf choices for filters: each grade, all of College, and each college course
$SHELVES = [];
foreach ($GRADES as $g) {
    $SHELVES[$g] = $g === "College" ? "College (all courses)" : $g;
    if ($g === "College") foreach ($COURSES as $c) $SHELVES["College|$c"] = "College - $c";
}

// Heading a book belongs under, e.g. "Grade 4" or "College - BSIT"
function shelf_of($b) {
    return $b['grade_level'] === 'College' ? 'College - ' . ($b['course'] ?: 'Unassigned') : $b['grade_level'];
}

// Fetch books ordered Grade 1 -> College (then by course, then title). $filter is a key of $SHELVES.
function fetch_books($conn, $filter = '', $search = '') {
    global $GRADES, $SHELVES;
    if (!isset($SHELVES[$filter])) $filter = '';
    $where = []; $types = ''; $vals = [];
    if ($filter !== '') {
        [$g, $c] = array_pad(explode('|', $filter, 2), 2, null);
        $where[] = "grade_level = ?"; $types .= 's'; $vals[] = $g;
        if ($c) { $where[] = "course = ?"; $types .= 's'; $vals[] = $c; }
    }
    if ($search !== '') {
        $where[] = "(title LIKE ? OR author LIKE ? OR category LIKE ? OR grade_level LIKE ? OR course LIKE ?)";
        $types .= 'sssss'; $t = "%$search%"; array_push($vals, $t, $t, $t, $t, $t);
    }
    $sql = "SELECT * FROM books" . ($where ? " WHERE " . implode(" AND ", $where) : "")
         . " ORDER BY FIELD(grade_level,'" . implode("','", $GRADES) . "'), course, title";
    $s = $conn->prepare($sql);
    if ($vals) $s->bind_param($types, ...$vals);
    $s->execute();
    return $s->get_result();
}
