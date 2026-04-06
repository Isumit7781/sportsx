<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is player
if (!isLoggedIn() || !isPlayer()) {
    redirect('../index.php');
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['event_id']) || !isset($_POST['team_id'])) {
    redirect('events.php');
}

$eventId = (int)$_POST['event_id'];
$teamId = (int)$_POST['team_id'];

// Validate event
$sql = "SELECT * FROM events WHERE id = ? AND is_team_event = 1 AND status = 'upcoming' AND registration_deadline >= CURDATE()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    setSessionMessage('error', 'Invalid event or registration is closed.');
    redirect('events.php');
}

$event = $result->fetch_assoc();

// Check if team exists and user is a member
$sql = "SELECT t.*, tm.is_captain
        FROM teams t
        JOIN team_members tm ON t.id = tm.team_id
        WHERE t.id = ? AND tm.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $teamId, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    setSessionMessage('error', 'You are not a member of this team.');
    redirect('events.php');
}

$team = $result->fetch_assoc();
$isCaptain = $team['is_captain'] == 1;

// Check if team is already registered for this event
$sql = "SELECT * FROM team_registrations WHERE team_id = ? AND event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $teamId, $eventId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    setSessionMessage('error', 'This team is already registered for this event.');
    redirect('events.php');
}

// Check if event has reached maximum participants
if ($event['max_participants']) {
    $sql = "SELECT COUNT(*) as count FROM team_registrations WHERE event_id = ? AND status != 'rejected'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] >= $event['max_participants']) {
        setSessionMessage('error', 'This event has reached its maximum number of participants.');
        redirect('events.php');
    }
}

// Register team for event
$sql = "INSERT INTO team_registrations (team_id, event_id, status) VALUES (?, ?, 'pending')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $teamId, $eventId);

if ($stmt->execute()) {
    setSessionMessage('success', 'Your team has been registered for the event successfully. Registration status: Pending');
} else {
    setSessionMessage('error', 'Failed to register for the event: ' . $conn->error);
}

redirect('player/my-events.php');
?>
