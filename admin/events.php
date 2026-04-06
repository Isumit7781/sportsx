<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

// Handle event deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $eventId = (int)$_GET['delete'];

    $sql = "DELETE FROM events WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $successMessage = "Event deleted successfully.";
    } else {
        $errorMessage = "Failed to delete event.";
    }
}

// Get all events
$events = [];
$sql = "SELECT * FROM events ORDER BY event_date DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
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
                    <i class="fas fa-calendar-alt text-purple-600 mr-3"></i> Events Management
                </h2>
                <p class="text-gray-600 mt-1">Create and manage all sports events</p>
            </div>
            <a href="event-add.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm hover:shadow-md transition-all duration-300 flex items-center">
                <i class="fas fa-plus mr-2"></i> Add New Event
            </a>
        </div>

        <?php if (isset($successMessage)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md shadow-sm animate-fadeIn">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <p><?php echo $successMessage; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($errorMessage)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow-sm animate-fadeIn">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <p><?php echo $errorMessage; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search and Filter -->
        <div class="bg-white rounded-xl shadow-md p-4 mb-6 border border-gray-200 animate-fadeIn">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-grow">
                    <div class="relative">
                        <input type="text" id="eventSearch" placeholder="Search events..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 transition-all duration-300">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <select id="eventFilter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300">
                        <option value="all">All Events</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button type="button" id="refreshEvents" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-all duration-300">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Events Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 animate-fadeIn">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-4 py-3 text-white">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-list mr-2"></i> Events List
                </h3>
            </div>
            <div class="p-4">
                <?php if (empty($events)): ?>
                    <div class="flex flex-col items-center justify-center py-12 text-gray-500">
                        <i class="fas fa-calendar-alt text-5xl mb-4 text-gray-300"></i>
                        <p class="text-xl font-medium mb-2">No events found</p>
                        <p class="text-gray-500">Add new events to get started</p>
                        <a href="event-add.php" class="mt-4 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm hover:shadow-md transition-all duration-300 flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add New Event
                        </a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrations</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="eventsTableBody">
                                <?php foreach ($events as $event): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $event['id']; ?></td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo $event['title']; ?></div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <i class="fas fa-map-marker-alt text-purple-500 mr-1"></i>
                                                <?php echo $event['location']; ?>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y', strtotime($event['registration_deadline'])); ?></td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                            <?php if ($event['status'] == 'upcoming'): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <span class="w-1.5 h-1.5 mr-1.5 bg-blue-400 rounded-full"></span>
                                                    Upcoming
                                                </span>
                                            <?php elseif ($event['status'] == 'ongoing'): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <span class="w-1.5 h-1.5 mr-1.5 bg-green-400 rounded-full"></span>
                                                    Ongoing
                                                </span>
                                            <?php elseif ($event['status'] == 'completed'): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <span class="w-1.5 h-1.5 mr-1.5 bg-gray-400 rounded-full"></span>
                                                    Completed
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <span class="w-1.5 h-1.5 mr-1.5 bg-red-400 rounded-full"></span>
                                                    Cancelled
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                            <?php
                                            $registrationsCount = getEventRegistrationsCount($event['id']);
                                            if ($event['max_participants']) {
                                                $percentage = ($registrationsCount / $event['max_participants']) * 100;
                                                $colorClass = $percentage < 50 ? 'bg-blue-500' : ($percentage < 80 ? 'bg-yellow-500' : 'bg-green-500');
                                                echo '<div class="flex items-center">';
                                                echo '<span class="mr-2">' . $registrationsCount . ' / ' . $event['max_participants'] . '</span>';
                                                echo '<div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">';
                                                echo '<div class="h-full ' . $colorClass . '" style="width: ' . min(100, $percentage) . '%"></div>';
                                                echo '</div>';
                                                echo '</div>';
                                            } else {
                                                echo $registrationsCount;
                                            }
                                            ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <a href="event-edit.php?id=<?php echo $event['id']; ?>" class="text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 p-2 rounded-full transition-colors duration-200" data-bs-toggle="tooltip" title="Edit Event">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="events.php?delete=<?php echo $event['id']; ?>" class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 p-2 rounded-full transition-colors duration-200 delete-btn" data-bs-toggle="tooltip" title="Delete Event">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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
                                    Showing <span class="font-medium">1</span> to <span class="font-medium"><?php echo count($events); ?></span> of <span class="font-medium"><?php echo count($events); ?></span> events
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

        <!-- JavaScript for Search and Filter -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('eventSearch');
                const filterSelect = document.getElementById('eventFilter');
                const refreshButton = document.getElementById('refreshEvents');
                const tableBody = document.getElementById('eventsTableBody');

                if (searchInput && tableBody) {
                    searchInput.addEventListener('keyup', filterEvents);
                }

                if (filterSelect && tableBody) {
                    filterSelect.addEventListener('change', filterEvents);
                }

                if (refreshButton) {
                    refreshButton.addEventListener('click', function() {
                        window.location.reload();
                    });
                }

                function filterEvents() {
                    const searchValue = searchInput.value.toLowerCase();
                    const filterValue = filterSelect.value;

                    const rows = tableBody.querySelectorAll('tr');

                    rows.forEach(row => {
                        const title = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                        const location = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                        const status = row.querySelector('td:nth-child(6)').textContent.toLowerCase();

                        const matchesSearch = title.includes(searchValue) ||
                                           location.includes(searchValue);

                        const matchesFilter = filterValue === 'all' || status.includes(filterValue.toLowerCase());

                        if (matchesSearch && matchesFilter) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                }
            });
        </script>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
