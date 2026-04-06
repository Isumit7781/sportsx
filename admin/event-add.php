<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$error = '';
$success = '';

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
            $sql = "INSERT INTO events (title, description, event_date, registration_deadline, location, max_participants, is_team_event, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                $error = 'Failed to prepare statement: ' . $conn->error;
            } else {
                $stmt->bind_param("sssssiis", $title, $description, $event_date, $registration_deadline, $location, $max_participants, $is_team_event, $status);
            }
        } else {
            // Column doesn't exist, use the original query without is_team_event
            $sql = "INSERT INTO events (title, description, event_date, registration_deadline, location, max_participants, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                $error = 'Failed to prepare statement: ' . $conn->error;
            } else {
                $stmt->bind_param("sssssss", $title, $description, $event_date, $registration_deadline, $location, $max_participants, $status);
            }
        }

        if ($stmt->execute()) {
            $success = 'Event added successfully';
        } else {
            $error = 'Failed to add event: ' . $conn->error;
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
            <h2>Add New Event</h2>
            <a href="events.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Events
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
                        <label for="title" class="form-label">Event Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required>
                        <div class="invalid-feedback">Please enter event title</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="event_date" class="form-label">Event Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="event_date" name="event_date" required>
                            <div class="invalid-feedback">Please select event date</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="registration_deadline" class="form-label">Registration Deadline <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="registration_deadline" name="registration_deadline" required>
                            <div class="invalid-feedback">Please select registration deadline</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="location" name="location" required>
                            <div class="invalid-feedback">Please enter event location</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="max_participants" class="form-label">Maximum Participants</label>
                            <input type="number" class="form-control" id="max_participants" name="max_participants" min="1">
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
                            <input class="form-check-input" type="checkbox" id="is_team_event" name="is_team_event">
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
                            <option value="upcoming" selected>Upcoming</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <div class="invalid-feedback">Please select event status</div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                        <button type="submit" class="btn btn-primary">Add Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
