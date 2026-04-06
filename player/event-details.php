<?php
require_once '../includes/config.php';

if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    $sql = "SELECT * FROM events WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
        echo "<h1>" . htmlspecialchars($event['title']) . "</h1>";
        echo "<p>Date: " . htmlspecialchars($event['event_date']) . "</p>";
        echo "<p>Location: " . htmlspecialchars($event['location']) . "</p>";
        echo "<p>Description: " . htmlspecialchars($event['description']) . "</p>";

        // Get participants
        $participants = [];
        
        // Get individual participants
        $sql = "SELECT u.full_name FROM registrations r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.event_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $participants[] = $row['full_name'];
        }
        
        // Get team participants if it's a team event
        if ($event['is_team_event']) {
            $sql = "SELECT t.name FROM team_registrations tr 
                    JOIN teams t ON tr.team_id = t.id 
                    WHERE tr.event_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $event_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $participants[] = $row['name'];
            }
        }
        
        // Display participants
        if (!empty($participants)) {
            echo "<h2>Participants</h2><ul>";
            foreach ($participants as $participant) {
                echo "<li>" . htmlspecialchars($participant) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No participants found.</p>";
        }

        // Get winners
        $winners = [];
        $sql = "SELECT r.*, u.full_name FROM results r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.event_id = ? 
                ORDER BY r.position ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $winners[] = [
                'name' => $row['full_name'],
                'position' => $row['position'],
                'score' => $row['score']
            ];
        }

        // Display winners
        if (!empty($winners)) {
            echo "<h2>Winners</h2><ul>";
            foreach ($winners as $winner) {
                echo "<li>" . htmlspecialchars($winner['name']) . " - Position: " . htmlspecialchars($winner['position']) . " - Score: " . htmlspecialchars($winner['score']) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No winners found.</p>";
        }
    } else {
        echo "<p>Event not found.</p>";
    }
} else {
    echo "<p>No event ID provided.</p>";
}
?>