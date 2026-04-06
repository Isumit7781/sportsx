<?php
// Determine the relative path to the includes directory
$includePath = '';
$currentDir = dirname($_SERVER['PHP_SELF']);

// If we're in a subdirectory (like admin or player), adjust the path
if (strpos($currentDir, '/admin') !== false || strpos($currentDir, '/player') !== false) {
    $includePath = '../';
}

require_once $includePath . 'includes/config.php';
require_once $includePath . 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $includePath; ?>assets/css/style.css">
    <!-- Event Summary Toggle -->
    <script src="<?php echo $includePath; ?>js/event-summary-toggle.js"></script>

    <!-- Fix for relative paths in subdirectories -->
    <script>
        // Set a global JavaScript variable for the base URL
        var baseUrl = '<?php echo BASE_URL; ?>';

        // Tailwind Configuration
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#64748b',
                        success: '#10b981',
                        danger: '#ef4444',
                        warning: '#f59e0b',
                        info: '#3b82f6'
                    },
                    fontFamily: {
                        'sans': ['Poppins', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gray-50 font-sans transition-all duration-300">
    <!-- Page content -->
    <main class="container mx-auto px-4 py-6 animate-fadeIn">
        <!-- Mobile sidebar toggle button - only visible on mobile -->
        <?php if (isLoggedIn()): ?>
        <div class="fixed top-4 left-4 z-50 md:hidden">
            <button id="sidebar-toggle" class="bg-blue-600 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-300">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <?php endif; ?>
        <!-- Main content will go here -->
