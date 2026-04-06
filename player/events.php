<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is player
if (!isLoggedIn() || !isPlayer()) {
    redirect('../index.php');
}

// Check if is_team_event column exists
$checkColumn = $conn->query("SHOW COLUMNS FROM events LIKE 'is_team_event'");
$hasTeamEventColumn = $checkColumn->num_rows > 0;

// Get available events for registration
$events = [];

if ($hasTeamEventColumn) {
    // Use query with team event support
    $sql = "SELECT e.* FROM events e
            WHERE e.event_date >= CURDATE()
            AND e.registration_deadline >= CURDATE()
            AND e.status = 'upcoming'
            AND (
                (e.is_team_event = 0 AND e.id NOT IN (SELECT event_id FROM registrations WHERE user_id = ?))
                OR
                (e.is_team_event = 1 AND e.id NOT IN (
                    SELECT tr.event_id FROM team_registrations tr
                    JOIN team_members tm ON tr.team_id = tm.team_id
                    WHERE tm.user_id = ?
                ))
            )
            ORDER BY e.event_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
} else {
    // Use original query without team event support
    $sql = "SELECT e.* FROM events e
            WHERE e.event_date >= CURDATE()
            AND e.registration_deadline >= CURDATE()
            AND e.status = 'upcoming'
            AND e.id NOT IN (SELECT event_id FROM registrations WHERE user_id = ?)
            ORDER BY e.event_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Add is_team_event field if it doesn't exist
        if (!$hasTeamEventColumn) {
            $row['is_team_event'] = 0;
        }
        $events[] = $row;
    }
}

// Get user's teams for team events
$userTeams = [];

