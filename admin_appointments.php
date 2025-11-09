<?php
// Start session at the very beginning
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "insurance";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if admin is already logged in via session
$admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

$login_error = "";

// Admin login logic
if (isset($_POST['admin_login'])) {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $login_error = "Invalid request. Please try again.";
    } else {
        $admin_username = $_POST['admin_username'];
        $admin_password = $_POST['admin_password'];
        
        // Hardcoded credentials
        $valid_username = "vikas";
        $valid_password = "password12";
        
        if ($admin_username === $valid_username && $admin_password === $valid_password) {
            $admin_logged_in = true;
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin_username;
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
        } else {
            $login_error = "Invalid username or password";
        }
    }
}
// Appointment management
$action_message = "";

// Handle appointment status updates
if (isset($_POST['update_status'])) {
    // Check if admin is logged in
    if (!$admin_logged_in) {
        $action_message = "Error: You must be logged in to perform this action.";
    } 
    // CSRF validation
    elseif (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $action_message = "Error: Invalid request token.";
    } 
    else {
        $appointment_id = $_POST['appointment_id'];
        $new_status = $_POST['new_status'];
        
        $update_sql = "UPDATE appointments SET status = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $new_status, $appointment_id);
        
        if ($update_stmt->execute()) {
            $action_message = "Appointment status updated successfully!";
        } else {
            $action_message = "Error updating status: " . $update_stmt->error;
        }
        $update_stmt->close();
    }
}

// Handle appointment deletion
if (isset($_POST['delete_appointment'])) {
    // Check if admin is logged in
    if (!$admin_logged_in) {
        $action_message = "Error: You must be logged in to perform this action.";
    }
    // CSRF validation
    elseif (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $action_message = "Error: Invalid request token.";
    }
    else {
        $appointment_id = $_POST['appointment_id'];
        
        $delete_sql = "DELETE FROM appointments WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $appointment_id);
        
        if ($delete_stmt->execute()) {
            $action_message = "Appointment deleted successfully!";
        } else {
            $action_message = "Error deleting appointment: " . $delete_stmt->error;
        }
        $delete_stmt->close();
    }
}

// Get appointments with search/filter
$search_query = '';
$status_filter = '';
$where_clauses = [];
$params = [];
$types = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $where_clauses[] = "(full_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= 'sss';
    $search_query = $_GET['search'];
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where_clauses[] = "status = ?";
    $params[] = $_GET['status'];
    $types .= 's';
    $status_filter = $_GET['status'];
}

$sql = "SELECT * FROM appointments";
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql .= " ORDER BY created_at DESC";

$appointments = [];

if ($admin_logged_in) {
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $appointments[] = $row;
                }
            }
            $stmt->close();
        }
    } else {
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
        }
    }
}

// Get status counts for stats
$status_counts = [];
if ($admin_logged_in) {
    $status_types = ['New', 'Contacted', 'Scheduled', 'Completed', 'Cancelled'];
    
    foreach ($status_types as $status) {
        $count_sql = "SELECT COUNT(*) as count FROM appointments WHERE status = ?";
        $count_stmt = $conn->prepare($count_sql);
        if ($count_stmt) {
            $count_stmt->bind_param("s", $status);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count_row = $count_result->fetch_assoc();
            $status_counts[$status] = $count_row['count'];
            $count_stmt->close();
        }
    }
}

// Admin logout
if (isset($_GET['logout'])) {
    $_SESSION = array();
    session_destroy();
    header("Location: admin_appointments.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Appointment Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background: #f4f7fa;
    color: #333;
    line-height: 1.6;
    padding: 20px;
}

.container {
    max-width: 1200px;
    margin: auto;
    background: #ffffff;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Header */
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

header h1 {
    font-size: 28px;
    color: #1e3a8a;
}

.admin-controls a {
    margin-left: 15px;
    text-decoration: none;
    color: #ffffff;
    background-color: #1e3a8a;
    padding: 8px 16px;
    border-radius: 8px;
    transition: background-color 0.3s ease;
}

.admin-controls a:hover {
    background-color: #2c5282;
}

/* Login */
.login-container {
    background-color: #eef2f7;
    padding: 30px;
    border-radius: 10px;
    max-width: 400px;
    margin: auto;
}

.login-container h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #1e3a8a;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
}

.form-group input,
.search-form select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
}

button {
    padding: 10px 18px;
    background-color: #2563eb;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #1d4ed8;
}

.alert {
    background-color: #fef3c7;
    color: #92400e;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 500;
}

.alert-error {
    background-color: #fecaca;
    color: #991b1b;
}

/* Stats */
.stats-container {
    display: flex;
    justify-content: space-between;
    margin: 30px 0;
    gap: 10px;
    flex-wrap: wrap;
}

.stat-box {
    flex: 1;
    min-width: 100px;
    background-color: #e0f2fe;
    padding: 20px;
    text-align: center;
    border-radius: 12px;
    box-shadow: 0 0 5px rgba(0,0,0,0.05);
}

.stat-box span {
    display: block;
    font-size: 14px;
    margin-bottom: 5px;
    color: #0369a1;
}

.stat-box strong {
    font-size: 20px;
    color: #0c4a6e;
}

/* Filter/Search */
.filter-container {
    margin-bottom: 25px;
    padding: 20px;
    background: #f1f5f9;
    border-radius: 12px;
}

