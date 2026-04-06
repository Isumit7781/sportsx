<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is player
if (!isLoggedIn() || !isPlayer()) {
    redirect('../index.php');
}

$error = '';
$success = '';

// Check if team ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('teams.php');
}

$teamId = (int)$_GET['id'];

// Get team details
$sql = "SELECT t.*, u.username as creator_name, u.full_name as creator_full_name
        FROM teams t
        JOIN users u ON t.created_by = u.id
        WHERE t.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teamId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    redirect('teams.php');
}

$team = $result->fetch_assoc();

// Check if user is a member of this team
$sql = "SELECT is_captain FROM team_members WHERE team_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $teamId, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$isMember = $result->num_rows > 0;
$isCaptain = false;

if ($isMember) {
    $memberData = $result->fetch_assoc();
    $isCaptain = $memberData['is_captain'] == 1;
}

// Process member removal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'remove_member') {
    // Check if user is captain
    if (!$isCaptain) {
        $error = 'Only team captains can remove members';
    } else {
        $memberId = (int)$_POST['member_id'];

        // Check if trying to remove self (captain)
        if ($memberId == $_SESSION['user_id']) {
            $error = 'Captains cannot remove themselves from the team';
        } else {
            // Remove member
            $sql = "DELETE FROM team_members WHERE team_id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $teamId, $memberId);

            if ($stmt->execute()) {
                $success = 'Member removed successfully';
            } else {
                $error = 'Failed to remove member: ' . $conn->error;
            }
        }
    }
}

// Process captain transfer
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'transfer_captain') {
    // Check if user is captain
    if (!$isCaptain) {
        $error = 'Only team captains can transfer captaincy';
    } else {
        $newCaptainId = (int)$_POST['new_captain_id'];

        // Check if new captain is a member
        $sql = "SELECT id FROM team_members WHERE team_id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $teamId, $newCaptainId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $error = 'Selected user is not a member of this team';
        } else {
            // Begin transaction
            $conn->begin_transaction();

            try {
                // Remove captain status from current captain
                $sql = "UPDATE team_members SET is_captain = 0 WHERE team_id = ? AND user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $teamId, $_SESSION['user_id']);
                $stmt->execute();

                // Set new captain
                $sql = "UPDATE team_members SET is_captain = 1 WHERE team_id = ? AND user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $teamId, $newCaptainId);
                $stmt->execute();

                $conn->commit();
                $success = 'Team captaincy transferred successfully';
                $isCaptain = false; // Update local variable
            } catch (Exception $e) {
                $conn->rollback();
                $error = 'Failed to transfer captaincy: ' . $e->getMessage();
            }
        }
    }
}

// Get team members
$members = [];
$sql = "SELECT tm.*, u.username, u.full_name, u.email
        FROM team_members tm
        JOIN users u ON tm.user_id = u.id
        WHERE tm.team_id = ?
        ORDER BY tm.is_captain DESC, u.username ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teamId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
}

// Get team events
$events = [];
$sql = "SELECT e.*, tr.status as registration_status
        FROM events e
        JOIN team_registrations tr ON e.id = tr.event_id
        WHERE tr.team_id = ?
        ORDER BY e.event_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teamId);
$stmt->execute();
$result = $stmt->get_result();

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
        <?php include '../includes/sidebar-player.php'; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><?php echo $team['name']; ?></h2>
            <div>
                <?php if ($isCaptain): ?>
                    <a href="team-edit.php?id=<?php echo $teamId; ?>" class="btn btn-primary me-2">
                        <i class="fas fa-edit me-2"></i>Edit Team
                    </a>
                <?php endif; ?>
                <a href="teams.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Teams
                </a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php echo displaySessionMessage(); ?>

        <!-- Team Info -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-info-circle me-2"></i>Team Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Team Name:</strong> <?php echo $team['name']; ?></p>
                        <p><strong>Created By:</strong> <?php echo $team['creator_full_name']; ?> (<?php echo $team['creator_name']; ?>)</p>
                        <p><strong>Created On:</strong> <?php echo date('F j, Y', strtotime($team['created_at'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Description:</strong></p>
                        <p><?php echo nl2br($team['description']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Members -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <i class="fas fa-users me-2"></i>Team Members
            </div>
            <div class="card-body">
                <?php if (empty($members)): ?>
                    <p class="text-muted">No members found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <?php if ($isCaptain): ?>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $member): ?>
                                    <tr>
                                        <td><?php echo $member['full_name']; ?></td>
                                        <td><?php echo $member['username']; ?></td>
                                        <td><?php echo $member['email']; ?></td>
                                        <td>
                                            <?php if ($member['is_captain']): ?>
                                                <span class="badge bg-primary">Captain</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Member</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($isCaptain && !$member['is_captain']): ?>
                                            <td>
                                                <form method="post" action="" class="d-inline">
                                                    <input type="hidden" name="action" value="transfer_captain">
                                                    <input type="hidden" name="new_captain_id" value="<?php echo $member['user_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Make Captain">
                                                        <i class="fas fa-crown"></i>
                                                    </button>
                                                </form>
                                                <form method="post" action="" class="d-inline">
                                                    <input type="hidden" name="action" value="remove_member">
                                                    <input type="hidden" name="member_id" value="<?php echo $member['user_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Remove Member">
                                                        <i class="fas fa-user-minus"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        <?php elseif ($isCaptain): ?>
                                            <td>
                                                <span class="text-muted">You (Captain)</span>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Team Events -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="fas fa-calendar-alt me-2"></i>Team Events
            </div>
            <div class="card-body">
                <?php if (empty($events)): ?>
                    <p class="text-muted">This team hasn't registered for any events yet.</p>
                    <?php if ($isCaptain): ?>
                        <a href="events.php" class="btn btn-primary">Browse Available Events</a>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Registration Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $event): ?>
                                    <tr>
                                        <td><?php echo $event['title']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                        <td><?php echo $event['location']; ?></td>
                                        <td>
                                            <?php if ($event['status'] == 'upcoming'): ?>
                                                <span class="badge bg-primary">Upcoming</span>
                                            <?php elseif ($event['status'] == 'ongoing'): ?>
                                                <span class="badge bg-success">Ongoing</span>
                                            <?php elseif ($event['status'] == 'completed'): ?>
                                                <span class="badge bg-secondary">Completed</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Cancelled</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($event['registration_status'] == 'pending'): ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php elseif ($event['registration_status'] == 'approved'): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Rejected</span>
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
    </div>
</div>

<?php include '../includes/footer.php'; ?>