// Only get teams if the teams table exists and team events are supported
if ($hasTeamEventColumn) {
    // Check if teams table exists
    $checkTable = $conn->query("SHOW TABLES LIKE 'teams'");

    if ($checkTable->num_rows > 0) {
        $sql = "SELECT t.* FROM teams t
                JOIN team_members tm ON t.id = tm.team_id
                WHERE tm.user_id = ?
                ORDER BY t.name ASC";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $userTeams[] = $row;
                }
            }
        }
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
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg mb-6 p-6 text-white animate-fadeIn">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Available Events</h2>
                    <p class="text-blue-100">Browse and register for upcoming sports events</p>
                </div>
                <div class="hidden md:block">
                    <i class="fas fa-calendar-alt text-6xl text-blue-200 opacity-50"></i>
                </div>
            </div>
        </div>

        <?php echo displaySessionMessage(); ?>

        <!-- Search Bar -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 mb-6 animate-fadeIn">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-3 text-white">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-search mr-2"></i> Find Events
                </h3>
            </div>
            <div class="card-body p-4">
                <div class="input-group">
                    <input type="text" class="form-control rounded-start" id="eventSearch" placeholder="Search events by title or location...">
                    <button class="btn btn-primary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>

        <?php if (empty($events)): ?>
            <div class="alert alert-info bg-blue-100 text-blue-800 rounded-xl border-blue-200 p-4">
                <i class="fas fa-info-circle me-2"></i>No available events for registration at the moment.
            </div>
        <?php else: ?>
            <div class="row" data-aos="fade-up">
                <?php foreach ($events as $event): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm rounded-xl overflow-hidden transform transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                            <div class="card-header bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 px-4">
                                <h5 class="card-title mb-0 font-semibold"><?php echo $event['title']; ?></h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                                        <i class="fas fa-calendar-day me-1"></i><?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                    </div>
                                    <?php if ($event['is_team_event']): ?>
                                        <span class="badge bg-blue-500 text-white px-2 py-1 rounded-full"><i class="fas fa-users me-1"></i>Team Event</span>
                                    <?php else: ?>
                                        <span class="badge bg-green-500 text-white px-2 py-1 rounded-full"><i class="fas fa-user me-1"></i>Individual</span>
                                    <?php endif; ?>
                                </div>
                                <p class="mb-3 flex items-center text-gray-600">
                                    <i class="fas fa-map-marker-alt me-2 text-blue-500"></i><?php echo $event['location']; ?>
                                </p>
                                <p class="card-text mb-4 text-gray-700"><?php echo substr($event['description'], 0, 100) . (strlen($event['description']) > 100 ? '...' : ''); ?></p>

                                <div class="d-flex justify-content-between align-items-center mb-3 text-sm">
                                    <span class="text-gray-600">
                                        <i class="fas fa-hourglass-half me-1 text-blue-500"></i>Deadline: <?php echo date('M d, Y', strtotime($event['registration_deadline'])); ?>
                                    </span>

                                    <?php
                                    // Check if event has maximum participants
                                    if ($event['max_participants']) {
                                        $registrationsCount = getEventRegistrationsCount($event['id']);
                                        $spotsLeft = $event['max_participants'] - $registrationsCount;

                                        if ($spotsLeft > 0) {
                                            echo '<span class="text-green-600 font-medium"><i class="fas fa-users me-1"></i>' . $spotsLeft . ' spots left</span>';
                                        } else {
                                            echo '<span class="text-red-600 font-medium"><i class="fas fa-users-slash me-1"></i>No spots left</span>';
                                        }
                                    }
                                    ?>
                                </div>

                                <div class="d-flex gap-2 mt-3">
                                    <?php if ($event['is_team_event']): ?>
                                        <?php if (empty($userTeams)): ?>
                                            <a href="teams.php" class="btn btn-info bg-blue-500 hover:bg-blue-600 text-white border-0 rounded-lg py-2 transition-all duration-300 flex-grow">
                                                <i class="fas fa-users me-2"></i>Create a Team First
                                            </a>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-primary text-white border-0 rounded-lg py-2 transition-all duration-300 flex-grow" data-bs-toggle="modal" data-bs-target="#teamRegistrationModal<?php echo $event['id']; ?>">
                                                <i class="fas fa-users me-2"></i>Register with Team
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="register-event.php?id=<?php echo $event['id']; ?>" class="btn btn-primary text-white border-0 rounded-lg py-2 transition-all duration-300 flex-grow">
                                            <i class="fas fa-user-plus me-2"></i>Register Now
                                        </a>
                                        <!-- http://localhost/sports/player/event-details.php?event_id=8 -->
                                    <?php endif; ?>
                                    <a href="event-details.php?event_id=<?php echo $event['id']; ?>" class="btn btn-primary text-white border-0 rounded-lg py-2 transition-all duration-300 flex-grow">
                                            <i class="fas fa-user-plus me-2"></i>view summary
                                        </a>
                                    <!-- <button type="button" class="btn btn-info text-white border-0 rounded-lg py-2 px-4 transition-all duration-300" onclick="toggleEventSummary(<?php echo $event['id']; ?>)">
                                        <i class="fas fa-info-circle"></i>
                                    </button> -->
                                </div>
                                <div id="eventSummary<?php echo $event['id']; ?>" class="mt-3 bg-gray-50 rounded-lg p-3 hidden">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <p><strong>Total Participants:</strong> <?php echo getEventRegistrationsCount($event['id']); ?></p>
                                            <p><strong>Maximum Participants:</strong> <?php echo $event['max_participants'] ?: 'Unlimited'; ?></p>
                                        </div>
                                        <div>
                                            <p><strong>Type:</strong> <?php echo $event['is_team_event'] ? 'Team Event' : 'Individual Event'; ?></p>
                                            <p><strong>Status:</strong> <?php echo ucfirst($event['status']); ?></p>
                                        </div>
                                    </div>
                                    <?php
                                    $participants = getEventParticipants($event['id']);
                                    if (!empty($participants)): ?>
                                    <div class="mt-3">
                                        <p><strong>Participants:</strong></p>
                                        <ul class="list-disc pl-4">
                                            <?php foreach ($participants as $participant): ?>
                                                <li><?php echo htmlspecialchars($participant['name']); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                    <?php
                                    $winners = getEventWinners($event['id']);
                                    if (!empty($winners)): ?>
                                    <div class="mt-3">
                                        <p><strong>Winners:</strong></p>
                                        <ul class="list-disc pl-4">
                                            <?php foreach ($winners as $index => $winner): ?>
                                                <li>
                                                    <span class="font-semibold"><?php echo ($index + 1) . getOrdinalSuffix($index + 1); ?> Place:</span>
                                                    <?php echo htmlspecialchars($winner['name']); ?>
                                                    <?php echo isset($winner['score']) ? '(Score: ' . $winner['score'] . ')' : ''; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
