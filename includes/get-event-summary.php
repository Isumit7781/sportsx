<?php
require_once 'config.php';
require_once 'functions.php';

// Check if event ID is provided
if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Event ID not provided']);
    exit;
}

$eventId = intval($_GET['id']);

// Get event details
$sql = "SELECT e.*, 
        (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) +
        (SELECT COUNT(*) FROM team_registrations tr WHERE tr.event_id = e.id) as total_participants
        FROM events e WHERE e.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $eventId);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    echo json_encode(['error' => 'Event not found']);
    exit;
}

// Get participants
$participants = [];

// Get individual participants
$sql = "SELECT u.name FROM registrations r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $eventId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $participants[] = $row;
}

// Get team participants if it's a team event
if ($event['is_team_event']) {
    $sql = "SELECT t.name FROM team_registrations tr 
            JOIN teams t ON tr.team_id = t.id 
            WHERE tr.event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $participants[] = $row;
    }
}

// Get winners
$winners = [];
$sql = "SELECT r.*, u.name, t.name as team_name 
        FROM results r 
        LEFT JOIN users u ON r.user_id = u.id 
        LEFT JOIN teams t ON r.team_id = t.id 
        WHERE r.event_id = ? 
        ORDER BY r.position ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $eventId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $winners[] = [
        'name' => $row['team_id'] ? $row['team_name'] : $row['name'],
        'position' => $row['position'],
        'score' => $row['score']
    ];
}

// Format dates
$event['event_date'] = date('F d, Y', strtotime($event['event_date']));
$event['registration_deadline'] = date('F d, Y', strtotime($event['registration_deadline']));

// Prepare response
$response = [
    'title' => $event['title'],
    'description' => $event['description'],
    'event_date' => $event['event_date'],
    'location' => $event['location'],
    'status' => $event['status'],
    'registration_deadline' => $event['registration_deadline'],
    'is_team_event' => (bool)$event['is_team_event'],
    'max_participants' => $event['max_participants'],
    'total_participants' => $event['total_participants'],
    'participants' => $participants,
    'winners' => $winners
];

// Send response
header('Content-Type: application/json');
echo json_encode($response);