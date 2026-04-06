<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$error = '';
$success = '';

// Handle individual result deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $resultId = (int)$_GET['delete'];

    $sql = "DELETE FROM results WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $resultId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $success = "Result deleted successfully.";
    } else {
        $error = "Failed to delete result.";
    }
}

// Handle team result deletion
if (isset($_GET['delete_team']) && !empty($_GET['delete_team'])) {
    $resultId = (int)$_GET['delete_team'];

    // Check if team_results table exists
    $checkTable = $conn->query("SHOW TABLES LIKE 'team_results'");
    if ($checkTable->num_rows > 0) {
        $sql = "DELETE FROM team_results WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $resultId);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $success = "Team result deleted successfully.";
        } else {
            $error = "Failed to delete team result.";
        }
    } else {
        $error = "Team results table does not exist.";
    }
}

// Get event filter if provided
$eventFilter = isset($_GET['event_id']) ? (int)$_GET['event_id'] : null;

// Get all events for filter dropdown
$events = [];
$sql = "SELECT id, title FROM events ORDER BY event_date DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

// Check if team tables exist
$checkColumn = $conn->query("SHOW COLUMNS FROM events LIKE 'is_team_event'");
$hasTeamEventColumn = $checkColumn->num_rows > 0;

$checkTeamResults = $conn->query("SHOW TABLES LIKE 'team_results'");
$hasTeamResults = $checkTeamResults->num_rows > 0;

// Get individual results
$results = [];
$sql = "SELECT r.*, u.username, u.full_name, e.title as event_title, e.event_date, 'individual' as result_type
        FROM results r
        JOIN users u ON r.user_id = u.id
        JOIN events e ON r.event_id = e.id";

if ($eventFilter) {
    $sql .= " WHERE r.event_id = ? ORDER BY r.position ASC, r.score ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventFilter);
} else {
    $sql .= " ORDER BY e.event_date DESC, r.position ASC, r.score ASC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
}

// Get team results if available
if ($hasTeamResults && $hasTeamEventColumn) {
    $sql = "SELECT tr.*, t.name as team_name, e.title as event_title, e.event_date, 'team' as result_type
            FROM team_results tr
            JOIN teams t ON tr.team_id = t.id
            JOIN events e ON tr.event_id = e.id";

    if ($eventFilter) {
        $sql .= " WHERE tr.event_id = ? ORDER BY tr.position ASC, tr.score ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $eventFilter);
    } else {
        $sql .= " ORDER BY e.event_date DESC, tr.position ASC, tr.score ASC";
        $stmt = $conn->prepare($sql);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    }
}

// Sort all results by event date and position
usort($results, function($a, $b) {
    // First sort by event date (descending)
    $dateCompare = strtotime($b['event_date']) - strtotime($a['event_date']);
    if ($dateCompare != 0) {
        return $dateCompare;
    }

    // Then by event title
    $eventCompare = strcmp($a['event_title'], $b['event_title']);
    if ($eventCompare != 0) {
        return $eventCompare;
    }

    // Then by position (if available)
    if (isset($a['position']) && isset($b['position'])) {
        if ($a['position'] === null && $b['position'] !== null) return 1;
        if ($a['position'] !== null && $b['position'] === null) return -1;
        if ($a['position'] !== null && $b['position'] !== null) {
            return $a['position'] - $b['position'];
        }
    }

    // Finally by score
    return strcmp($a['score'], $b['score']);
});

