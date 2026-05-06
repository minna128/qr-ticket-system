<?php
/**
 * Admin - Staff Management
 * World Play QR Ticketing System
 */

$adminPageTitle = 'Staff Management';
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDBConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = trim($_POST['name']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $role = $_POST['role'];
        
        if ($name && $username && $password && in_array($role, ['entry', 'exit', 'both'])) {
            // Check if username exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM staff WHERE username = :username");
            $stmt->execute([':username' => $username]);
            
            if ($stmt->fetchColumn() > 0) {
                setFlash('danger', 'Username already exists.');
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare(
                    "INSERT INTO staff (name, username, password_hash, role) VALUES (:name, :username, :hash, :role)"
                );
                $stmt->execute([':name' => $name, ':username' => $username, ':hash' => $hash, ':role' => $role]);
                setFlash('success', 'Staff member added successfully.');
            }
        } else {
            setFlash('danger', 'All fields are required.');
        }
    }
    
    if ($action === 'toggle') {
        $id = (int)$_POST['staff_id'];
        $stmt = $pdo->prepare("UPDATE staff SET is_active = NOT is_active WHERE id = :id");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Staff status updated.');
    }
    
    if ($action === 'reset_password') {
        $id = (int)$_POST['staff_id'];
        $newPassword = $_POST['new_password'];
        
        if ($newPassword) {
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE staff SET password_hash = :hash WHERE id = :id");
            $stmt->execute([':hash' => $hash, ':id' => $id]);
            setFlash('success', 'Password reset successfully.');
        }
    }
    
    header("Location: " . BASE_URL . "/admin/staff.php");
    exit;
}

// Fetch staff
$stmt = $pdo->query("SELECT * FROM staff ORDER BY created_at DESC");
$staffList = $stmt->fetchAll();
?>

<?php displayFlash(); ?>

<div class="admin-card">
    <div class="card-header">
        <h3>Add New Staff Member</h3>
    </div>
    <div class="card-body">
        <form method="POST" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; align-items: end;">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="add">
            
            <div class="form-group" style="margin:0;">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="John Doe" required>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="john_doe" required>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Role</label>
                <select name="role" class="form-control" required>
                    <option value="both">Both (Entry & Exit)</option>
                    <option value="entry">Entry Only</option>
                    <option value="exit">Exit Only</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-secondary btn-block">Add Staff</button>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="card-header">
        <h3>Staff List</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staffList as $s): ?>
                    <tr>
                        <td><?php echo sanitize($s['name']); ?></td>
                        <td><strong><?php echo sanitize($s['username']); ?></strong></td>
                        <td>
                            <span class="badge badge-completed"><?php echo ucfirst($s['role']); ?></span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $s['is_active'] ? 'active' : 'expired'; ?>">
                                <?php echo $s['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($s['created_at'])); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="staff_id" value="<?php echo $s['id']; ?>">
                                <button type="submit" class="btn btn-outline" style="padding: 4px 10px; font-size: 0.8rem;">
                                    <?php echo $s['is_active'] ? 'Disable' : 'Enable'; ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</main>
</div>
<script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
</body>
</html>
