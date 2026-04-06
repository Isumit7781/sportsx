    </main>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-gray-800 to-gray-900 text-white mt-12 shadow-lg">
        <div class="container mx-auto px-4 py-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Logo and description -->
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-2 mb-4 transform transition-transform duration-500 hover:scale-105">
                        <i class="fas fa-running text-blue-400 text-3xl"></i>
                        <h3 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-blue-300">Sports Management</h3>
                    </div>
                    <p class="text-gray-300 mb-4 leading-relaxed">A comprehensive platform for managing sports events, players, and results. Join us to participate in exciting sports events and track your performance.</p>
                    <div class="flex space-x-6 mt-6">
                        <a href="#" class="text-gray-300 hover:text-blue-400 transition-all duration-300 transform hover:scale-125">
                            <i class="fab fa-facebook-f text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-blue-400 transition-all duration-300 transform hover:scale-125">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-pink-400 transition-all duration-300 transform hover:scale-125">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-red-500 transition-all duration-300 transform hover:scale-125">
                            <i class="fab fa-youtube text-xl"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick links -->
                <div>
                    <h4 class="text-lg font-semibold mb-4 text-blue-400 border-b border-blue-400 pb-2 inline-block">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="<?php echo BASE_URL; ?>" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center hover:translate-x-2"><i class="fas fa-chevron-right text-blue-400 mr-2 text-xs"></i> Home</a></li>
                        <?php if (!isLoggedIn()): ?>
                            <li><a href="<?php echo BASE_URL; ?>register.php" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center hover:translate-x-2"><i class="fas fa-chevron-right text-blue-400 mr-2 text-xs"></i> Register</a></li>
                            <li><a href="<?php echo BASE_URL; ?>index.php" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center hover:translate-x-2"><i class="fas fa-chevron-right text-blue-400 mr-2 text-xs"></i> Login</a></li>
                        <?php else: ?>
                            <?php if (isAdmin()): ?>
                                <li><a href="<?php echo BASE_URL; ?>admin/" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center hover:translate-x-2"><i class="fas fa-chevron-right text-blue-400 mr-2 text-xs"></i> Admin Dashboard</a></li>
                                <li><a href="<?php echo BASE_URL; ?>admin/events.php" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center hover:translate-x-2"><i class="fas fa-chevron-right text-blue-400 mr-2 text-xs"></i> Manage Events</a></li>
                            <?php elseif (isPlayer()): ?>
                                <li><a href="<?php echo BASE_URL; ?>player/" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center hover:translate-x-2"><i class="fas fa-chevron-right text-blue-400 mr-2 text-xs"></i> Player Dashboard</a></li>
                                <li><a href="<?php echo BASE_URL; ?>player/events.php" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center hover:translate-x-2"><i class="fas fa-chevron-right text-blue-400 mr-2 text-xs"></i> Available Events</a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Contact info -->
                <div>
                    <h4 class="text-lg font-semibold mb-4 text-blue-400 border-b border-blue-400 pb-2 inline-block">Contact Us</h4>
                    <ul class="space-y-3">
                        <li class="flex items-start space-x-3 hover:translate-x-2 transition-all duration-300 group">
                            <i class="fas fa-map-marker-alt mt-1 text-blue-400 group-hover:scale-125 transition-transform duration-300"></i>
                            <span class="text-gray-300">123 Sports Avenue, Athletic City, AC 12345</span>
                        </li>
                        <li class="flex items-center space-x-3 hover:translate-x-2 transition-all duration-300 group">
                            <i class="fas fa-envelope text-blue-400 group-hover:scale-125 transition-transform duration-300"></i>
                            <span class="text-gray-300">info@sportsmanagement.com</span>
                        </li>
                        <li class="flex items-center space-x-3 hover:translate-x-2 transition-all duration-300 group">
                            <i class="fas fa-phone text-blue-400 group-hover:scale-125 transition-transform duration-300"></i>
                            <span class="text-gray-300">+1 (123) 456-7890</span>
                        </li>
                        <li class="flex items-center space-x-3 hover:translate-x-2 transition-all duration-300 group">
                            <i class="fas fa-clock text-blue-400 group-hover:scale-125 transition-transform duration-300"></i>
                            <span class="text-gray-300">Mon-Fri: 9:00 AM - 5:00 PM</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-6 text-center">
                <p class="text-gray-400">&copy; <?php echo date('Y'); ?> <span class="text-blue-400 font-medium">Sports Management System</span>. All rights reserved.</p>
                <div class="mt-4 text-sm text-gray-500">Made with <i class="fas fa-heart text-red-500 animate-pulse"></i> by Sports Management Team</div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>

    <!-- Additional JS for UI enhancements -->
    <script>
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const dropdowns = document.querySelectorAll('.relative > div:not(.hidden)');
            dropdowns.forEach(dropdown => {
                const parent = dropdown.parentElement;
                if (!parent.contains(event.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        });

        // Add scroll to top button functionality
        const scrollToTopBtn = document.createElement('button');
        scrollToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
        scrollToTopBtn.className = 'fixed bottom-6 right-6 bg-blue-600 text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center opacity-0 transition-all duration-300 hover:bg-blue-700 focus:outline-none';
        scrollToTopBtn.style.zIndex = '999';
        document.body.appendChild(scrollToTopBtn);

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('opacity-100');
                scrollToTopBtn.classList.remove('opacity-0');
            } else {
                scrollToTopBtn.classList.add('opacity-0');
                scrollToTopBtn.classList.remove('opacity-100');
            }
        });

        scrollToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Mobile sidebar functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Get sidebar elements
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const closeSidebar = document.getElementById('close-sidebar');

            // Add overlay for mobile sidebar
            const overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-30 hidden transition-opacity duration-300';
            overlay.id = 'sidebar-overlay';
            document.body.appendChild(overlay);

            // Toggle sidebar on mobile
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.remove('translate-x-[-100%]');
                    sidebar.classList.add('translate-x-0');
                    overlay.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                });
            }

            // Close sidebar when clicking the close button
            if (closeSidebar) {
                closeSidebar.addEventListener('click', function() {
                    sidebar.classList.remove('translate-x-0');
                    sidebar.classList.add('translate-x-[-100%]');
                    overlay.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                });
            }

            // Close sidebar when clicking the overlay
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('translate-x-[-100%]');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            });

            // Close sidebar on window resize (if desktop)
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) { // md breakpoint
                    overlay.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            });
        });
    </script>
</body>
</html>
