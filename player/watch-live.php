<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is player
if (!isLoggedIn() || !isPlayer()) {
    redirect('../index.php');
}

// Get stream ID if provided
$stream_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$selectedStream = null;

// Get all active live streams
$streams = [];
$sql = "SELECT ls.*, e.title as event_title, e.event_date
        FROM live_streams ls
        LEFT JOIN events e ON ls.event_id = e.id
        WHERE ls.is_active = 1
        ORDER BY ls.created_at DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $streams[] = $row;

        // If this is the selected stream or if no stream is selected yet, set it as selected
        if (($stream_id && $row['id'] == $stream_id) || (!$selectedStream && !$stream_id)) {
            $selectedStream = $row;
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
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg mb-6 p-6 text-white animate-fadeIn">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Watch Live</h2>
                    <p class="text-blue-100">Watch live streams of ongoing events</p>
                </div>
                <div class="hidden md:block">
                    <i class="fas fa-video text-6xl text-blue-200 opacity-50"></i>
                </div>
            </div>
        </div>

        <?php if (empty($streams)): ?>
            <!-- No Streams Available -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 animate-fadeIn">
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-4 py-3 text-white">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-info-circle mr-2"></i> No Live Streams
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex flex-col items-center justify-center py-6 text-gray-500">
                        <i class="fas fa-video-slash text-6xl mb-4 text-gray-300"></i>
                        <p class="text-lg mb-2">No live streams are currently available.</p>
                        <p>Please check back later for upcoming event broadcasts.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Video Player -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Main Video Area -->
                <div class="md:col-span-2">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 animate-fadeIn">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-3 text-white">
                            <h3 class="text-lg font-semibold flex items-center">
                                <i class="fas fa-play-circle mr-2"></i> <?php echo $selectedStream ? $selectedStream['title'] : 'No Stream Selected'; ?>
                            </h3>
                        </div>
                        <div class="p-4">
                            <?php if ($selectedStream): ?>
                                <div class="aspect-w-16 aspect-h-9 mb-4">
                                    <?php
                                    $videoUrl = $selectedStream['video_url'];

                                    // Check if it's a YouTube URL
                                    if (strpos($videoUrl, 'youtube.com/watch?v=') !== false || strpos($videoUrl, 'youtu.be/') !== false) {
                                        // Extract video ID
                                        $videoId = '';
                                        if (strpos($videoUrl, 'youtube.com/watch?v=') !== false) {
                                            $videoId = explode('v=', $videoUrl)[1];
                                            $ampersandPosition = strpos($videoId, '&');
                                            if ($ampersandPosition !== false) {
                                                $videoId = substr($videoId, 0, $ampersandPosition);
                                            }
                                        } else if (strpos($videoUrl, 'youtu.be/') !== false) {
                                            $videoId = explode('youtu.be/', $videoUrl)[1];
                                        }

                                        // Create embed code
                                        if ($videoId) {
                                            echo '<iframe width="100%" height="100%" src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                                        } else {
                                            echo '<div class="flex items-center justify-center h-full bg-gray-100 text-gray-500">Invalid YouTube URL</div>';
                                        }
                                    } else if (strpos($videoUrl, '<iframe') !== false) {
                                        // It's already an embed code
                                        echo $videoUrl;
                                    } else {
                                        // Unknown format
                                        echo '<div class="flex items-center justify-center h-full bg-gray-100 text-gray-500">Unsupported video format</div>';
                                    }
                                    ?>
                                </div>

                                <?php if ($selectedStream['description']): ?>
                                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                        <h4 class="text-lg font-semibold mb-2">About this stream</h4>
                                        <p class="text-gray-700"><?php echo nl2br($selectedStream['description']); ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if ($selectedStream['event_title']): ?>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-calendar-alt mr-2 text-orange-500"></i>
                                        <span>Related Event: <strong><?php echo $selectedStream['event_title']; ?></strong> (<?php echo date('F j, Y', strtotime($selectedStream['event_date'])); ?>)</span>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="flex flex-col items-center justify-center py-12 text-gray-500">
                                    <i class="fas fa-video-slash text-6xl mb-4 text-gray-300"></i>
                                    <p class="text-lg">No stream selected.</p>
                                    <p>Please select a stream from the list.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Streams List -->
                <div class="md:col-span-1">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 animate-fadeIn">
                        <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 py-3 text-white">
                            <h3 class="text-lg font-semibold flex items-center">
                                <i class="fas fa-list mr-2"></i> Available Streams
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
                                <?php foreach ($streams as $stream): ?>
                                    <a href="watch-live.php?id=<?php echo $stream['id']; ?>"
                                       class="block p-3 rounded-lg border <?php echo ($selectedStream && $selectedStream['id'] == $stream['id']) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300 hover:bg-blue-50'; ?> transition-all duration-300">
                                        <h4 class="font-semibold text-gray-800 mb-1"><?php echo $stream['title']; ?></h4>
                                        <?php if ($stream['event_title']): ?>
                                            <div class="flex items-center text-xs text-gray-500 mb-1">
                                                <i class="fas fa-calendar-day mr-1 text-blue-500"></i>
                                                <?php echo $stream['event_title']; ?> (<?php echo date('M d, Y', strtotime($stream['event_date'])); ?>)
                                            </div>
                                        <?php endif; ?>
                                        <div class="flex items-center text-xs text-gray-500">
                                            <i class="fas fa-clock mr-1 text-orange-500"></i>
                                            Added <?php echo date('M d, Y', strtotime($stream['created_at'])); ?>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

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
