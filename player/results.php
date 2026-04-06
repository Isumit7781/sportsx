<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is player
if (!isLoggedIn() || !isPlayer()) {
    redirect('../index.php');
}

// Get event filter if provided
$eventFilter = isset($_GET['event_id']) ? (int)$_GET['event_id'] : null;

// Get all events with results
$allEvents = [];
$sql = "SELECT DISTINCT e.id, e.title, e.event_date
        FROM events e
        JOIN results r ON e.id = r.event_id
        WHERE e.event_date < CURDATE()
        ORDER BY e.event_date DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $allEvents[] = $row;
    }
}

// Get events the player has participated in
$playerEvents = [];
$sql = "SELECT DISTINCT e.id, e.title, e.event_date
        FROM events e
        JOIN registrations r ON e.id = r.event_id
        WHERE r.user_id = ? AND e.event_date < CURDATE()
        ORDER BY e.event_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $playerEvents[] = $row;
    }
}

// Get results
$results = [];
$sql = "SELECT r.*, u.username, u.full_name, e.title as event_title, e.event_date
        FROM results r
        JOIN users u ON r.user_id = u.id
        JOIN events e ON r.event_id = e.id";

if ($eventFilter) {
    // Filter by specific event
    $sql .= " WHERE r.event_id = ? ORDER BY r.position ASC, r.score ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventFilter);
} else {
    // Show all results for all events
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

// Get player's own results
$playerResults = [];
$sql = "SELECT r.*, e.title as event_title, e.event_date
        FROM results r
        JOIN events e ON r.event_id = e.id
        WHERE r.user_id = ?
        ORDER BY e.event_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $playerResults[] = $row;
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
                    <h2 class="text-2xl font-bold mb-2">Event Results</h2>
                    <p class="text-blue-100">View your achievements and all event results</p>
                </div>
                <div class="hidden md:block">
                    <i class="fas fa-trophy text-6xl text-blue-200 opacity-50"></i>
                </div>
            </div>
        </div>

        <!-- My Results Summary -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 mb-6 animate-fadeIn">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-3 text-white">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-medal mr-2"></i> My Results Summary
                </h3>
            </div>
            <div class="p-6">
                <?php if (empty($playerResults)): ?>
                    <div class="flex flex-col items-center justify-center py-6 text-gray-500">
                        <i class="fas fa-trophy text-4xl mb-3 text-gray-300"></i>
                        <p>You don't have any results yet.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php
                        // Count positions
                        $positions = [1 => 0, 2 => 0, 3 => 0];
                        $totalEvents = count($playerResults);

                        foreach ($playerResults as $result) {
                            if (isset($result['position']) && $result['position'] >= 1 && $result['position'] <= 3) {
                                $positions[$result['position']]++;
                            }
                        }
                        ?>
                        <div class="text-center p-4 bg-gray-50 rounded-xl border border-gray-200 transform transition-all duration-300 hover:shadow-md hover:-translate-y-1">
                            <div class="flex justify-center mb-3">
                                <div class="result-position individual position-1 w-16 h-16 flex items-center justify-center">
                                    <i class="fas fa-medal text-2xl" style="color: gold;"></i>
                                </div>
                            </div>
                            <h4 class="text-2xl font-bold text-gray-800"><?php echo $positions[1]; ?></h4>
                            <p class="text-sm font-medium text-gray-600 uppercase tracking-wider">Gold Medals</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-xl border border-gray-200 transform transition-all duration-300 hover:shadow-md hover:-translate-y-1">
                            <div class="flex justify-center mb-3">
                                <div class="result-position individual position-2 w-16 h-16 flex items-center justify-center">
                                    <i class="fas fa-medal text-2xl" style="color: silver;"></i>
                                </div>
                            </div>
                            <h4 class="text-2xl font-bold text-gray-800"><?php echo $positions[2]; ?></h4>
                            <p class="text-sm font-medium text-gray-600 uppercase tracking-wider">Silver Medals</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-xl border border-gray-200 transform transition-all duration-300 hover:shadow-md hover:-translate-y-1">
                            <div class="flex justify-center mb-3">
                                <div class="result-position individual position-3 w-16 h-16 flex items-center justify-center">
                                    <i class="fas fa-medal text-2xl" style="color: #cd7f32;"></i>
                                </div>
                            </div>
                            <h4 class="text-2xl font-bold text-gray-800"><?php echo $positions[3]; ?></h4>
                            <p class="text-sm font-medium text-gray-600 uppercase tracking-wider">Bronze Medals</p>
                        </div>
                    </div>
                    <div class="text-center mt-6 p-3 bg-blue-50 rounded-lg border border-blue-100">
                        <p class="text-blue-800">You have participated in <span class="font-bold"><?php echo $totalEvents; ?></span> events with recorded results.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Event Results -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 animate-fadeIn">
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 py-3 text-white flex justify-between items-center flex-wrap gap-2">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-medal mr-2"></i> Event Results
                </h3>
                <form action="" method="get" class="flex items-center space-x-2">
                    <select name="event_id" class="text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">All Events</option>
                        <?php foreach ($allEvents as $event): ?>
                            <option value="<?php echo $event['id']; ?>" <?php echo $eventFilter == $event['id'] ? 'selected' : ''; ?>>
                                <?php echo $event['title']; ?> (<?php echo date('M d, Y', strtotime($event['event_date'])); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-700 hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </form>
            </div>
            <div class="p-6">
                <?php if (empty($results)): ?>
                    <div class="flex flex-col items-center justify-center py-6 text-gray-500">
                        <i class="fas fa-search text-4xl mb-3 text-gray-300"></i>
                        <p>No results found for the selected event(s).</p>
                    </div>
                <?php else: ?>
                    <?php
                    // Group results by event
                    $eventResults = [];
                    foreach ($results as $result) {
                        $eventId = $result['event_id'];
                        if (!isset($eventResults[$eventId])) {
                            $eventResults[$eventId] = [
                                'title' => $result['event_title'],
                                'date' => $result['event_date'],
                                'results' => []
                            ];
                        }
                        $eventResults[$eventId]['results'][] = $result;
                    }

                    foreach ($eventResults as $eventId => $event):
                    ?>
                        <div class="mb-8 last:mb-0 animate-fadeIn">
                            <div class="flex items-center mb-3 pb-2 border-b border-gray-200">
                                <h4 class="text-xl font-semibold text-gray-800"><?php echo $event['title']; ?></h4>
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-calendar-day mr-1"></i>
                                    <?php echo date('F j, Y', strtotime($event['date'])); ?>
                                </span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Player</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($event['results'] as $result): ?>
                                            <tr class="<?php echo $result['user_id'] == $_SESSION['user_id'] ? 'bg-blue-50' : 'hover:bg-gray-50'; ?> transition-colors duration-200">
                                                <td class="px-3 py-3 whitespace-nowrap text-sm">
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
                                                        <small class="block text-gray-500">
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
                                                <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?php echo $result['full_name']; ?>
                                                    <?php if ($result['user_id'] == $_SESSION['user_id']): ?>
                                                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">You</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500"><?php echo $result['score'] ? $result['score'] : '-'; ?></td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500"><?php echo $result['remarks'] ? $result['remarks'] : '-'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
