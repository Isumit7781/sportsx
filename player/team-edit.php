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

// Check if user is the team captain
$sql = "SELECT t.* FROM teams t 
        JOIN team_members tm ON t.id = tm.team_id 
        WHERE t.id = ? AND tm.user_id = ? AND tm.is_captain = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $teamId, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    redirect('teams.php');
}

$team = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $team_name = sanitize($_POST['team_name']);
    $team_description = sanitize($_POST['team_description']);
    
    if (empty($team_name)) {
        $error = 'Please enter a team name';
    } else {
        // Check if team name already exists (excluding current team)
        $sql = "SELECT id FROM teams WHERE name = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $team_name, $teamId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Team name already exists';
        } else {
            // Update team
            $sql = "UPDATE teams SET name = ?, description = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $team_name, $team_description, $teamId);
            
            if ($stmt->execute()) {
                $success = 'Team updated successfully';
                // Refresh team data
                $sql = "SELECT * FROM teams WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $teamId);
                $stmt->execute();
                $result = $stmt->get_result();
                $team = $result->fetch_assoc();
            } else {
                $error = 'Failed to update team: ' . $conn->error;
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Team</h2>
            <a href="team-details.php?id=<?php echo $teamId; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Team Details
            </a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-edit me-2"></i>Edit Team Information
            </div>
            <div class="card-body">
                <form method="post" action="" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="team_name" class="form-label">Team Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="team_name" name="team_name" value="<?php echo $team['name']; ?>" required>
                        <div class="invalid-feedback">Please enter a team name</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="team_description" class="form-label">Description</label>
                        <textarea class="form-control" id="team_description" name="team_description" rows="5"><?php echo $team['description']; ?></textarea>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">Update Team</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
