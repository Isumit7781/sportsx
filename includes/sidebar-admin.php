<div id="sidebar" class="bg-white rounded-xl shadow-md overflow-hidden mb-6 border-t-4 border-blue-500 transform transition-all duration-300 hover:shadow-lg md:translate-x-0 translate-x-[-100%] fixed md:relative top-0 left-0 h-full md:h-auto z-40 w-64 md:w-auto">
    <!-- Close button for mobile -->
    <div class="absolute top-2 right-2 md:hidden">
        <button id="close-sidebar" class="text-gray-500 hover:text-gray-700 focus:outline-none">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-4 py-4">
        <h3 class="text-white text-lg font-semibold flex items-center">
            <i class="fas fa-user-shield mr-2"></i>
            <span>Admin Panel</span>
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
        <a href="<?php echo BASE_URL; ?>admin/index.php"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-blue-50 hover:pl-6 transition-all duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active bg-blue-50 border-l-4 border-blue-600 pl-5' : ''; ?>">
            <i class="fas fa-tachometer-alt text-blue-600 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">Dashboard</span>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/players.php"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-blue-50 hover:pl-6 transition-all duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'players.php' || basename($_SERVER['PHP_SELF']) == 'player-add.php' || basename($_SERVER['PHP_SELF']) == 'player-edit.php' ? 'active bg-blue-50 border-l-4 border-blue-600 pl-5' : ''; ?>">
            <i class="fas fa-users text-blue-600 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">Players</span>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/events.php"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-blue-50 hover:pl-6 transition-all duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' || basename($_SERVER['PHP_SELF']) == 'event-add.php' || basename($_SERVER['PHP_SELF']) == 'event-edit.php' ? 'active bg-blue-50 border-l-4 border-blue-600 pl-5' : ''; ?>">
            <i class="fas fa-calendar-alt text-blue-600 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">Events</span>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/registrations.php"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-blue-50 hover:pl-6 transition-all duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'registrations.php' ? 'active bg-blue-50 border-l-4 border-blue-600 pl-5' : ''; ?>">
            <i class="fas fa-clipboard-list text-blue-600 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">Registrations</span>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/results.php"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-blue-50 hover:pl-6 transition-all duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'results.php' || basename($_SERVER['PHP_SELF']) == 'result-add.php' ? 'active bg-blue-50 border-l-4 border-blue-600 pl-5' : ''; ?>">
            <i class="fas fa-trophy text-blue-600 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">Results</span>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/live-streams.php"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-blue-50 hover:pl-6 transition-all duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'live-streams.php' ? 'active bg-blue-50 border-l-4 border-blue-600 pl-5' : ''; ?>">
            <i class="fas fa-video text-blue-600 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">Live Streams</span>
        </a>
        <!-- <a href="<?php echo BASE_URL; ?>admin/user-verifications.php"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-blue-50 hover:pl-6 transition-all duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'user-verifications.php' ? 'active bg-blue-50 border-l-4 border-blue-600 pl-5' : ''; ?>">
            <i class="fas fa-user-check text-blue-600 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">User Verifications</span>
        </a> -->

        <!-- User Profile Link -->
        <a href="<?php echo BASE_URL; ?>admin/profile.php"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-blue-50 hover:pl-6 transition-all duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active bg-blue-50 border-l-4 border-blue-600 pl-5' : ''; ?>">
            <i class="fas fa-user-cog text-blue-600 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">My Profile</span>
        </a>

        <!-- Frontend Link -->
        <a href="<?php echo BASE_URL; ?>"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-blue-50 hover:pl-6 transition-all duration-300">
            <i class="fas fa-home text-blue-600 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">Landing Page</span>
        </a>

        <!-- Logout Link -->
        <a href="<?php echo BASE_URL; ?>logout.php"
           class="sidebar-link flex items-center px-4 py-3 hover:bg-red-50 hover:pl-6 transition-all duration-300">
            <i class="fas fa-sign-out-alt text-red-600 w-6"></i>
            <span class="ml-2 text-gray-700 font-medium">Logout</span>
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="px-4 py-4 bg-gray-50">
        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3 flex items-center">
            <i class="fas fa-chart-pie text-blue-500 mr-2"></i>
            <span>Quick Stats</span>
        </h4>
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white p-3 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-md transition-all duration-300 hover:border-blue-300">
                <div class="text-blue-600 text-lg font-bold">
                    <?php
                    $sql = "SELECT COUNT(*) as count FROM users WHERE role = 'player'";
                    $result = $conn->query($sql);
                    $row = $result->fetch_assoc();
                    echo $row['count'];
                    ?>
                </div>
                <div class="text-xs text-gray-500">Players</div>
            </div>
            <div class="bg-white p-3 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-md transition-all duration-300 hover:border-blue-300">
                <div class="text-blue-600 text-lg font-bold">
                    <?php
                    $sql = "SELECT COUNT(*) as count FROM events WHERE event_date >= CURDATE()";
                    $result = $conn->query($sql);
                    $row = $result->fetch_assoc();
                    echo $row['count'];
                    ?>
                </div>
                <div class="text-xs text-gray-500">Upcoming Events</div>
            </div>
        </div>
    </div>
</div>
