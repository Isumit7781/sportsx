<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$error = '';
$success = '';
$isEdit = false;
$resultData = null;

// Check if it's an edit operation
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $resultId = (int)$_GET['edit'];
    $isTeamResult = isset($_GET['team']) && $_GET['team'] == 1;
    $isEdit = true;

    if ($isTeamResult) {
        // Check if team_results table exists
        $checkTable = $conn->query("SHOW TABLES LIKE 'team_results'");
        if ($checkTable->num_rows > 0) {
            // Get team result details
            $sql = "SELECT * FROM team_results WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $resultId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $resultData = $result->fetch_assoc();
            } else {
                redirect('results.php');
            }
        } else {
            redirect('results.php');
        }
    } else {
        // Get individual result details
        $sql = "SELECT * FROM results WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $resultId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $resultData = $result->fetch_assoc();
        } else {
            redirect('results.php');
        }
    }
}

// Get event filter if provided
$eventFilter = isset($_GET['event_id']) ? (int)$_GET['event_id'] : ($isEdit ? $resultData['event_id'] : null);

// Check if is_team_event column exists
$checkColumn = $conn->query("SHOW COLUMNS FROM events LIKE 'is_team_event'");
$hasTeamEventColumn = $checkColumn->num_rows > 0;

// Check if team tables exist
$hasTeamTables = false;
if ($hasTeamEventColumn) {
    $checkTeamsTable = $conn->query("SHOW TABLES LIKE 'teams'");
    $hasTeamTables = $checkTeamsTable->num_rows > 0;
}

// Get all events with is_team_event flag if available
$events = [];
if ($hasTeamEventColumn) {
    $sql = "SELECT id, title, event_date, is_team_event FROM events ORDER BY event_date DESC";
} else {
    $sql = "SELECT id, title, event_date, 0 as is_team_event FROM events ORDER BY event_date DESC";
}

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

// Get all teams if team tables exist
$teams = [];
if ($hasTeamTables) {
    $sql = "SELECT id, name FROM teams ORDER BY name ASC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $teams[] = $row;
        }
    }
}

// Get all players
$players = [];
$sql = "SELECT id, username, full_name FROM users WHERE role = 'player' ORDER BY full_name";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $players[] = $row;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = (int)$_POST['event_id'];
    $position = !empty($_POST['position']) ? (int)$_POST['position'] : null;
    $score = sanitize($_POST['score']);
    $remarks = sanitize($_POST['remarks']);

    // Check if the event is a team event
    $isTeamEvent = false;
    foreach ($events as $event) {
        if ($event['id'] == $event_id) {
            $isTeamEvent = $event['is_team_event'] == 1;
            break;
        }
    }

    if ($isTeamEvent && $hasTeamTables) {
        // Team event result
        $team_id = isset($_POST['team_id']) ? (int)$_POST['team_id'] : 0;

        // Validate input
        if (empty($event_id) || empty($team_id)) {
            $error = 'Please select both event and team';
        } else {
            // Check if this team already has a result for this event (except for the current edit)
            $sql = "SELECT id FROM team_results WHERE event_id = ? AND team_id = ?";
            $params = [$event_id, $team_id];
            $types = "ii";

            if ($isEdit) {
                $sql .= " AND id != ?";
                $params[] = $resultId;
                $types .= "i";
            }

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $checkResult = $stmt->get_result();

            if ($checkResult->num_rows > 0) {
                $error = 'This team already has a result for this event';
            } else {
                if ($isEdit) {
                    // Update existing team result
                    $sql = "UPDATE team_results SET event_id = ?, team_id = ?, position = ?, score = ?, remarks = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iiissi", $event_id, $team_id, $position, $score, $remarks, $resultId);
                } else {
                    // Insert new team result
                    $sql = "INSERT INTO team_results (event_id, team_id, position, score, remarks) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iiiss", $event_id, $team_id, $position, $score, $remarks);
                }

                if ($stmt->execute()) {
                    $success = $isEdit ? 'Team result updated successfully' : 'Team result added successfully';
                    if (!$isEdit) {
                        // Clear form after successful add
                        $team_id = null;
                        $position = null;
                        $score = '';
                        $remarks = '';
                    } else {
                        // Refresh result data
                        $sql = "SELECT * FROM team_results WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $resultId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $resultData = $result->fetch_assoc();
                    }
                } else {
                    $error = ($isEdit ? 'Failed to update team result: ' : 'Failed to add team result: ') . $conn->error;
                }
            }
        }
    } else {
        // Individual event result
        $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

        // Validate input
        if (empty($event_id) || empty($user_id)) {
            $error = 'Please select both event and player';
        } else {
            // Check if this player already has a result for this event (except for the current edit)
            $sql = "SELECT id FROM results WHERE event_id = ? AND user_id = ?";
            $params = [$event_id, $user_id];
            $types = "ii";

            if ($isEdit) {
                $sql .= " AND id != ?";
                $params[] = $resultId;
                $types .= "i";
            }

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $checkResult = $stmt->get_result();

            if ($checkResult->num_rows > 0) {
                $error = 'This player already has a result for this event';
            } else {
                if ($isEdit) {
                    // Update existing result
                    $sql = "UPDATE results SET event_id = ?, user_id = ?, position = ?, score = ?, remarks = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iiissi", $event_id, $user_id, $position, $score, $remarks, $resultId);
                } else {
                    // Insert new result
                    $sql = "INSERT INTO results (event_id, user_id, position, score, remarks) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iiiss", $event_id, $user_id, $position, $score, $remarks);
                }

                if ($stmt->execute()) {
                    $success = $isEdit ? 'Result updated successfully' : 'Result added successfully';
                    if (!$isEdit) {
                        // Clear form after successful add
                        $user_id = null;
                        $position = null;
                        $score = '';
                        $remarks = '';
                    } else {
                        // Refresh result data
                        $sql = "SELECT * FROM results WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $resultId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $resultData = $result->fetch_assoc();
                    }
                } else {
                    $error = ($isEdit ? 'Failed to update result: ' : 'Failed to add result: ') . $conn->error;
                }
            }
        }
    }
}

