<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

// Get admin details
$userId = $_SESSION['user_id'];
$admin = getUserById($userId);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $username = sanitize($_POST['username']);
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate current password if trying to change password
    $passwordChanged = false;
    if (!empty($newPassword)) {
        // Verify current password
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!password_verify($currentPassword, $user['password'])) {
            setSessionMessage('error', 'Current password is incorrect.');
            redirect('profile.php');
            exit;
        }
        
        // Check if new password and confirm password match
        if ($newPassword !== $confirmPassword) {
            setSessionMessage('error', 'New password and confirm password do not match.');
            redirect('profile.php');
            exit;
        }
        
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $passwordChanged = true;
    }
    
    // Update user information
    if ($passwordChanged) {
        $sql = "UPDATE users SET full_name = ?, email = ?, username = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $fullName, $email, $username, $hashedPassword, $userId);
    } else {
        $sql = "UPDATE users SET full_name = ?, email = ?, username = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $fullName, $email, $username, $userId);
    }
    
    if ($stmt->execute()) {
        // Update session variables
        $_SESSION['full_name'] = $fullName;
        $_SESSION['username'] = $username;
        
        setSessionMessage('success', 'Profile updated successfully.');
    } else {
        setSessionMessage('error', 'Error updating profile: ' . $conn->error);
    }
    
    redirect('profile.php');
}

// Include header
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include '../includes/sidebar-admin.php'; ?>
    </div>
    <div class="col-md-9">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-lg mb-6 p-6 text-white animate-fadeIn">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold mb-2">My Profile</h2>
                    <p class="text-blue-100">View and update your account information</p>
                </div>
                <div class="hidden md:block">
                    <i class="fas fa-user-cog text-6xl text-blue-200 opacity-50"></i>
                </div>
            </div>
        </div>

        <?php echo displaySessionMessage(); ?>

        <!-- Profile Form -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 animate-fadeIn">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-3 text-white">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-user-edit mr-2"></i> Edit Profile
                </h3>
            </div>
            <div class="p-6">
                <form action="profile.php" method="post">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" value="<?php echo $admin['full_name']; ?>" required>
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" id="email" name="email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" value="<?php echo $admin['email']; ?>" required>
                        </div>
                        
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <input type="text" id="username" name="username" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" value="<?php echo $admin['username']; ?>" required>
                        </div>
                        
                        <div class="md:col-span-2">
                            <h4 class="text-lg font-medium text-gray-700 mb-3 border-b border-gray-200 pb-2">Change Password (optional)</h4>
                            <p class="text-sm text-gray-500 mb-4">Leave these fields empty if you don't want to change your password.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                </div>
                                
                                <div>
                                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                    <input type="password" id="new_password" name="new_password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                </div>
                                
                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-300">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Account Information -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 mt-6 animate-fadeIn">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-3 text-white">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-info-circle mr-2"></i> Account Information
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Account Type</h4>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-user-shield mr-1"></i> Administrator
                            </span>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Last Login</h4>
                        <div class="text-gray-700">
                            <i class="fas fa-clock text-blue-500 mr-1"></i>
                            <?php echo isset($admin['last_login']) ? date('F j, Y, g:i a', strtotime($admin['last_login'])) : 'Not available'; ?>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Account Created</h4>
                        <div class="text-gray-700">
                            <i class="fas fa-calendar-plus text-blue-500 mr-1"></i>
                            <?php echo date('F j, Y', strtotime($admin['created_at'])); ?>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Account Status</h4>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Active
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
