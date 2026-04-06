<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

// Process form submission for adding/editing live stream
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $video_url = sanitize($_POST['video_url']);
    $event_id = !empty($_POST['event_id']) ? (int)$_POST['event_id'] : null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (isset($_POST['stream_id']) && !empty($_POST['stream_id'])) {
        // Update existing stream
        $stream_id = (int)$_POST['stream_id'];
        $sql = "UPDATE live_streams SET title = ?, description = ?, video_url = ?, event_id = ?, is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            setSessionMessage('error', 'Error preparing statement: ' . $conn->error);
        } else {
            // Handle NULL event_id properly
            if ($event_id === null) {
                $stmt->bind_param("sssiii", $title, $description, $video_url, $null_value, $is_active, $stream_id);
                $null_value = null;
            } else {
                $stmt->bind_param("sssiii", $title, $description, $video_url, $event_id, $is_active, $stream_id);
            }

            if ($stmt->execute()) {
                setSessionMessage('success', 'Live stream updated successfully.');
            } else {
                setSessionMessage('error', 'Error updating live stream: ' . $stmt->error);
            }
        }
    } else {
        // Add new stream
        $sql = "INSERT INTO live_streams (title, description, video_url, event_id, is_active) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            setSessionMessage('error', 'Error preparing statement: ' . $conn->error);
        } else {
            // Handle NULL event_id properly
            if ($event_id === null) {
                $stmt->bind_param("sssii", $title, $description, $video_url, $null_value, $is_active);
                $null_value = null;
            } else {
                $stmt->bind_param("sssii", $title, $description, $video_url, $event_id, $is_active);
            }

            if ($stmt->execute()) {
                setSessionMessage('success', 'Live stream added successfully.');
            } else {
                setSessionMessage('error', 'Error adding live stream: ' . $stmt->error);
            }
        }
    }

    redirect('live-streams.php');
}

// Process stream deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $stream_id = (int)$_GET['delete'];
    $sql = "DELETE FROM live_streams WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        setSessionMessage('error', 'Error preparing statement: ' . $conn->error);
    } else {
        $stmt->bind_param("i", $stream_id);

        if ($stmt->execute()) {
            setSessionMessage('success', 'Live stream deleted successfully.');
        } else {
            setSessionMessage('error', 'Error deleting live stream: ' . $stmt->error);
        }
    }

    redirect('live-streams.php');
}

// Get stream for editing if ID is provided
$editStream = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $stream_id = (int)$_GET['edit'];
    $sql = "SELECT * FROM live_streams WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        setSessionMessage('error', 'Error preparing statement: ' . $conn->error);
    } else {
        $stmt->bind_param("i", $stream_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $editStream = $result->fetch_assoc();
        }
    }
}

// Get all events for dropdown
$events = [];
$sql = "SELECT id, title, event_date FROM events ORDER BY event_date DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

