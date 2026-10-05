<?php
require "config.php"; require "layout.php"; require_role('Administrator');

$one = fn($sql) => $conn->query($sql)->fetch_assoc()['c'];
$stats = [
    "Different Books"    => $one("SELECT COUNT(*) c FROM books"),
    "Total Copies"       => $one("SELECT COALESCE(SUM(quantity),0) c FROM books"),
    "Available Copies"   => $one("SELECT COALESCE(SUM(available),0) c FROM books"),
    "Currently Borrowed" => $one("SELECT COUNT(*) c FROM borrowings WHERE status='Borrowed'"),
    "Returned Books"     => $one("SELECT COUNT(*) c FROM borrowings WHERE status='Returned'"),
];
page_start("📊 Library Reports");
?>
<div class="cards">
    <?php foreach ($stats as $label => $n): ?>
    <div class="card"><h3><?= $n ?></h3><p><?= e($label) ?></p></div>
    <?php endforeach; ?>
</div>
<?php page_end();
