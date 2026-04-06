<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is player
if (!isLoggedIn() || !isPlayer()) {
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

// Check if event is available for registration
if ($event['event_date'] < date('Y-m-d') || $event['registration_deadline'] < date('Y-m-d') || $event['status'] != 'upcoming') {
    $error = 'This event is not available for registration.';
}

// Check if player is already registered
if (isPlayerRegistered($_SESSION['user_id'], $eventId)) {
    $error = 'You are already registered for this event.';
}

// Check if event has reached maximum participants
if ($event['max_participants']) {
    $registrationsCount = getEventRegistrationsCount($eventId);
    if ($registrationsCount >= $event['max_participants']) {
        $error = 'This event has reached its maximum number of participants.';
    }
}

// Process registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$error) {
    // Register player for the event
    $sql = "INSERT INTO registrations (user_id, event_id, status) VALUES (?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $_SESSION['user_id'], $eventId);

    if ($stmt->execute()) {
        setSessionMessage('success', 'You have successfully registered for this event. Your registration is pending approval.');
        redirect('player/my-events.php');
    } else {
        $error = 'Failed to register for the event: ' . $conn->error;
    }
}

// Include header
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include '../includes/sidebar-player.php'; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Event Registration</h2>
            <a href="events.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Events
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
            <div class="text-center mt-4">
                <a href="events.php" class="btn btn-primary">Browse Other Events</a>
            </div>
        <?php elseif ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            </div>
            <div class="text-center mt-4">
                <a href="my-events.php" class="btn btn-primary">View My Registrations</a>
            </div>
        <?php else: ?>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-alt me-2"></i>Event Details
                </div>
                <div class="card-body">
                    <h3 class="card-title"><?php echo $event['title']; ?></h3>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-calendar-day me-2"></i>Event Date:</strong> <?php echo date('F j, Y', strtotime($event['event_date'])); ?></p>
                            <p><strong><i class="fas fa-map-marker-alt me-2"></i>Location:</strong> <?php echo $event['location']; ?></p>
                            <p><strong><i class="fas fa-hourglass-end me-2"></i>Registration Deadline:</strong> <?php echo date('F j, Y', strtotime($event['registration_deadline'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-users me-2"></i>Participants:</strong>
                                <?php
                                $registrationsCount = getEventRegistrationsCount($eventId);
                                echo $registrationsCount;
                                if ($event['max_participants']) {
                                    echo ' / ' . $event['max_participants'];
                                    $spotsLeft = $event['max_participants'] - $registrationsCount;
                                    echo ' (' . $spotsLeft . ' spots left)';
                                }
                                ?>
                            </p>
                            <p><strong><i class="fas fa-info-circle me-2"></i>Status:</strong>
                                <span class="badge bg-info">Upcoming</span>
                            </p>
                        </div>
                    </div>

                    <div class="mt-3">
                        <h5>Description</h5>
                        <p><?php echo nl2br($event['description']); ?></p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clipboard-check me-2"></i>Confirm Registration
                </div>
                <div class="card-body">
                    <p>Please confirm that you want to register for this event. Once registered, you will be able to view this event in your registrations.</p>

                    <form method="post" action="">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirmRegistration" required>
                            <label class="form-check-label" for="confirmRegistration">
                                I confirm that I want to register for this event and that I have read and understood the event details.
                            </label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Register for Event</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
