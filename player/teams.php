<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is player
if (!isLoggedIn() || !isPlayer()) {
    redirect('../index.php');
}

$error = '';
$success = '';

// Process team creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create_team') {
    $team_name = sanitize($_POST['team_name']);
    $team_description = sanitize($_POST['team_description']);

    if (empty($team_name)) {
        $error = 'Please enter a team name';
    } else {
        // Check if team name already exists
        $sql = "SELECT id FROM teams WHERE name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $team_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Team name already exists';
        } else {
            // Create new team
            $sql = "INSERT INTO teams (name, description, created_by) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $team_name, $team_description, $_SESSION['user_id']);

            if ($stmt->execute()) {
                $team_id = $conn->insert_id;

                // Add creator as team captain
                $sql = "INSERT INTO team_members (team_id, user_id, is_captain) VALUES (?, ?, 1)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $team_id, $_SESSION['user_id']);
                $stmt->execute();

                $success = 'Team created successfully';
            } else {
                $error = 'Failed to create team: ' . $conn->error;
            }
        }
    }
}

// Process team join
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'join_team') {
    $team_id = (int)$_POST['team_id'];

    // Check if user is already a member of this team
    $sql = "SELECT id FROM team_members WHERE team_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $team_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = 'You are already a member of this team';
    } else {
        // Join team
        $sql = "INSERT INTO team_members (team_id, user_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $team_id, $_SESSION['user_id']);

        if ($stmt->execute()) {
            $success = 'You have joined the team successfully';
        } else {
            $error = 'Failed to join team: ' . $conn->error;
        }
    }
}

// Process team leave
if (isset($_GET['leave']) && !empty($_GET['leave'])) {
    $team_id = (int)$_GET['leave'];

    // Check if user is a captain
    $sql = "SELECT is_captain FROM team_members WHERE team_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $team_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $member = $result->fetch_assoc();

        if ($member['is_captain']) {
            $error = 'Team captains cannot leave their team. You must transfer captaincy or delete the team.';
        } else {
            // Leave team
            $sql = "DELETE FROM team_members WHERE team_id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $team_id, $_SESSION['user_id']);

            if ($stmt->execute()) {
                $success = 'You have left the team successfully';
            } else {
                $error = 'Failed to leave team: ' . $conn->error;
            }
        }
    } else {
        $error = 'You are not a member of this team';
    }
}

// Process team deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $team_id = (int)$_GET['delete'];

    // Check if user is the team captain
    $sql = "SELECT t.id FROM teams t
            JOIN team_members tm ON t.id = tm.team_id
            WHERE t.id = ? AND tm.user_id = ? AND tm.is_captain = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $team_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Delete team
        $sql = "DELETE FROM teams WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $team_id);

        if ($stmt->execute()) {
            $success = 'Team deleted successfully';
        } else {
            $error = 'Failed to delete team: ' . $conn->error;
        }
    } else {
        $error = 'You do not have permission to delete this team';
    }
}

// Get teams created by the user
$myTeams = [];
$sql = "SELECT t.*, COUNT(tm.id) as member_count
        FROM teams t
        LEFT JOIN team_members tm ON t.id = tm.team_id
        JOIN team_members tm2 ON t.id = tm2.team_id AND tm2.user_id = ? AND tm2.is_captain = 1
        GROUP BY t.id
        ORDER BY t.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $myTeams[] = $row;
    }
}

// Get teams the user is a member of (but not captain)
$joinedTeams = [];
$sql = "SELECT t.*, COUNT(tm.id) as member_count, tm2.is_captain
        FROM teams t
        LEFT JOIN team_members tm ON t.id = tm.team_id
        JOIN team_members tm2 ON t.id = tm2.team_id AND tm2.user_id = ? AND tm2.is_captain = 0
        GROUP BY t.id
        ORDER BY t.name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $joinedTeams[] = $row;
    }
}

