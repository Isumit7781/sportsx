<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

// Get dashboard statistics
$totalPlayers = getTotalPlayersCount();
$upcomingEvents = getUpcomingEventsCount();

// Get recent registrations
$recentRegistrations = [];
$sql = "SELECT r.id, r.registration_date, r.status, u.username, u.full_name, e.title
        FROM registrations r
        JOIN users u ON r.user_id = u.id
        JOIN events e ON r.event_id = e.id
        ORDER BY r.registration_date DESC
        LIMIT 5";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentRegistrations[] = $row;
    }
}

// Get upcoming events
$upcomingEventsList = [];
$sql = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 5";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $upcomingEventsList[] = $row;
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
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-lg mb-6 p-6 text-white animate-fadeIn">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Welcome to Admin Dashboard</h2>
                    <p class="text-blue-100">Manage your sports events, players, and registrations from one place</p>
                </div>
                <div class="hidden md:block">
                    <i class="fas fa-tachometer-alt text-6xl text-blue-200 opacity-50"></i>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="events.php?action=add" class="bg-white text-blue-700 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm hover:shadow-md transition-all duration-300 flex items-center">
                    <i class="fas fa-plus mr-2"></i> New Event
                </a>
                <a href="player-add.php" class="bg-white text-blue-700 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm hover:shadow-md transition-all duration-300 flex items-center">
                    <i class="fas fa-user-plus mr-2"></i> Add Player
                </a>
                <a href="results.php?action=add" class="bg-white text-blue-700 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm hover:shadow-md transition-all duration-300 flex items-center">
                    <i class="fas fa-trophy mr-2"></i> Add Result
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl animate-fadeIn">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-blue-100 text-sm font-medium uppercase tracking-wider">Total Players</p>
                        <h3 class="text-3xl font-bold mt-2"><?php echo $totalPlayers; ?></h3>
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 p-3 rounded-full">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="players.php" class="inline-flex items-center text-sm font-medium text-blue-100 hover:text-white">
                        View Details <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl animate-fadeIn" style="animation-delay: 0.1s;">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-purple-100 text-sm font-medium uppercase tracking-wider">Upcoming Events</p>
                        <h3 class="text-3xl font-bold mt-2"><?php echo $upcomingEvents; ?></h3>
                    </div>
                    <div class="bg-purple-400 bg-opacity-30 p-3 rounded-full">
                        <i class="fas fa-calendar-alt text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="events.php" class="inline-flex items-center text-sm font-medium text-purple-100 hover:text-white">
                        View Details <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <?php
            $sql = "SELECT COUNT(*) as count FROM registrations";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $totalRegistrations = $row['count'];
            ?>

            <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl animate-fadeIn" style="animation-delay: 0.2s;">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-green-100 text-sm font-medium uppercase tracking-wider">Total Registrations</p>
                        <h3 class="text-3xl font-bold mt-2"><?php echo $totalRegistrations; ?></h3>
                    </div>
                    <div class="bg-green-400 bg-opacity-30 p-3 rounded-full">
                        <i class="fas fa-clipboard-list text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="registrations.php" class="inline-flex items-center text-sm font-medium text-green-100 hover:text-white">
                        View Details <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="bg-white rounded-xl shadow-md p-4 mb-6 border border-gray-200 animate-fadeIn" style="animation-delay: 0.3s;">
    <h3 class="text-lg font-semibold mb-3 flex items-center text-gray-700">
        <i class="fas fa-chart-bar text-red-500 mr-2"></i> Performance Overview
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="mr-3 bg-red-100 p-2 rounded-full">
                    <i class="fas fa-futbol text-red-500"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Total Matches</p>
                    <p class="font-medium">245 Played</p>
                </div>
            </div>
        </div>
        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="mr-3 bg-blue-100 p-2 rounded-full">
                    <i class="fas fa-users text-blue-500"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Active Players</p>
                    <p class="font-medium">58 Registered</p>
                </div>
            </div>
        </div>
        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="mr-3 bg-yellow-100 p-2 rounded-full">
                    <i class="fas fa-calendar-alt text-yellow-500"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Upcoming Events</p>
                    <p class="font-medium">3 Scheduled</p>
                </div>
            </div>
        </div>
        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="mr-3 bg-purple-100 p-2 rounded-full">
                    <i class="fas fa-heartbeat text-purple-500"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Injuries Reported</p>
                    <p class="font-medium text-red-600">2 Active</p>
                </div>
            </div>
        </div>
    </div>
</div>


            <!-- Recent Registrations -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 animate-fadeIn" style="animation-delay: 0.4s;">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-3 text-white flex justify-between items-center">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-clipboard-list mr-2"></i> Recent Registrations
                    </h3>
                    <a href="registrations.php" class="text-xs bg-white bg-opacity-80 hover:bg-opacity-100 px-2 py-1 rounded text-black transition-all duration-300">
                        View All
                    </a>
                </div>
                <div class="p-4">
                    <?php if (empty($recentRegistrations)): ?>
                        <div class="flex flex-col items-center justify-center py-6 text-gray-500">
                            <i class="fas fa-clipboard-list text-4xl mb-3 text-gray-300"></i>
                            <p>No registrations found</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Player</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($recentRegistrations as $reg): ?>
                                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $reg['full_name']; ?></td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700"><?php echo $reg['title']; ?></td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700"><?php echo date('M d, Y', strtotime($reg['registration_date'])); ?></td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm">
                                                <?php if ($reg['status'] == 'pending'): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <span class="w-1.5 h-1.5 mr-1.5 bg-yellow-400 rounded-full"></span>
                                                        Pending
                                                    </span>
                                                <?php elseif ($reg['status'] == 'approved'): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <span class="w-1.5 h-1.5 mr-1.5 bg-green-400 rounded-full"></span>
                                                        Approved
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <span class="w-1.5 h-1.5 mr-1.5 bg-red-400 rounded-full"></span>
                                                        Rejected
                                                    </span>
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

            <!-- Upcoming Events -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 animate-fadeIn" style="animation-delay: 0.5s;">
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-4 py-3 text-white flex justify-between items-center">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-calendar-alt mr-2"></i> Upcoming Events
                    </h3>
                    <a href="events.php" class="text-xs bg-white bg-opacity-80 hover:bg-opacity-100 px-2 py-1 rounded text-black transition-all duration-300">
                        View All
                    </a>
                </div>
                <div class="p-4">
                    <?php if (empty($upcomingEventsList)): ?>
                        <div class="flex flex-col items-center justify-center py-6 text-gray-500">
                            <i class="fas fa-calendar-alt text-4xl mb-3 text-gray-300"></i>
                            <p>No upcoming events found</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($upcomingEventsList as $event): ?>
                                <a href="event-edit.php?id=<?php echo $event['id']; ?>" class="block p-4 rounded-lg border border-gray-200 hover:border-purple-300 hover:shadow-md transition-all duration-300">
                                    <div class="flex justify-between items-start">
                                        <h4 class="text-lg font-semibold text-gray-800 mb-1"><?php echo $event['title']; ?></h4>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2"><?php echo substr($event['description'], 0, 100) . (strlen($event['description']) > 100 ? '...' : ''); ?></p>
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i class="fas fa-map-marker-alt mr-1 text-purple-500"></i>
                                        <?php echo $event['location']; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
