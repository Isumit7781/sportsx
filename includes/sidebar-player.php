<div id="sidebar" class="bg-white rounded-xl shadow-md overflow-hidden mb-6 border-t-4 border-blue-500 transform transition-all duration-300 hover:shadow-lg md:translate-x-0 translate-x-[-100%] fixed md:relative top-0 left-0 h-full md:h-auto z-40 w-64 md:w-auto">
    <!-- Close button for mobile -->
    <div class="absolute top-2 right-2 md:hidden">
        <button id="close-sidebar" class="text-gray-500 hover:text-gray-700 focus:outline-none">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-4">
        <h3 class="text-white text-lg font-semibold flex items-center">
            <i class="fas fa-running mr-2"></i>
            <span>Player Menu</span>
        </h3>
        <div class="mt-3 flex items-center">
            <?php
            // Get user details to determine gender for avatar
            $user = getUserById($_SESSION['user_id']);
            $gender = isset($user['gender']) ? $user['gender'] : null;
            $avatarPath = getRandomAvatar($gender);
            ?>
            <img src="<?php echo BASE_URL . $avatarPath; ?>" alt="<?php echo $_SESSION['full_name']; ?>" class="w-10 h-10 rounded-full border-2 border-white object-cover mr-3">
            <div>
                <div class="text-white font-medium"><?php echo $_SESSION['full_name']; ?></div>
                <div class="text-xs text-blue-100"><?php echo ucfirst($_SESSION['role']); ?></div>
            </div>
        </div>
    </div>
    <div class="divide-y divide-gray-200 py-2">
        <a href="<?php echo BASE_URL; ?>player/index.php"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-blue-50 hover:pl-6 transition-all duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active bg-blue-50 border-l-4 border-blue-500 pl-5' : ''; ?>">
            <i class="fas fa-tachometer-alt text-blue-500 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">Dashboard</span>
        </a>
        <a href="<?php echo BASE_URL; ?>player/profile.php"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-blue-50 hover:pl-6 transition-all duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active bg-blue-50 border-l-4 border-blue-500 pl-5' : ''; ?>">
            <i class="fas fa-user text-blue-500 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">My Profile</span>
        </a>
        <a href="<?php echo BASE_URL; ?>player/events.php"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-blue-50 hover:pl-6 transition-all duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active bg-blue-50 border-l-4 border-blue-500 pl-5' : ''; ?>">
            <i class="fas fa-calendar-alt text-blue-500 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">Available Events</span>
        </a>
        <a href="<?php echo BASE_URL; ?>player/my-events.php"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-blue-50 hover:pl-6 transition-all duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'my-events.php' ? 'active bg-blue-50 border-l-4 border-blue-500 pl-5' : ''; ?>">
            <i class="fas fa-clipboard-check text-blue-500 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">My Registrations</span>
        </a>
        <a href="<?php echo BASE_URL; ?>player/teams.php"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-blue-50 hover:pl-6 transition-all duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'teams.php' || basename($_SERVER['PHP_SELF']) == 'team-details.php' || basename($_SERVER['PHP_SELF']) == 'team-edit.php' ? 'active bg-blue-50 border-l-4 border-blue-500 pl-5' : ''; ?>">
            <i class="fas fa-users text-blue-500 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">My Teams</span>
        </a>
        <a href="<?php echo BASE_URL; ?>player/results.php"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-blue-50 hover:pl-6 transition-all duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'results.php' ? 'active bg-blue-50 border-l-4 border-blue-500 pl-5' : ''; ?>">
            <i class="fas fa-trophy text-blue-500 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">Results</span>
        </a>
        <a href="<?php echo BASE_URL; ?>player/watch-live.php"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-blue-50 hover:pl-6 transition-all duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'watch-live.php' ? 'active bg-blue-50 border-l-4 border-blue-500 pl-5' : ''; ?>">
            <i class="fas fa-video text-blue-500 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">Watch Live</span>
        </a>

        <!-- Frontend Link -->
        <a href="<?php echo BASE_URL; ?>"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-blue-50 hover:pl-6 transition-all duration-300">
            <i class="fas fa-home text-blue-500 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">Landing Page</span>
        </a>

        <!-- Logout Link -->
        <a href="<?php echo BASE_URL; ?>logout.php"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-red-50 hover:pl-6 transition-all duration-300">
            <i class="fas fa-sign-out-alt text-red-600 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">Logout</span>
        </a>
    </div>

    <!-- Player Stats -->
    <div class="px-4 py-4 bg-gray-50">
        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3 flex items-center">
            <i class="fas fa-chart-line text-blue-500 mr-2"></i>
            <span>My Stats</span>
        </h4>
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white p-3 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-md transition-all duration-300 hover:border-blue-300">
                <div class="text-blue-600 text-lg font-bold">
                    <?php
                    $sql = "SELECT COUNT(*) as count FROM registrations WHERE user_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    echo $row['count'];
                    ?>
                </div>
                <div class="text-xs text-gray-500">Registrations</div>
            </div>
            <div class="bg-white p-3 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-md transition-all duration-300 hover:border-blue-300">
                <div class="text-blue-600 text-lg font-bold">
                    <?php
                    $sql = "SELECT COUNT(*) as count FROM results WHERE user_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    echo $row['count'];
                    ?>
                </div>
                <div class="text-xs text-gray-500">Results</div>
            </div>
        </div>
    </div>
</div>
