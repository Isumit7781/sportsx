<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is player
if (!isLoggedIn() || !isPlayer()) {
    redirect('../index.php');
}

$error = '';
$success = '';

// Handle individual registration cancellation
if (isset($_GET['cancel']) && !empty($_GET['cancel'])) {
    $registrationId = (int)$_GET['cancel'];

    // Check if registration belongs to the current user
    $sql = "SELECT r.*, e.title, e.event_date FROM registrations r
            JOIN events e ON r.event_id = e.id
            WHERE r.id = ? AND r.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $registrationId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $registration = $result->fetch_assoc();

        // Check if event date has not passed
        if ($registration['event_date'] > date('Y-m-d')) {
            // Delete registration
            $sql = "DELETE FROM registrations WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $registrationId);

            if ($stmt->execute()) {
                $success = "Your registration for '" . $registration['title'] . "' has been cancelled.";
            } else {
                $error = "Failed to cancel registration.";
            }
        } else {
            $error = "Cannot cancel registration for past events.";
        }
    } else {
        $error = "Invalid registration.";
    }
}

// Handle team registration cancellation
if (isset($_GET['cancel_team']) && !empty($_GET['cancel_team'])) {
    $teamRegistrationId = (int)$_GET['cancel_team'];

    // Check if team registration belongs to a team where the user is a captain
    $sql = "SELECT tr.*, e.title, e.event_date, t.name as team_name
            FROM team_registrations tr
            JOIN events e ON tr.event_id = e.id
            JOIN teams t ON tr.team_id = t.id
            JOIN team_members tm ON t.id = tm.team_id
            WHERE tr.id = ? AND tm.user_id = ? AND tm.is_captain = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $teamRegistrationId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $teamRegistration = $result->fetch_assoc();

        // Check if event date has not passed
        if ($teamRegistration['event_date'] > date('Y-m-d')) {
            // Delete team registration
            $sql = "DELETE FROM team_registrations WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $teamRegistrationId);

            if ($stmt->execute()) {
                $success = "Team '" . $teamRegistration['team_name'] . "' registration for '" . $teamRegistration['title'] . "' has been cancelled.";
            } else {
                $error = "Failed to cancel team registration.";
            }
        } else {
            $error = "Cannot cancel registration for past events.";
        }
    } else {
        $error = "Invalid team registration or you are not the team captain.";
    }
}

// Check if team-related tables exist
$checkTeamRegistrations = $conn->query("SHOW TABLES LIKE 'team_registrations'");
$hasTeamRegistrations = $checkTeamRegistrations->num_rows > 0;

// Get player's individual registrations
$registrations = [];
$sql = "SELECT r.*, e.title, e.description, e.event_date, e.location, e.status, 0 as is_team_event, NULL as team_id, NULL as team_name
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        WHERE r.user_id = ?
        ORDER BY e.event_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $registrations[] = $row;
    }
}

// Get player's team registrations if the tables exist
if ($hasTeamRegistrations) {
    $sql = "SELECT tr.*, e.title, e.description, e.event_date, e.location, e.status, 1 as is_team_event, t.id as team_id, t.name as team_name, tm.is_captain
            FROM team_registrations tr
            JOIN events e ON tr.event_id = e.id
            JOIN teams t ON tr.team_id = t.id
            JOIN team_members tm ON t.id = tm.team_id
            WHERE tm.user_id = ?
            ORDER BY e.event_date DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $registrations[] = $row;
            }
        }
    }
}

// Sort registrations by event date
usort($registrations, function($a, $b) {
    return strtotime($b['event_date']) - strtotime($a['event_date']);
});

