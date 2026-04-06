<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

// Handle player deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $playerId = (int)$_GET['delete'];

    // Don't allow deleting the admin account
    $sql = "SELECT role FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $playerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user['role'] != ROLE_ADMIN) {
        $sql = "DELETE FROM users WHERE id = ? AND role = ?";
        $stmt = $conn->prepare($sql);
        $playerRole = ROLE_PLAYER;
        $stmt->bind_param("is", $playerId, $playerRole);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $successMessage = "Player deleted successfully.";
        } else {
            $errorMessage = "Failed to delete player.";
        }
    } else {
        $errorMessage = "Cannot delete admin account.";
    }
}

// Get all players
$players = [];
$sql = "SELECT * FROM users WHERE role = ? ORDER BY full_name";
$stmt = $conn->prepare($sql);
$playerRole = ROLE_PLAYER;
$stmt->bind_param("s", $playerRole);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $players[] = $row;
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
                    <i class="fas fa-users text-blue-600 mr-3"></i> Players Management
                </h2>
                <p class="text-gray-600 mt-1">Manage all player accounts and profiles</p>
            </div>
            <a href="player-add.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm hover:shadow-md transition-all duration-300 flex items-center">
                <i class="fas fa-plus mr-2"></i> Add New Player
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
                        <input type="text" id="playerSearch" placeholder="Search players..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <select id="playerFilter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                        <option value="all">All Players</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <button type="button" id="refreshPlayers" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-all duration-300">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Players Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 animate-fadeIn">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-3 text-white">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-list mr-2"></i> Players List
                </h3>
            </div>
            <div class="p-4">
                <?php if (empty($players)): ?>
                    <div class="flex flex-col items-center justify-center py-12 text-gray-500">
                        <i class="fas fa-users text-5xl mb-4 text-gray-300"></i>
                        <p class="text-xl font-medium mb-2">No players found</p>
                        <p class="text-gray-500">Add new players to get started</p>
                        <a href="player-add.php" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm hover:shadow-md transition-all duration-300 flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add New Player
                        </a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="playersTableBody">
                                <?php foreach ($players as $player): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $player['id']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                                                    <?php echo strtoupper(substr($player['full_name'], 0, 1)); ?>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo $player['full_name']; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $player['username']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $player['email']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $player['phone'] ? $player['phone'] : 'N/A'; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y', strtotime($player['created_at'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <a href="player-edit.php?id=<?php echo $player['id']; ?>" class="text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 p-2 rounded-full transition-colors duration-200" data-bs-toggle="tooltip" title="Edit Player">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="players.php?delete=<?php echo $player['id']; ?>" class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 p-2 rounded-full transition-colors duration-200 delete-btn" data-bs-toggle="tooltip" title="Delete Player">
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
                                    Showing <span class="font-medium">1</span> to <span class="font-medium"><?php echo count($players); ?></span> of <span class="font-medium"><?php echo count($players); ?></span> players
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
                const searchInput = document.getElementById('playerSearch');
                const filterSelect = document.getElementById('playerFilter');
                const refreshButton = document.getElementById('refreshPlayers');
                const tableBody = document.getElementById('playersTableBody');

                if (searchInput && tableBody) {
                    searchInput.addEventListener('keyup', filterPlayers);
                }

                if (filterSelect && tableBody) {
                    filterSelect.addEventListener('change', filterPlayers);
                }

                if (refreshButton) {
                    refreshButton.addEventListener('click', function() {
                        window.location.reload();
                    });
                }

                function filterPlayers() {
                    const searchValue = searchInput.value.toLowerCase();
                    const filterValue = filterSelect.value;

                    const rows = tableBody.querySelectorAll('tr');

                    rows.forEach(row => {
                        const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                        const username = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                        const email = row.querySelector('td:nth-child(4)').textContent.toLowerCase();

                        const matchesSearch = name.includes(searchValue) ||
                                           username.includes(searchValue) ||
                                           email.includes(searchValue);

                        // For demonstration, we're not actually filtering by active/inactive
                        // In a real implementation, you would check the status
                        const matchesFilter = filterValue === 'all' || true;

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
