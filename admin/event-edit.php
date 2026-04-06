<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$error = '';
$success = '';

// Check if event ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('events.php');
}

$eventId = (int)$_GET['id'];

// Get event details
$event = getEventById($eventId);

if (!$event) {
    redirect('events.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $event_date = sanitize($_POST['event_date']);
    $registration_deadline = sanitize($_POST['registration_deadline']);
    $location = sanitize($_POST['location']);
    $max_participants = !empty($_POST['max_participants']) ? (int)$_POST['max_participants'] : null;
    $is_team_event = isset($_POST['is_team_event']) ? 1 : 0;
    $status = sanitize($_POST['status']);

    // Validate input
    if (empty($title) || empty($event_date) || empty($registration_deadline) || empty($location) || empty($status)) {
        $error = 'Please fill in all required fields';
    } elseif (strtotime($registration_deadline) > strtotime($event_date)) {
        $error = 'Registration deadline cannot be after event date';
    } else {
        // Check if is_team_event column exists
        $checkColumn = $conn->query("SHOW COLUMNS FROM events LIKE 'is_team_event'");

        if ($checkColumn->num_rows > 0) {
            // Column exists, include it in the query
            $sql = "UPDATE events SET
                    title = ?,
                    description = ?,
                    event_date = ?,
                    registration_deadline = ?,
                    location = ?,
                    max_participants = ?,
                    is_team_event = ?,
                    status = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                $error = 'Failed to prepare statement: ' . $conn->error;
            } else {
                $stmt->bind_param("ssssssisi", $title, $description, $event_date, $registration_deadline, $location, $max_participants, $is_team_event, $status, $eventId);
            }
        } else {
            // Column doesn't exist, use the original query without is_team_event
            $sql = "UPDATE events SET
                    title = ?,
                    description = ?,
                    event_date = ?,
                    registration_deadline = ?,
                    location = ?,
                    max_participants = ?,
                    status = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                $error = 'Failed to prepare statement: ' . $conn->error;
            } else {
                $stmt->bind_param("ssssssi", $title, $description, $event_date, $registration_deadline, $location, $max_participants, $status, $eventId);
            }
        }

        if ($stmt->execute()) {
            $success = 'Event updated successfully';
            // Refresh event data
            $event = getEventById($eventId);
        } else {
            $error = 'Failed to update event: ' . $conn->error;
        }
    }
}

// Get registrations count
$registrationsCount = getEventRegistrationsCount($eventId);

// Include header
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include '../includes/sidebar-admin.php'; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Event</h2>
            <div>
                <a href="registrations.php?event_id=<?php echo $eventId; ?>" class="btn btn-info me-2">
                    <i class="fas fa-clipboard-list me-2"></i>View Registrations
                </a>
                <a href="events.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Events
                </a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Event Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Current Registrations:</strong> <?php echo $registrationsCount; ?><?php echo $event['max_participants'] ? ' / ' . $event['max_participants'] : ''; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Created:</strong> <?php echo date('F j, Y', strtotime($event['created_at'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="post" action="" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="title" class="form-label">Event Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo $event['title']; ?>" required>
                        <div class="invalid-feedback">Please enter event title</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo $event['description']; ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="event_date" class="form-label">Event Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="event_date" name="event_date" value="<?php echo $event['event_date']; ?>" required>
                            <div class="invalid-feedback">Please select event date</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="registration_deadline" class="form-label">Registration Deadline <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="registration_deadline" name="registration_deadline" value="<?php echo $event['registration_deadline']; ?>" required>
                            <div class="invalid-feedback">Please select registration deadline</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo $event['location']; ?>" required>
                            <div class="invalid-feedback">Please enter event location</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="max_participants" class="form-label">Maximum Participants</label>
                            <input type="number" class="form-control" id="max_participants" name="max_participants" min="1" value="<?php echo $event['max_participants']; ?>">
                            <small class="text-muted">Leave blank for unlimited</small>
                        </div>
                    </div>

                    <?php
                    // Check if is_team_event column exists
                    $checkColumn = $conn->query("SHOW COLUMNS FROM events LIKE 'is_team_event'");
                    if ($checkColumn->num_rows > 0):
                    ?>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_team_event" name="is_team_event" <?php echo isset($event['is_team_event']) && $event['is_team_event'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_team_event">
                                This is a team event
                            </label>
                            <div class="form-text">Check this if participants will register as teams rather than individuals</div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="upcoming" <?php echo $event['status'] == 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="ongoing" <?php echo $event['status'] == 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                            <option value="completed" <?php echo $event['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $event['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <div class="invalid-feedback">Please select event status</div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">Update Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