// Include header
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include '../includes/sidebar-player.php'; ?>
    </div>
    <div class="col-md-9">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg mb-6 p-6 text-white animate-fadeIn">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold mb-2">My Registrations</h2>
                    <p class="text-blue-100">View and manage your event registrations</p>
                </div>
                <div class="hidden md:block">
                    <i class="fas fa-clipboard-check text-6xl text-blue-200 opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mb-4">
            <a href="events.php" class="btn btn-primary text-white border-0 rounded-lg py-2 px-4 transition-all duration-300 shadow-sm hover:shadow-md">
                <i class="fas fa-plus me-2"></i>Register for More Events
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger bg-red-100 text-red-800 rounded-xl border-red-200 p-4 mb-4">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success bg-green-100 text-green-800 rounded-xl border-green-200 p-4 mb-4">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php echo displaySessionMessage(); ?>

        <?php if (empty($registrations)): ?>
            <div class="alert alert-info bg-blue-100 text-blue-800 rounded-xl border-blue-200 p-4">
                <i class="fas fa-info-circle me-2"></i>You have not registered for any events yet.
            </div>
        <?php else: ?>
            <!-- Upcoming Events -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 mb-6 animate-fadeIn">
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-4 py-3 text-white">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-calendar-alt me-2"></i>Upcoming Events
                    </h3>
                </div>
                <div class="card-body p-4">
                    <?php
                    $upcomingFound = false;
                    foreach ($registrations as $registration) {
                        if ($registration['event_date'] >= date('Y-m-d')) {
                            $upcomingFound = true;
                            ?>
                            <div class="card mb-3 border-0 shadow-sm rounded-lg overflow-hidden transform transition-all duration-300 hover:shadow-md">
                                <div class="card-body p-0">
                                    <div class="row g-0">
                                        <div class="col-md-8 p-4">
                                            <h5 class="card-title font-semibold text-gray-800 mb-2"><?php echo $registration['title']; ?></h5>
                                            <div class="mb-3">
                                                <?php if ($registration['is_team_event']): ?>
                                                    <span class="badge bg-blue-500 text-white px-2 py-1 rounded-full"><i class="fas fa-users me-1"></i>Team Event</span>
                                                    <span class="ms-2 text-gray-700"><i class="fas fa-users me-1 text-orange-500"></i><?php echo $registration['team_name']; ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-purple-500 text-white px-2 py-1 rounded-full"><i class="fas fa-user me-1"></i>Individual Event</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mb-3">
                                                <p class="mb-1 text-gray-600"><i class="fas fa-calendar-day me-2 text-orange-500"></i><?php echo date('F j, Y', strtotime($registration['event_date'])); ?></p>
                                                <p class="mb-0 text-gray-600"><i class="fas fa-map-marker-alt me-2 text-orange-500"></i><?php echo $registration['location']; ?></p>
                                            </div>
                                            <p class="card-text text-gray-700"><?php echo substr($registration['description'], 0, 100) . (strlen($registration['description']) > 100 ? '...' : ''); ?></p>
                                        </div>
                                        <div class="col-md-4 bg-gray-50 p-4 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="mb-3">
                                                    <span class="badge <?php
                                                        if ($registration['status'] == 'pending') echo 'bg-yellow-500';
                                                        elseif ($registration['status'] == 'approved') echo 'bg-green-500';
                                                        else echo 'bg-red-500';
                                                    ?> text-white px-3 py-1 rounded-full">
                                                        <?php echo ucfirst($registration['status']); ?>
                                                    </span>
                                                </div>
                                                <p class="text-gray-500 text-sm"><i class="fas fa-clock me-1"></i> Registered on <?php echo date('M d, Y', strtotime($registration['registration_date'])); ?></p>
                                            </div>
                                            <div class="mt-3">
                                                <?php if ($registration['is_team_event']): ?>
                                                    <?php if (isset($registration['is_captain']) && $registration['is_captain']): ?>
                                                        <a href="my-events.php?cancel_team=<?php echo $registration['id']; ?>" class="btn btn-sm btn-danger bg-red-500 hover:bg-red-600 text-white border-0 rounded-lg py-2 px-3 transition-all duration-300 w-100">
                                                            <i class="fas fa-times me-1"></i>Cancel Team Registration
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-secondary bg-gray-400 text-white border-0 rounded-lg py-2 px-3 w-100" disabled>
                                                            <i class="fas fa-info-circle me-1"></i>Only Team Captain Can Cancel
                                                        </button>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <a href="my-events.php?cancel=<?php echo $registration['id']; ?>" class="btn btn-sm btn-danger bg-red-500 hover:bg-red-600 text-white border-0 rounded-lg py-2 px-3 transition-all duration-300 w-100">
                                                        <i class="fas fa-times me-1"></i>Cancel Registration
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }

                    if (!$upcomingFound) {
                        echo '<p class="text-muted">You have no upcoming events.</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- Past Events -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 mb-6 animate-fadeIn">
                <div class="bg-gradient-to-r from-gray-500 to-gray-600 px-4 py-3 text-white">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-history me-2"></i>Past Events
                    </h3>
                </div>
                <div class="card-body p-4">
                    <?php
                    $pastFound = false;
                    foreach ($registrations as $registration) {
                        if ($registration['event_date'] < date('Y-m-d')) {
                            $pastFound = true;
                            ?>
                            <div class="card mb-3 border-0 shadow-sm rounded-lg overflow-hidden transform transition-all duration-300 hover:shadow-md opacity-75">
                                <div class="card-body p-0">
                                    <div class="row g-0">
                                        <div class="col-md-8 p-4">
                                            <h5 class="card-title font-semibold text-gray-700 mb-2"><?php echo $registration['title']; ?></h5>
                                            <div class="mb-3">
                                                <?php if ($registration['is_team_event']): ?>
                                                    <span class="badge bg-blue-400 text-white px-2 py-1 rounded-full"><i class="fas fa-users me-1"></i>Team Event</span>
                                                    <span class="ms-2 text-gray-600"><i class="fas fa-users me-1 text-gray-500"></i><?php echo $registration['team_name']; ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-purple-400 text-white px-2 py-1 rounded-full"><i class="fas fa-user me-1"></i>Individual Event</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mb-3">
                                                <p class="mb-1 text-gray-600"><i class="fas fa-calendar-day me-2 text-gray-500"></i><?php echo date('F j, Y', strtotime($registration['event_date'])); ?></p>
                                                <p class="mb-0 text-gray-600"><i class="fas fa-map-marker-alt me-2 text-gray-500"></i><?php echo $registration['location']; ?></p>
                                            </div>
                                            <p class="card-text text-gray-600"><?php echo substr($registration['description'], 0, 100) . (strlen($registration['description']) > 100 ? '...' : ''); ?></p>
                                        </div>
                                        <div class="col-md-4 bg-gray-50 p-4 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="mb-3">
                                                    <span class="badge <?php
                                                        if ($registration['status'] == 'pending') echo 'bg-yellow-400';
                                                        elseif ($registration['status'] == 'approved') echo 'bg-green-400';
                                                        else echo 'bg-red-400';
                                                    ?> text-white px-3 py-1 rounded-full">
                                                        <?php echo ucfirst($registration['status']); ?>
                                                    </span>
                                                </div>
                                                <p class="text-gray-500 text-sm"><i class="fas fa-clock me-1"></i> Registered on <?php echo date('M d, Y', strtotime($registration['registration_date'])); ?></p>
                                            </div>
                                            <div class="mt-3">
                                                <a href="results.php?event_id=<?php echo $registration['event_id']; ?>" class="btn btn-sm btn-info bg-blue-500 hover:bg-blue-600 text-white border-0 rounded-lg py-2 px-3 transition-all duration-300 w-100">
                                                    <i class="fas fa-trophy me-1"></i>View Results
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }

                    if (!$pastFound) {
                        echo '<p class="text-muted">You have no past events.</p>';
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
