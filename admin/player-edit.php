<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$error = '';
$success = '';

// Check if player ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('players.php');
}

$playerId = (int)$_GET['id'];

// Get player details
$player = getUserById($playerId);

if (!$player || $player['role'] != ROLE_PLAYER) {
    redirect('players.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : null;
    $gender = isset($_POST['gender']) ? sanitize($_POST['gender']) : null;
    $date_of_birth = isset($_POST['date_of_birth']) ? sanitize($_POST['date_of_birth']) : null;
    $address = isset($_POST['address']) ? sanitize($_POST['address']) : null;
    
    // Validate input
    if (empty($full_name) || empty($email)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if email is already used by another user
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $playerId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email is already used by another user';
        } else {
            // Update player details
            $data = [
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'gender' => $gender,
                'date_of_birth' => $date_of_birth,
                'address' => $address
            ];
            
            $result = updateUserProfile($playerId, $data);
            
            if ($result['success']) {
                $success = 'Player updated successfully';
                // Refresh player data
                $player = getUserById($playerId);
            } else {
                $error = $result['message'];
            }
        }
    }
    
    // Handle password change if provided
    if (!empty($_POST['new_password'])) {
        $newPassword = $_POST['new_password'];
        
        if (strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters long';
        } else {
            // Update password directly (admin doesn't need to know old password)
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hashedPassword, $playerId);
            
            if ($stmt->execute()) {
                $success = 'Player updated successfully with new password';
            } else {
                $error = 'Failed to update password: ' . $conn->error;
            }
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include '../includes/sidebar-admin.php'; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Player</h2>
            <a href="players.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Players
            </a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="post" action="" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?php echo $player['username']; ?>" readonly>
                            <small class="text-muted">Username cannot be changed</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" minlength="6">
                            <small class="text-muted">Leave blank to keep current password</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo $player['full_name']; ?>" required>
                            <div class="invalid-feedback">Please enter full name</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $player['email']; ?>" required>
                            <div class="invalid-feedback">Please enter a valid email</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $player['phone']; ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo $player['date_of_birth']; ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="male" <?php echo $player['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo $player['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo $player['gender'] == 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="created_at" class="form-label">Registration Date</label>
                            <input type="text" class="form-control" id="created_at" value="<?php echo date('F j, Y', strtotime($player['created_at'])); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo $player['address']; ?></textarea>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">Update Player</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
