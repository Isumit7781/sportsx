<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$error = '';
$success = '';

// Handle registration status update
if (isset($_GET['approve']) && !empty($_GET['approve'])) {
    $registrationId = (int)$_GET['approve'];

    $sql = "UPDATE registrations SET status = 'approved' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $registrationId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $success = "Registration approved successfully.";
    } else {
        $error = "Failed to approve registration.";
    }
}

if (isset($_GET['reject']) && !empty($_GET['reject'])) {
    $registrationId = (int)$_GET['reject'];

    $sql = "UPDATE registrations SET status = 'rejected' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $registrationId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $success = "Registration rejected successfully.";
    } else {
        $error = "Failed to reject registration.";
    }
}

if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $registrationId = (int)$_GET['delete'];

    $sql = "DELETE FROM registrations WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $registrationId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $success = "Registration deleted successfully.";
    } else {
        $error = "Failed to delete registration.";
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

// Get registrations
$registrations = [];
$sql = "SELECT r.*, u.username, u.full_name, u.email, e.title as event_title, e.event_date
        FROM registrations r
        JOIN users u ON r.user_id = u.id
        JOIN events e ON r.event_id = e.id";

if ($eventFilter) {
    $sql .= " WHERE r.event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventFilter);
} else {
    $sql .= " ORDER BY r.registration_date DESC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $registrations[] = $row;
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
        <!-- Page Header -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 animate-fadeIn">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-clipboard-list text-green-600 mr-3"></i> Registrations Management
                </h2>
                <p class="text-gray-600 mt-1">Manage player registrations for all events</p>
            </div>
            <div>
                <form action="" method="get" class="flex flex-col sm:flex-row gap-2">
                    <div class="relative">
                        <select name="event_id" class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-all duration-300 w-full">
                            <option value="">All Events</option>
                            <?php foreach ($events as $event): ?>
                                <option value="<?php echo $event['id']; ?>" <?php echo $eventFilter == $event['id'] ? 'selected' : ''; ?>>
                                    <?php echo $event['title']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-filter text-gray-400"></i>
                        </div>
                    </div>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-center">
                        <i class="fas fa-search mr-2"></i> Filter
                    </button>
                </form>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow-sm animate-fadeIn">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <p><?php echo $error; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md shadow-sm animate-fadeIn">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <p><?php echo $success; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Status Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 animate-fadeIn">
            <?php
            $pendingCount = 0;
            $approvedCount = 0;
            $rejectedCount = 0;

            foreach ($registrations as $reg) {
                if ($reg['status'] == 'pending') $pendingCount++;
                else if ($reg['status'] == 'approved') $approvedCount++;
                else if ($reg['status'] == 'rejected') $rejectedCount++;
            }
            ?>

            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="bg-yellow-100 p-3 rounded-full mr-4">
                        <i class="fas fa-clock text-yellow-500"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Pending</p>
                        <p class="text-xl font-bold"><?php echo $pendingCount; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-full mr-4">
                        <i class="fas fa-check text-green-500"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Approved</p>
                        <p class="text-xl font-bold"><?php echo $approvedCount; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-red-500">
                <div class="flex items-center">
                    <div class="bg-red-100 p-3 rounded-full mr-4">
                        <i class="fas fa-times text-red-500"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Rejected</p>
                        <p class="text-xl font-bold"><?php echo $rejectedCount; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registrations Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 animate-fadeIn">
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 py-3 text-white">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-list mr-2"></i> Registrations List
                </h3>
            </div>
            <div class="p-4">
                <?php if (empty($registrations)): ?>
                    <div class="flex flex-col items-center justify-center py-12 text-gray-500">
                        <i class="fas fa-clipboard-list text-5xl mb-4 text-gray-300"></i>
                        <p class="text-xl font-medium mb-2">No registrations found</p>
                        <p class="text-gray-500">Try changing your filter criteria</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Player</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Date</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration Date</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($registrations as $registration): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $registration['id']; ?></td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                                                    <?php echo strtoupper(substr($registration['full_name'], 0, 1)); ?>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <a href="player-edit.php?id=<?php echo $registration['user_id']; ?>" class="hover:text-blue-600 transition-colors duration-200">
                                                            <?php echo $registration['full_name']; ?>
                                                        </a>
                                                    </div>
                                                    <div class="text-sm text-gray-500"><?php echo $registration['email']; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <a href="event-edit.php?id=<?php echo $registration['event_id']; ?>" class="hover:text-purple-600 transition-colors duration-200">
                                                    <?php echo $registration['event_title']; ?>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y', strtotime($registration['event_date'])); ?></td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y', strtotime($registration['registration_date'])); ?></td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                            <?php if ($registration['status'] == 'pending'): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <span class="w-1.5 h-1.5 mr-1.5 bg-yellow-400 rounded-full"></span>
                                                    Pending
                                                </span>
                                            <?php elseif ($registration['status'] == 'approved'): ?>
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
                                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <?php if ($registration['status'] == 'pending'): ?>
                                                    <a href="registrations.php?approve=<?php echo $registration['id']; ?><?php echo $eventFilter ? '&event_id=' . $eventFilter : ''; ?>" class="text-green-600 hover:text-green-900 bg-green-100 hover:bg-green-200 p-2 rounded-full transition-colors duration-200" data-bs-toggle="tooltip" title="Approve Registration">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                    <a href="registrations.php?reject=<?php echo $registration['id']; ?><?php echo $eventFilter ? '&event_id=' . $eventFilter : ''; ?>" class="text-yellow-600 hover:text-yellow-900 bg-yellow-100 hover:bg-yellow-200 p-2 rounded-full transition-colors duration-200" data-bs-toggle="tooltip" title="Reject Registration">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="registrations.php?delete=<?php echo $registration['id']; ?><?php echo $eventFilter ? '&event_id=' . $eventFilter : ''; ?>" class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 p-2 rounded-full transition-colors duration-200 delete-btn" data-bs-toggle="tooltip" title="Delete Registration">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6 mt-4">
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing <span class="font-medium">1</span> to <span class="font-medium"><?php echo count($registrations); ?></span> of <span class="font-medium"><?php echo count($registrations); ?></span> registrations
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <span class="sr-only">Previous</span>
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                    <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>
                                    <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <span class="sr-only">Next</span>
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </nav>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
