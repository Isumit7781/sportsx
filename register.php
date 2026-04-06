<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/');
    } else {
        redirect('player/');
    }
}

$error = '';
$success = '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = sanitize($_POST['email']);
    $full_name = sanitize($_POST['full_name']);

    // Validate input
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($full_name)) {
        $error = 'Please fill in all fields';
    } elseif ($password != $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Register the user
        $result = registerUser($username, $password, $email, $full_name);

        if ($result['success']) {
            // Auto-login the user after registration
            $loginResult = loginUser($username, $password);
            if ($loginResult['success']) {
                // Redirect based on role
                if ($_SESSION['role'] == ROLE_ADMIN) {
                    redirect('admin/');
                } else {
                    redirect('player/');
                }
            } else {
                $success = $result['message'] . '. You can now <a href="login.php">login</a>.';
            }
        } else {
            $error = $result['message'];
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div class="auth-wrapper">
    <div class="card auth-card">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-user-plus me-2"></i>Register
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="post" action="" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                    <div class="invalid-feedback">Please enter your full name</div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="invalid-feedback">Please enter a valid email address</div>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                    <div class="invalid-feedback">Please choose a username</div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="invalid-feedback">Please enter a password</div>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <div class="invalid-feedback">Please confirm your password</div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </form>

            <div class="mt-3 text-center">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