// Get selected event details if event filter is provided
$selectedEvent = null;
if ($eventFilter) {
    foreach ($events as $event) {
        if ($event['id'] == $eventFilter) {
            $selectedEvent = $event;
            break;
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
            <h2>
                <?php if ($isEdit): ?>
                    <?php echo isset($_GET['team']) && $_GET['team'] == 1 ? 'Edit Team Result' : 'Edit Individual Result'; ?>
                <?php else: ?>
                    Add New Result
                <?php endif; ?>
            </h2>
            <a href="results.php<?php echo $eventFilter ? '?event_id=' . $eventFilter : ''; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Results
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
                    <div class="mb-3">
                        <label for="event_id" class="form-label">Event <span class="text-danger">*</span></label>
                        <select class="form-select" id="event_id" name="event_id" required onchange="toggleParticipantType()">
                            <option value="">Select Event</option>
                            <?php foreach ($events as $event): ?>
                                <option value="<?php echo $event['id']; ?>"
                                    data-is-team="<?php echo $event['is_team_event']; ?>"
                                    <?php echo ($eventFilter == $event['id'] || ($isEdit && $resultData['event_id'] == $event['id'])) ? 'selected' : ''; ?>>
                                    <?php echo $event['title']; ?>
                                    (<?php echo date('M d, Y', strtotime($event['event_date'])); ?>)
                                    <?php if ($event['is_team_event']): ?>
                                        <span class="text-info">[Team Event]</span>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select an event</div>
                    </div>

                    <div id="player-selection" class="mb-3">
                        <label for="user_id" class="form-label">Player <span class="text-danger">*</span></label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="">Select Player</option>
                            <?php foreach ($players as $player): ?>
                                <option value="<?php echo $player['id']; ?>" <?php echo ($isEdit && isset($resultData['user_id']) && $resultData['user_id'] == $player['id']) ? 'selected' : ''; ?>>
                                    <?php echo $player['full_name']; ?> (<?php echo $player['username']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select a player</div>
                    </div>

                    <div id="team-selection" class="mb-3" style="display: none;">
                        <label for="team_id" class="form-label">Team <span class="text-danger">*</span></label>
                        <select class="form-select" id="team_id" name="team_id">
                            <option value="">Select Team</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo $team['id']; ?>" <?php echo ($isEdit && isset($resultData['team_id']) && $resultData['team_id'] == $team['id']) ? 'selected' : ''; ?>>
                                    <?php echo $team['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select a team</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="position" class="form-label">Position</label>
                            <input type="number" class="form-control" id="position" name="position" min="1" value="<?php echo $isEdit ? $resultData['position'] : ''; ?>">
                            <small class="text-muted">Leave blank if not applicable</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="score" class="form-label">Score</label>
                            <input type="text" class="form-control" id="score" name="score" value="<?php echo $isEdit ? $resultData['score'] : ''; ?>">
                            <small class="text-muted">Can be time, points, or any other metric</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3"><?php echo $isEdit ? $resultData['remarks'] : ''; ?></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <?php if (!$isEdit): ?>
                            <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary"><?php echo $isEdit ? 'Update Result' : 'Add Result'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Function to toggle between player and team selection based on event type
    function toggleParticipantType() {
        const eventSelect = document.getElementById('event_id');
        const selectedOption = eventSelect.options[eventSelect.selectedIndex];
        const isTeamEvent = selectedOption.getAttribute('data-is-team') === '1';

        const playerSelection = document.getElementById('player-selection');
        const teamSelection = document.getElementById('team-selection');
        const userIdSelect = document.getElementById('user_id');
        const teamIdSelect = document.getElementById('team_id');

        if (isTeamEvent) {
            playerSelection.style.display = 'none';
            teamSelection.style.display = 'block';
            userIdSelect.removeAttribute('required');
            teamIdSelect.setAttribute('required', 'required');
        } else {
            playerSelection.style.display = 'block';
            teamSelection.style.display = 'none';
            userIdSelect.setAttribute('required', 'required');
            teamIdSelect.removeAttribute('required');
        }
    }

    // Run the function on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleParticipantType();
    });
</script>

<?php include '../includes/footer.php'; ?>