// Get available teams to join
$availableTeams = [];
$sql = "SELECT t.*, COUNT(tm.id) as member_count, u.username as captain_name
        FROM teams t
        LEFT JOIN team_members tm ON t.id = tm.team_id
        JOIN team_members tm2 ON t.id = tm2.team_id AND tm2.is_captain = 1
        JOIN users u ON tm2.user_id = u.id
        WHERE t.id NOT IN (
            SELECT team_id FROM team_members WHERE user_id = ?
        )
        GROUP BY t.id
        ORDER BY t.name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $availableTeams[] = $row;
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
                    <h2 class="text-2xl font-bold mb-2">Team Management</h2>
                    <p class="text-blue-100">Create and manage your sports teams</p>
                </div>
                <div class="hidden md:block">
                    <i class="fas fa-users text-6xl text-blue-200 opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mb-4">
            <button type="button" class="btn btn-primary text-white border-0 rounded-lg py-2 px-4 transition-all duration-300 shadow-sm hover:shadow-md" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                <i class="fas fa-plus me-2"></i>Create New Team
            </button>
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

        <!-- My Teams -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 mb-6 animate-fadeIn">
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-4 py-3 text-white">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-crown me-2"></i>My Teams (Captain)
                </h3>
            </div>
            <div class="card-body p-4">
                <?php if (empty($myTeams)): ?>
                    <p class="text-muted">You haven't created any teams yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover border-separate border-spacing-y-2">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-gray-600 font-medium rounded-l-lg">Team Name</th>
                                    <th class="px-4 py-3 text-gray-600 font-medium">Description</th>
                                    <th class="px-4 py-3 text-gray-600 font-medium">Members</th>
                                    <th class="px-4 py-3 text-gray-600 font-medium">Created</th>
                                    <th class="px-4 py-3 text-gray-600 font-medium rounded-r-lg">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myTeams as $team): ?>
                                    <tr class="bg-white hover:bg-orange-50 transition-colors duration-200">
                                        <td class="px-4 py-3 border-b border-gray-100">
                                            <div class="font-medium text-gray-800"><?php echo $team['name']; ?></div>
                                        </td>
                                        <td class="px-4 py-3 border-b border-gray-100 text-gray-600"><?php echo substr($team['description'], 0, 50) . (strlen($team['description']) > 50 ? '...' : ''); ?></td>
                                        <td class="px-4 py-3 border-b border-gray-100">
                                            <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded-full text-xs font-medium">
                                                <i class="fas fa-users me-1"></i><?php echo $team['member_count']; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 border-b border-gray-100 text-gray-600"><?php echo date('M d, Y', strtotime($team['created_at'])); ?></td>
                                        <td class="px-4 py-3 border-b border-gray-100">
                                            <div class="flex space-x-2">
                                                <a href="team-details.php?id=<?php echo $team['id']; ?>" class="btn btn-sm btn-info bg-blue-500 hover:bg-blue-600 text-white border-0 rounded-lg transition-all duration-300" data-bs-toggle="tooltip" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="team-edit.php?id=<?php echo $team['id']; ?>" class="btn btn-sm btn-primary bg-orange-500 hover:bg-orange-600 text-white border-0 rounded-lg transition-all duration-300" data-bs-toggle="tooltip" title="Edit Team">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=<?php echo $team['id']; ?>" class="btn btn-sm btn-danger bg-red-500 hover:bg-red-600 text-white border-0 rounded-lg transition-all duration-300 delete-btn" data-bs-toggle="tooltip" title="Delete Team">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Joined Teams -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 mb-6 animate-fadeIn">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-3 text-white">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-user-friends me-2"></i>Teams I've Joined
                </h3>
            </div>
            <div class="card-body p-4">
                <?php if (empty($joinedTeams)): ?>
                    <p class="text-muted">You haven't joined any teams yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover border-separate border-spacing-y-2">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-gray-600 font-medium rounded-l-lg">Team Name</th>
                                    <th class="px-4 py-3 text-gray-600 font-medium">Description</th>
                                    <th class="px-4 py-3 text-gray-600 font-medium">Members</th>
                                    <th class="px-4 py-3 text-gray-600 font-medium rounded-r-lg">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($joinedTeams as $team): ?>
                                    <tr class="bg-white hover:bg-blue-50 transition-colors duration-200">
                                        <td class="px-4 py-3 border-b border-gray-100">
                                            <div class="font-medium text-gray-800"><?php echo $team['name']; ?></div>
                                        </td>
                                        <td class="px-4 py-3 border-b border-gray-100 text-gray-600"><?php echo substr($team['description'], 0, 50) . (strlen($team['description']) > 50 ? '...' : ''); ?></td>
                                        <td class="px-4 py-3 border-b border-gray-100">
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                                                <i class="fas fa-users me-1"></i><?php echo $team['member_count']; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 border-b border-gray-100">
                                            <div class="flex space-x-2">
                                                <a href="team-details.php?id=<?php echo $team['id']; ?>" class="btn btn-sm btn-info bg-blue-500 hover:bg-blue-600 text-white border-0 rounded-lg transition-all duration-300" data-bs-toggle="tooltip" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="?leave=<?php echo $team['id']; ?>" class="btn btn-sm btn-warning bg-yellow-500 hover:bg-yellow-600 text-white border-0 rounded-lg transition-all duration-300 leave-btn" data-bs-toggle="tooltip" title="Leave Team">
                                                    <i class="fas fa-sign-out-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Available Teams -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 mb-6 animate-fadeIn">
            <div class="bg-gradient-to-r from-gray-500 to-gray-600 px-4 py-3 text-white">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-search me-2"></i>Available Teams to Join
                </h3>
            </div>
            <div class="card-body p-4">
                <?php if (empty($availableTeams)): ?>
                    <p class="text-muted">No available teams to join at the moment.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover border-separate border-spacing-y-2">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-gray-600 font-medium rounded-l-lg">Team Name</th>
                                    <th class="px-4 py-3 text-gray-600 font-medium">Description</th>
                                    <th class="px-4 py-3 text-gray-600 font-medium">Captain</th>
                                    <th class="px-4 py-3 text-gray-600 font-medium">Members</th>
                                    <th class="px-4 py-3 text-gray-600 font-medium rounded-r-lg">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($availableTeams as $team): ?>
                                    <tr class="bg-white hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-4 py-3 border-b border-gray-100">
                                            <div class="font-medium text-gray-800"><?php echo $team['name']; ?></div>
                                        </td>
                                        <td class="px-4 py-3 border-b border-gray-100 text-gray-600"><?php echo substr($team['description'], 0, 50) . (strlen($team['description']) > 50 ? '...' : ''); ?></td>
                                        <td class="px-4 py-3 border-b border-gray-100">
                                            <div class="flex items-center">
                                                <i class="fas fa-crown text-yellow-500 me-1"></i>
                                                <span class="text-gray-700"><?php echo $team['captain_name']; ?></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 border-b border-gray-100">
                                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs font-medium">
                                                <i class="fas fa-users me-1"></i><?php echo $team['member_count']; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 border-b border-gray-100">
                                            <form method="post" action="" class="d-inline">
                                                <input type="hidden" name="action" value="join_team">
                                                <input type="hidden" name="team_id" value="<?php echo $team['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-success bg-green-500 hover:bg-green-600 text-white border-0 rounded-lg py-2 px-3 transition-all duration-300" data-bs-toggle="tooltip" title="Join Team">
                                                    <i class="fas fa-plus me-1"></i> Join Team
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Create Team Modal -->
<div class="modal fade" id="createTeamModal" tabindex="-1" aria-labelledby="createTeamModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-lg overflow-hidden border-0 shadow-lg">
            <div class="modal-header bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                <h5 class="modal-title" id="createTeamModalLabel"><i class="fas fa-users me-2"></i>Create New Team</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="create_team">

                    <div class="mb-4">
                        <label for="team_name" class="form-label font-medium">Team Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-users text-orange-500"></i>
                            </span>
                            <input type="text" class="form-control rounded-end border-gray-300" id="team_name" name="team_name" placeholder="Enter team name" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="team_description" class="form-label font-medium">Description</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-align-left text-orange-500"></i>
                            </span>
                            <textarea class="form-control rounded-end border-gray-300" id="team_description" name="team_description" rows="3" placeholder="Describe your team (optional)"></textarea>
                        </div>
                    </div>

                    <div class="alert alert-info bg-blue-100 text-blue-800 rounded-xl border-blue-200 p-3 mb-0">
                        <i class="fas fa-info-circle me-2"></i> You will automatically be assigned as the team captain.
                    </div>
                </div>
                <div class="modal-footer bg-gray-50">
                    <button type="button" class="btn btn-secondary bg-gray-500 hover:bg-gray-600 text-white border-0 rounded-lg py-2 px-4 transition-all duration-300" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary bg-orange-500 hover:bg-orange-600 text-white border-0 rounded-lg py-2 px-4 transition-all duration-300">
                        <i class="fas fa-check me-2"></i>Create Team
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
