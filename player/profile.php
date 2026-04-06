<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in and is player
if (!isLoggedIn() || !isPlayer()) {
    redirect('../index.php');
}

$error = '';
$success = '';
$passwordError = '';
$passwordSuccess = '';

// Get player details
$player = getUserById($_SESSION['user_id']);

// Process profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
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
        $stmt->bind_param("si", $email, $_SESSION['user_id']);
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

            $result = updateUserProfile($_SESSION['user_id'], $data);

            if ($result['success']) {
                $success = 'Profile updated successfully';
                // Update session variables
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                // Refresh player data
                $player = getUserById($_SESSION['user_id']);
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Process password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $passwordError = 'Please fill in all password fields';
    } elseif ($new_password != $confirm_password) {
        $passwordError = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $passwordError = 'New password must be at least 6 characters long';
    } else {
        // Change password
        $result = changePassword($_SESSION['user_id'], $current_password, $new_password);

        if ($result['success']) {
            $passwordSuccess = $result['message'];
        } else {
            $passwordError = $result['message'];
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include '../includes/sidebar-player.php'; ?>
    </div>
    <div class="col-md-9">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg mb-6 p-6 text-white animate-fadeIn">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold mb-2">My Profile</h2>
                    <p class="text-blue-100">View and update your personal information</p>
                </div>
                <div class="hidden md:block">
                    <i class="fas fa-user-cog text-6xl text-blue-200 opacity-50"></i>
                </div>
            </div>
        </div>

        <!-- Profile Header -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 mb-6 animate-fadeIn">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-3 text-white">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-id-card me-2"></i> Profile Information
                </h3>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        <div class="position-relative d-inline-block">
                            <?php
                            // Use profile image if exists, otherwise use random avatar based on gender
                            $profileImage = $player['profile_image'] ? '../uploads/' . $player['profile_image'] : '../' . getRandomAvatar($player['gender']);
                            ?>
                            <img src="<?php echo $profileImage; ?>" alt="Profile" class="rounded-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                    </div>
                    <div class="col-md-9">
                        <h3 class="mb-2 d-flex align-items-center">
                            <?php echo $player['full_name']; ?>
                        </h3>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><i class="fas fa-user me-2 text-blue-500"></i><strong>Username:</strong> <?php echo $player['username']; ?></p>
                                <p class="mb-2"><i class="fas fa-envelope me-2 text-blue-500"></i><strong>Email:</strong> <?php echo $player['email']; ?></p>
                                <p class="mb-2"><i class="fas fa-phone me-2 text-blue-500"></i><strong>Phone:</strong> <?php echo $player['phone'] ? $player['phone'] : 'Not provided'; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><i class="fas fa-venus-mars me-2 text-blue-500"></i><strong>Gender:</strong> <?php echo $player['gender'] ? ucfirst($player['gender']) : 'Not provided'; ?></p>
                                <p class="mb-2"><i class="fas fa-birthday-cake me-2 text-blue-500"></i><strong>Date of Birth:</strong> <?php echo $player['date_of_birth'] ? date('F j, Y', strtotime($player['date_of_birth'])) : 'Not provided'; ?></p>
                                <p class="mb-2"><i class="fas fa-calendar me-2 text-blue-500"></i><strong>Member since:</strong> <?php echo date('F Y', strtotime($player['created_at'])); ?></p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Update Form -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 mb-6 animate-fadeIn">
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 py-3 text-white">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-user-edit me-2"></i> Update Profile
                </h3>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="post" action="" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?php echo $player['username']; ?>" readonly>
                            <small class="text-muted">Username cannot be changed</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo $player['full_name']; ?>" required>
                            <div class="invalid-feedback">Please enter your full name</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $player['email']; ?>" required>
                            <div class="invalid-feedback">Please enter a valid email</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $player['phone']; ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo $player['date_of_birth']; ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="male" <?php echo $player['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo $player['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo $player['gender'] == 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo $player['address']; ?></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password Form -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 mb-6 animate-fadeIn">
            <div class="bg-gradient-to-r from-gray-600 to-gray-700 px-4 py-3 text-white">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-key me-2"></i> Change Password
                </h3>
            </div>
            <div class="card-body p-4">
                <?php if ($passwordError): ?>
                    <div class="alert alert-danger"><?php echo $passwordError; ?></div>
                <?php endif; ?>

                <?php if ($passwordSuccess): ?>
                    <div class="alert alert-success"><?php echo $passwordSuccess; ?></div>
                <?php endif; ?>

                <form method="post" action="" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                        <div class="invalid-feedback">Please enter your current password</div>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                        <div class="invalid-feedback">New password must be at least 6 characters long</div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <div class="invalid-feedback">Please confirm your new password</div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