// Include header
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include '../includes/sidebar-admin.php'; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Results Management</h2>
            <div class="d-flex">
                <form action="" method="get" class="d-flex me-2">
                    <select name="event_id" class="form-select me-2">
                        <option value="">All Events</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?php echo $event['id']; ?>" <?php echo $eventFilter == $event['id'] ? 'selected' : ''; ?>>
                                <?php echo $event['title']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
                <a href="result-add.php<?php echo $eventFilter ? '?event_id=' . $eventFilter : ''; ?>" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Add Result
                </a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <?php if (empty($results)): ?>
                    <p class="text-muted">No results found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Event</th>
                                    <th>Type</th>
                                    <th>Participant</th>
                                    <th>Position</th>
                                    <th>Score</th>
                                    <th>Remarks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $result): ?>
                                    <tr>
                                        <td><?php echo $result['id']; ?></td>
                                        <td>
                                            <a href="event-edit.php?id=<?php echo $result['event_id']; ?>">
                                                <?php echo $result['event_title']; ?>
                                            </a>
                                            <small class="d-block text-muted"><?php echo date('M d, Y', strtotime($result['event_date'])); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($result['result_type'] == 'team'): ?>
                                                <span class="badge bg-info"><i class="fas fa-users mr-1"></i> Team</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><i class="fas fa-user mr-1"></i> Individual</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($result['result_type'] == 'team'): ?>
                                                <span class="fw-bold"><?php echo $result['team_name']; ?></span>
                                            <?php else: ?>
                                                <a href="player-edit.php?id=<?php echo $result['user_id']; ?>">
                                                    <?php echo $result['full_name']; ?>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($result['position']): ?>
                                                <?php if ($result['result_type'] == 'team'): ?>
                                                    <span class="result-position team <?php echo $result['position'] <= 3 ? 'position-' . $result['position'] : ''; ?>">
                                                        <?php if ($result['position'] == 1): ?>
                                                            <i class="fas fa-trophy" style="color: gold;"></i>
                                                        <?php elseif ($result['position'] == 2): ?>
                                                            <i class="fas fa-award" style="color: silver;"></i>
                                                        <?php elseif ($result['position'] == 3): ?>
                                                            <i class="fas fa-award" style="color: #cd7f32;"></i>
                                                        <?php else: ?>
                                                            <?php echo $result['position']; ?>
                                                        <?php endif; ?>
                                                    </span>
                                                    <small class="d-block text-muted">
                                                        <?php
                                                        if ($result['position'] == 1) echo 'Champion';
                                                        elseif ($result['position'] == 2) echo 'Runner-up';
                                                        elseif ($result['position'] == 3) echo '3rd Place';
                                                        ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="result-position individual <?php echo $result['position'] <= 3 ? 'position-' . $result['position'] : ''; ?>">
                                                        <?php if ($result['position'] == 1): ?>
                                                            <i class="fas fa-medal" style="color: gold;"></i>
                                                        <?php elseif ($result['position'] == 2): ?>
                                                            <i class="fas fa-medal" style="color: silver;"></i>
                                                        <?php elseif ($result['position'] == 3): ?>
                                                            <i class="fas fa-medal" style="color: #cd7f32;"></i>
                                                        <?php else: ?>
                                                            <?php echo $result['position']; ?>
                                                        <?php endif; ?>
                                                    </span>
                                                    <small class="d-block text-muted">
                                                        <?php
                                                        if ($result['position'] == 1) echo 'Gold';
                                                        elseif ($result['position'] == 2) echo 'Silver';
                                                        elseif ($result['position'] == 3) echo 'Bronze';
                                                        ?>
                                                    </small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $result['score'] ? $result['score'] : '-'; ?></td>
                                        <td><?php echo $result['remarks'] ? substr($result['remarks'], 0, 50) . (strlen($result['remarks']) > 50 ? '...' : '') : '-'; ?></td>
                                        <td>
                                            <?php if ($result['result_type'] == 'team'): ?>
                                                <a href="result-add.php?edit=<?php echo $result['id']; ?>&team=1<?php echo $eventFilter ? '&event_id=' . $eventFilter : ''; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit Team Result">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="results.php?delete_team=<?php echo $result['id']; ?><?php echo $eventFilter ? '&event_id=' . $eventFilter : ''; ?>" class="btn btn-sm btn-danger delete-btn" data-bs-toggle="tooltip" title="Delete Team Result">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="result-add.php?edit=<?php echo $result['id']; ?><?php echo $eventFilter ? '&event_id=' . $eventFilter : ''; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="results.php?delete=<?php echo $result['id']; ?><?php echo $eventFilter ? '&event_id=' . $eventFilter : ''; ?>" class="btn btn-sm btn-danger delete-btn" data-bs-toggle="tooltip" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