// Get all live streams
$streams = [];
$sql = "SELECT ls.*, e.title as event_title, e.event_date
        FROM live_streams ls
        LEFT JOIN events e ON ls.event_id = e.id
        ORDER BY ls.created_at DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $streams[] = $row;
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
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-lg mb-6 p-6 text-white animate-fadeIn">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Manage Live Streams</h2>
                    <p class="text-blue-100">Add, edit, or remove live stream links for events</p>
                </div>
                <div class="hidden md:block">
                    <i class="fas fa-video text-6xl text-blue-200 opacity-50"></i>
                </div>
            </div>
        </div>

        <?php echo displaySessionMessage(); ?>

        <!-- Add/Edit Live Stream Form -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 mb-6 animate-fadeIn">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-3 text-white">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i> <?php echo $editStream ? 'Edit Live Stream' : 'Add New Live Stream'; ?>
                </h3>
            </div>
            <div class="p-6">
                <form action="live-streams.php" method="post">
                    <?php if ($editStream): ?>
                        <input type="hidden" name="stream_id" value="<?php echo $editStream['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Stream Title</label>
                        <input type="text" id="title" name="title" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required value="<?php echo $editStream ? $editStream['title'] : ''; ?>">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"><?php echo $editStream ? $editStream['description'] : ''; ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="video_url" class="block text-sm font-medium text-gray-700 mb-1">YouTube Video URL or Embed Code</label>
                        <input type="text" id="video_url" name="video_url" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required value="<?php echo $editStream ? $editStream['video_url'] : ''; ?>">
                        <p class="text-sm text-gray-500 mt-1">Enter a YouTube URL (e.g., https://www.youtube.com/watch?v=VIDEO_ID) or full embed code</p>
                    </div>

                    <div class="mb-4">
                        <label for="event_id" class="block text-sm font-medium text-gray-700 mb-1">Related Event (Optional)</label>
                        <select id="event_id" name="event_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <option value="">-- Select Event --</option>
                            <?php foreach ($events as $event): ?>
                                <option value="<?php echo $event['id']; ?>" <?php echo ($editStream && $editStream['event_id'] == $event['id']) ? 'selected' : ''; ?>>
                                    <?php echo $event['title']; ?> (<?php echo date('M d, Y', strtotime($event['event_date'])); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" <?php echo (!$editStream || $editStream['is_active'] == 1) ? 'checked' : ''; ?>>
                            <span class="ml-2 text-sm text-gray-700">Active (visible to players)</span>
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <?php if ($editStream): ?>
                            <a href="live-streams.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2">
                                Cancel
                            </a>
                        <?php endif; ?>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <?php echo $editStream ? 'Update Stream' : 'Add Stream'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Live Streams List -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 animate-fadeIn">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-3 text-white">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-list mr-2"></i> Live Streams
                </h3>
            </div>
            <div class="p-6">
                <?php if (empty($streams)): ?>
                    <div class="flex flex-col items-center justify-center py-6 text-gray-500">
                        <i class="fas fa-video-slash text-4xl mb-3 text-gray-300"></i>
                        <p>No live streams have been added yet.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Related Event</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($streams as $stream): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo $stream['title']; ?>
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                                            <?php if ($stream['event_title']): ?>
                                                <?php echo $stream['event_title']; ?>
                                                <span class="text-xs text-gray-400">
                                                    (<?php echo date('M d, Y', strtotime($stream['event_date'])); ?>)
                                                </span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                                            <?php if ($stream['is_active']): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i> Active
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-times-circle mr-1"></i> Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($stream['created_at'])); ?>
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex space-x-2">
                                                <a href="live-streams.php?edit=<?php echo $stream['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="live-streams.php?delete=<?php echo $stream['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this live stream?');">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </a>
                                                <a href="#" class="text-gray-600 hover:text-gray-900" onclick="previewStream('<?php echo htmlspecialchars($stream['video_url'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($stream['title'], ENT_QUOTES); ?>')">
                                                    <i class="fas fa-eye"></i> Preview
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
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-3 text-white rounded-t-lg flex justify-between items-center">
            <h3 class="text-lg font-semibold" id="previewTitle">Stream Preview</h3>
            <button type="button" onclick="closePreview()" class="text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="aspect-w-16 aspect-h-9 mb-4">
                <div id="previewContent" class="w-full h-full"></div>
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="closePreview()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function previewStream(url, title) {
    const modal = document.getElementById('previewModal');
    const previewTitle = document.getElementById('previewTitle');
    const previewContent = document.getElementById('previewContent');

    previewTitle.textContent = title || 'Stream Preview';

    // Check if it's a YouTube URL
    if (url.includes('youtube.com/watch?v=') || url.includes('youtu.be/')) {
        // Extract video ID
        let videoId = '';
        if (url.includes('youtube.com/watch?v=')) {
            videoId = url.split('v=')[1];
            const ampersandPosition = videoId.indexOf('&');
            if (ampersandPosition !== -1) {
                videoId = videoId.substring(0, ampersandPosition);
            }
        } else if (url.includes('youtu.be/')) {
            videoId = url.split('youtu.be/')[1];
        }

        // Create embed code
        if (videoId) {
            previewContent.innerHTML = `<iframe width="100%" height="100%" src="https://www.youtube.com/embed/${videoId}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
        } else {
            previewContent.innerHTML = '<div class="flex items-center justify-center h-full bg-gray-100 text-gray-500">Invalid YouTube URL</div>';
        }
    } else if (url.includes('<iframe')) {
        // It's already an embed code
        previewContent.innerHTML = url;
    } else {
        // Unknown format
        previewContent.innerHTML = '<div class="flex items-center justify-center h-full bg-gray-100 text-gray-500">Unsupported video format</div>';
    }

    modal.classList.remove('hidden');
}

function closePreview() {
    const modal = document.getElementById('previewModal');
    const previewContent = document.getElementById('previewContent');

    previewContent.innerHTML = '';
    modal.classList.add('hidden');
}
</script>

<style>
/* Aspect ratio container for responsive video */
.aspect-w-16 {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
    height: 0;
}
.aspect-w-16 > * {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}
</style>

<?php include '../includes/footer.php'; ?>
