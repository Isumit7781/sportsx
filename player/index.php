<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is player
if (!isLoggedIn() || !isPlayer()) {
    redirect('../index.php');
}

// Get player's upcoming events (registered)
$upcomingEvents = [];
$sql = "SELECT e.* FROM events e
        JOIN registrations r ON e.id = r.event_id
        WHERE r.user_id = ? AND e.event_date >= CURDATE()
        ORDER BY e.event_date ASC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $upcomingEvents[] = $row;
    }
}

// Get player's recent results
$recentResults = [];
$sql = "SELECT r.*, e.title as event_title, e.event_date
        FROM results r
        JOIN events e ON r.event_id = e.id
        WHERE r.user_id = ?
        ORDER BY e.event_date DESC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentResults[] = $row;
    }
}

// Get registration stats
$sql = "SELECT
            COUNT(*) as total_registrations,
            SUM(CASE WHEN e.event_date < CURDATE() THEN 1 ELSE 0 END) as past_events,
            SUM(CASE WHEN e.event_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming_events
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        WHERE r.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();

// Include header
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include '../includes/sidebar-player.php'; ?>
    </div>
    <div class="col-md-9">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg mb-6 p-6 text-white animate-fadeIn">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Welcome, <?php echo $_SESSION['full_name']; ?></h2>
                    <p class="text-blue-100">Track your events, registrations, and results from one place</p>
                </div>
                <div class="hidden md:block">
                    <i class="fas fa-running text-6xl text-blue-200 opacity-50"></i>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="events.php" class="bg-white text-blue-700 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm hover:shadow-md transition-all duration-300 flex items-center">
                    <i class="fas fa-calendar-plus mr-2"></i> Browse Events
                </a>
                <a href="teams.php" class="bg-white text-blue-700 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm hover:shadow-md transition-all duration-300 flex items-center">
                    <i class="fas fa-users mr-2"></i> My Teams
                </a>
                <a href="results.php" class="bg-white text-blue-700 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm hover:shadow-md transition-all duration-300 flex items-center">
                    <i class="fas fa-medal mr-2"></i> View Results
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl animate-fadeIn">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-blue-100 text-sm font-medium uppercase tracking-wider">Total Registrations</p>
                        <h3 class="text-3xl font-bold mt-2"><?php echo $stats['total_registrations']; ?></h3>
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 p-3 rounded-full">
                        <i class="fas fa-calendar-check text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="my-events.php" class="inline-flex items-center text-sm font-medium text-blue-100 hover:text-white">
                        View Details <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl animate-fadeIn" style="animation-delay: 0.2s;">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-green-100 text-sm font-medium uppercase tracking-wider">Upcoming Events</p>
                        <h3 class="text-3xl font-bold mt-2"><?php echo $stats['upcoming_events']; ?></h3>
                    </div>
                    <div class="bg-green-400 bg-opacity-30 p-3 rounded-full">
                        <i class="fas fa-calendar-alt text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="my-events.php" class="inline-flex items-center text-sm font-medium text-green-100 hover:text-white">
                        View Details <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <div class="bg-gradient-to-br from-gray-500 to-gray-700 rounded-xl shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl animate-fadeIn" style="animation-delay: 0.4s;">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-100 text-sm font-medium uppercase tracking-wider">Past Events</p>
                        <h3 class="text-3xl font-bold mt-2"><?php echo $stats['past_events']; ?></h3>
                    </div>
                    <div class="bg-gray-400 bg-opacity-30 p-3 rounded-full">
                        <i class="fas fa-history text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="results.php" class="inline-flex items-center text-sm font-medium text-gray-100 hover:text-white">
                        View Results <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Upcoming Events -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 animate-fadeIn" style="animation-delay: 0.4s;">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-3 text-white flex justify-between items-center">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-calendar-alt mr-2"></i> Your Upcoming Events
                    </h3>
                    <a href="my-events.php" class="text-xs bg-white bg-opacity-80 hover:bg-opacity-100 px-2 py-1 rounded text-black transition-all duration-300">
                        View All
                    </a>
                </div>
                <div class="p-4">
                    <?php if (empty($upcomingEvents)): ?>
                        <div class="flex flex-col items-center justify-center py-6 text-gray-500">
                            <i class="fas fa-calendar-times text-4xl mb-3 text-gray-300"></i>
                            <p>You have no upcoming events.</p>
                            <a href="events.php" class="mt-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-search mr-2"></i> Browse Available Events
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($upcomingEvents as $event): ?>
                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all duration-300">
                                    <div class="flex justify-between items-start">
                                        <h5 class="font-semibold text-gray-800"><?php echo $event['title']; ?></h5>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-calendar-day mr-1"></i>
                                            <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1"><?php echo substr($event['description'], 0, 100) . (strlen($event['description']) > 100 ? '...' : ''); ?></p>
                                    <div class="flex items-center mt-2 text-xs text-gray-500">
                                        <i class="fas fa-map-marker-alt mr-1 text-red-500"></i>
                                        <?php echo $event['location']; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Results -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 animate-fadeIn" style="animation-delay: 0.6s;">
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 py-3 text-white flex justify-between items-center">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-medal mr-2"></i> Your Recent Results
                    </h3>
                    <a href="results.php" class="text-xs bg-white bg-opacity-80 hover:bg-opacity-100 px-2 py-1 rounded text-black transition-all duration-300">
                        View All
                    </a>
                </div>
                <div class="p-4">
                    <?php if (empty($recentResults)): ?>
                        <div class="flex flex-col items-center justify-center py-6 text-gray-500">
                            <i class="fas fa-trophy text-4xl mb-3 text-gray-300"></i>
                            <p>You have no results yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($recentResults as $result): ?>
                                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                                            <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $result['event_title']; ?></td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y', strtotime($result['event_date'])); ?></td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                                                <?php if ($result['position']): ?>
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
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500"><?php echo $result['score'] ? $result['score'] : '-'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Available Events -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 mt-6 animate-fadeIn" style="animation-delay: 0.8s;">
            <div class="bg-gradient-to-r from-gray-600 to-gray-700 px-4 py-3 text-white flex justify-between items-center">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-calendar-plus mr-2"></i> Available Events for Registration
                </h3>
                <a href="events.php" class="text-xs bg-white bg-opacity-80 hover:bg-opacity-100 px-2 py-1 rounded text-black transition-all duration-300">
                    View All
                </a>
            </div>
            <div class="p-4">
                <?php
                // Get available events for registration
                $availableEvents = [];
                $sql = "SELECT e.* FROM events e
                        WHERE e.event_date >= CURDATE()
                        AND e.registration_deadline >= CURDATE()
                        AND e.status = 'upcoming'
                        AND e.id NOT IN (SELECT event_id FROM registrations WHERE user_id = ?)
                        ORDER BY e.event_date ASC
                        LIMIT 3";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php while ($event = $result->fetch_assoc()) { ?>
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-300 hover:border-green-300 transform hover:-translate-y-1">
                            <div class="p-4">
                                <div class="flex justify-between items-start">
                                    <h5 class="font-semibold text-gray-800 mb-2"><?php echo $event['title']; ?></h5>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-calendar-day mr-1"></i>
                                        <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3"><?php echo substr($event['description'], 0, 100) . (strlen($event['description']) > 100 ? '...' : ''); ?></p>
                                <div class="flex items-center mb-2 text-xs text-gray-500">
                                    <i class="fas fa-map-marker-alt mr-1 text-red-500"></i>
                                    <?php echo $event['location']; ?>
                                </div>
                                <div class="flex items-center mb-3 text-xs text-gray-500">
                                    <i class="fas fa-clock mr-1 text-orange-500"></i>
                                    Registration Deadline: <?php echo date('M d, Y', strtotime($event['registration_deadline'])); ?>
                                </div>
                                <a href="register-event.php?id=<?php echo $event['id']; ?>" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-300">
                                    <i class="fas fa-check-circle mr-2"></i> Register Now
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="flex flex-col items-center justify-center py-6 text-gray-500">
                        <i class="fas fa-calendar-times text-4xl mb-3 text-gray-300"></i>
                        <p>No available events for registration at the moment.</p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