.search-form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: center;
}

.search-form .form-group {
    flex: 1;
    min-width: 200px;
}

.search-form a {
    color: #ef4444;
    text-decoration: none;
    font-weight: bold;
}

/* Table */
.appointments-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.appointments-table thead {
    background-color: #1e3a8a;
    color: #ffffff;
}

.appointments-table th, .appointments-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.appointments-table tr:hover {
    background-color: #f0f9ff;
}

/* Status badges */
.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-block;
}

.status-new { background-color: #e0f2fe; color: #0369a1; }
.status-contacted { background-color: #fef9c3; color: #ca8a04; }
.status-scheduled { background-color: #d9f99d; color: #3f6212; }
.status-completed { background-color: #bbf7d0; color: #065f46; }
.status-cancelled { background-color: #fecaca; color: #b91c1c; }

/* Actions */
.actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.btn-edit {
    background-color: #0284c7;
    color: #fff;
    padding: 6px 12px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
}

.btn-edit:hover {
    background-color: #0369a1;
}

.delete-form button {
    background-color: #dc2626;
}

.delete-form button:hover {
    background-color: #b91c1c;
}

.update-form {
    margin-top: 10px;
    display: flex;
    gap: 10px;
    align-items: center;
}

.no-data {
    text-align: center;
    padding: 20px;
    font-size: 18px;
    color: #64748b;
}
</style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Mint Insure - Admin Panel</h1>
            <?php if ($admin_logged_in): ?>
                <div class="admin-controls">
                    <a href="admin_appointments.php?logout=1">Logout</a>
                    <a href="insurance.php">View Website</a>
                </div>
            <?php endif; ?>
        </header>
        
        <?php if (!$admin_logged_in): ?>
            <div class="login-container">
                <h2>Admin Login</h2>
                <?php if (!empty($login_error)): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>
                
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <label for="admin_username">Username</label>
                        <input type="text" id="admin_username" name="admin_username" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_password">Password</label>
                        <input type="password" id="admin_password" name="admin_password" required>
                    </div>
                    <button type="submit" name="admin_login">Login</button>
                </form>
            </div>
        <?php else: ?>
            <h2>Appointment Manager</h2>
            
            <?php if (!empty($action_message)): ?>
                <div class="alert"><?php echo htmlspecialchars($action_message); ?></div>
            <?php endif; ?>
            
            <div class="stats-container">
                <div class="stat-box">
                    <span>New</span>
                    <strong><?php echo $status_counts['New'] ?? 0; ?></strong>
                </div>
                <div class="stat-box">
                    <span>Contacted</span>
                    <strong><?php echo $status_counts['Contacted'] ?? 0; ?></strong>
                </div>
                <div class="stat-box">
                    <span>Scheduled</span>
                    <strong><?php echo $status_counts['Scheduled'] ?? 0; ?></strong>
                </div>
                <div class="stat-box">
                    <span>Completed</span>
                    <strong><?php echo $status_counts['Completed'] ?? 0; ?></strong>
                </div>
                <div class="stat-box">
                    <span>Cancelled</span>
                    <strong><?php echo $status_counts['Cancelled'] ?? 0; ?></strong>
                </div>
            </div>
            
            <div class="filter-container">
                <form method="get" class="search-form">
                    <div class="form-group">
                        <input type="text" name="search" placeholder="Search by name, email or phone" value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    <div class="form-group">
                        <select name="status">
                            <option value="">All Statuses</option>
                            <option value="New" <?php echo $status_filter === 'New' ? 'selected' : ''; ?>>New</option>
                            <option value="Contacted" <?php echo $status_filter === 'Contacted' ? 'selected' : ''; ?>>Contacted</option>
                            <option value="Scheduled" <?php echo $status_filter === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                            <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit">Filter</button>
                    <a href="admin_appointments.php">Clear Filters</a>
                </form>
            </div>
            
            <?php if (empty($appointments)): ?>
                <div class="no-data">No appointments found.</div>
            <?php else: ?>
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['id']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['phone']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['email']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($appointment['created_at'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                        <?php echo htmlspecialchars($appointment['status']); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <button class="btn-edit" onclick="showUpdateForm(<?php echo $appointment['id']; ?>)">
                                        <i class="fas fa-edit"></i> Update
                                    </button>
                                    
                                    <form method="post" class="update-form" id="update-form-<?php echo $appointment['id']; ?>" style="display: none;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <select name="new_status">
                                            <option value="New" <?php echo $appointment['status'] === 'New' ? 'selected' : ''; ?>>New</option>
                                            <option value="Contacted" <?php echo $appointment['status'] === 'Contacted' ? 'selected' : ''; ?>>Contacted</option>
                                            <option value="Scheduled" <?php echo $appointment['status'] === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                            <option value="Completed" <?php echo $appointment['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="Cancelled" <?php echo $appointment['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status">Save</button>
                                        <button type="button" onclick="hideUpdateForm(<?php echo $appointment['id']; ?>)">Cancel</button>
                                    </form>
                                    
                                    <form method="post" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this appointment?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <button type="submit" name="delete_appointment">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <script>
        function showUpdateForm(id) {
            document.getElementById('update-form-' + id).style.display = 'flex';
        }
        
        function hideUpdateForm(id) {
            document.getElementById('update-form-' + id).style.display = 'none';
        }
    </script>
</body>
</html>