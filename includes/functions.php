<?php
require_once 'config.php';

/**
 * Get a random avatar based on gender
 * @param string $gender The gender (male, female, or null)
 * @return string The path to the avatar image
 */
function getRandomAvatar($gender = null) {
    // Default avatar path
    $defaultAvatar = 'assets/images/avatars/male/avatar1.png';

    // If no gender specified or invalid gender, return a random avatar
    if (!in_array($gender, ['male', 'female'])) {
        $gender = rand(0, 1) ? 'male' : 'female';
    }

    // Get the avatar directory based on gender
    $avatarDir = 'assets/images/avatars/' . $gender;
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $avatarDir;

    // Check if directory exists
    if (!is_dir($fullPath)) {
        return $defaultAvatar;
    }

    // Get all PNG files in the directory
    $avatars = glob($fullPath . '/*.png');

    // If no avatars found, return default
    if (empty($avatars)) {
        return $defaultAvatar;
    }

    // Select a random avatar
    $randomAvatar = $avatars[array_rand($avatars)];

    // Convert to web path
    $webPath = str_replace($_SERVER['DOCUMENT_ROOT'] . '/', '', $randomAvatar);

    return $webPath;
}

/**
 * Redirect to a specific page
 */
function redirect($url) {
    // Remove leading slash if present to avoid double slashes
    if (substr($url, 0, 1) === '/') {
        $url = substr($url, 1);
    }

    // Construct the full URL
    $redirectUrl = BASE_URL . $url;

    header("Location: " . $redirectUrl);
    exit;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == ROLE_ADMIN;
}

/**
 * Check if user is player
 */
function isPlayer() {
    return isset($_SESSION['role']) && $_SESSION['role'] == ROLE_PLAYER;
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(trim($data));
}

/**
 * Display error message
 */
function displayError($message) {
    return '<div class="alert alert-danger">' . $message . '</div>';
}

/**
 * Display success message
 */
function displaySuccess($message) {
    return '<div class="alert alert-success">' . $message . '</div>';
}

/**
 * Get user details by ID
 */
function getUserById($userId) {
    global $conn;
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        return $result->fetch_assoc();
    }

    return false;
}

/**
 * Get event details by ID
 */
function getEventById($eventId) {
    global $conn;
    $sql = "SELECT * FROM events WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        return $result->fetch_assoc();
    }

    return false;
}

/**
 * Check if a player is registered for an event
 */
function isPlayerRegistered($userId, $eventId) {
    global $conn;
    $sql = "SELECT id FROM registrations WHERE user_id = ? AND event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $eventId);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}

/**
 * Format date for display
 */
function formatDate($date) {
    return date("F j, Y", strtotime($date));
}

/**
 * Get count of upcoming events
 */
function getUpcomingEventsCount() {
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM events WHERE event_date >= CURDATE()";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['count'];
}

/**
 * Get count of registered players for an event
 */
function getEventRegistrationsCount($eventId) {
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM registrations WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'];
}

/**
 * Get count of total players
 */
function getTotalPlayersCount() {
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM users WHERE role = 'player'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['count'];
}

/**
 * Set a session message to be displayed on the next page load
 * @param string $type The type of message (success, error, warning, info)
 * @param string $message The message text
 */
function setSessionMessage($type, $message) {
    $_SESSION['message_type'] = $type;
    $_SESSION['message'] = $message;
}

/**
 * Display session message if exists and clear it
 */
function displaySessionMessage() {
    if (isset($_SESSION['message'])) {
        $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
        $alertClass = 'alert-info';

        if ($type == 'success') $alertClass = 'alert-success';
        else if ($type == 'error') $alertClass = 'alert-danger';
        else if ($type == 'warning') $alertClass = 'alert-warning';

        $message = $_SESSION['message'];

        // Clear the message
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);

        return '<div class="alert ' . $alertClass . '">' . $message . '</div>';
    }

    return '';
}

function getEventParticipants($eventId) {
    global $conn;
    $participants = [];
    $sql = "SELECT p.name FROM participants p WHERE p.event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $participants[] = $row;
    }
    $stmt->close();
    return $participants;
}
?>
