<?php
require_once 'config.php';
require_once 'functions.php';

/**
 * Register a new user
 */
function registerUser($username, $password, $email, $fullName, $role = 'player') {
    global $conn;

    // Check if username already exists
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Username already exists'];
    }

    // Check if email already exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Email already exists'];
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Generate a random profile image
    $gender = null; // Random gender for avatar
    $profileImage = getRandomAvatar($gender);

    // Insert new user
    $sql = "INSERT INTO users (username, password, email, full_name, role, profile_image) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $username, $hashedPassword, $email, $fullName, $role, $profileImage);

    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Registration successful', 'user_id' => $conn->insert_id];
    } else {
        return ['success' => false, 'message' => 'Registration failed: ' . $conn->error];
    }
}

/**
 * Login a user
 */
function loginUser($username, $password) {
    global $conn;

    $sql = "SELECT id, username, password, email, full_name, role, profile_image FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Password is correct, start a new session if not already started
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            // Store data in session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['profile_image'] = $user['profile_image'];

            // Update last login time
            $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();

            return ['success' => true, 'message' => 'Login successful', 'role' => $user['role']];
        } else {
            return ['success' => false, 'message' => 'Invalid password'];
        }
    } else {
        return ['success' => false, 'message' => 'Username not found'];
    }
}

/**
 * Logout a user
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = [];

    // Destroy the session
    session_destroy();

    return ['success' => true, 'message' => 'Logout successful'];
}

/**
 * Update user profile
 */
function updateUserProfile($userId, $data) {
    global $conn;

    $fields = [];
    $types = '';
    $values = [];

    // Build the query dynamically based on provided data
    foreach ($data as $field => $value) {
        if (in_array($field, ['full_name', 'email', 'phone', 'date_of_birth', 'gender', 'address'])) {
            $fields[] = "$field = ?";
            $types .= 's';
            $values[] = $value;
        }
    }

    if (empty($fields)) {
        return ['success' => false, 'message' => 'No valid fields to update'];
    }

    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
    $types .= 'i';
    $values[] = $userId;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Profile updated successfully'];
    } else {
        return ['success' => false, 'message' => 'Profile update failed: ' . $conn->error];
    }
}

/**
 * Change user password
 */
function changePassword($userId, $currentPassword, $newPassword) {
    global $conn;

    // Get current password
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify current password
        if (password_verify($currentPassword, $user['password'])) {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hashedPassword, $userId);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Password changed successfully'];
            } else {
                return ['success' => false, 'message' => 'Password change failed: ' . $conn->error];
            }
        } else {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
    } else {
        return ['success' => false, 'message' => 'User not found'];
    }
}
?>
