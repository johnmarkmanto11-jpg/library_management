<?php
require "config.php"; require "layout.php"; require_role('Administrator');
$msg = "";

if (isset($_POST['add_user'])) {
    csrf_check();
    $username = trim($_POST['username']); $name = trim($_POST['name']);
    $role = $_POST['role'] === 'Administrator' ? 'Administrator' : 'Student';
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $s = $conn->prepare("INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)");
        $s->bind_param("ssss", $username, $hash, $name, $role);
        $s->execute();
        $msg = "User added successfully.";
    } catch (mysqli_sql_exception $ex) {
        $msg = "Could not add user. That username may already exist.";
    }
}

$users = $conn->query("SELECT id, username, name, role FROM users ORDER BY id DESC");
page_start("👥 Manage Users");
flash($msg);
?>
<h3>Add New User</h3>
<form method="POST">
    <?= csrf_field() ?>
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="text" name="name" placeholder="Full Name" required>
    <select name="role">
        <option value="Student">Student</option>
        <option value="Administrator">Administrator</option>
    </select>
    <button type="submit" name="add_user">Add User</button>
</form>

<h3>User List</h3>
<table>
    <tr><th>ID</th><th>Username</th><th>Name</th><th>Role</th></tr>
    <?php while ($u = $users->fetch_assoc()): ?>
    <tr>
        <td><?= $u['id'] ?></td>
        <td><?= e($u['username']) ?></td>
        <td><?= e($u['name']) ?></td>
        <td><?= e($u['role']) ?></td>
    </tr>
    <?php endwhile; ?>
</table>
<?php page_end();
