// Enhanced JavaScript for Sports Management System

document.addEventListener('DOMContentLoaded', function() {
    // Add staggered fade-in animation to main content and its children
    const mainContent = document.querySelector('main');
    if (mainContent) {
        mainContent.classList.add('animate-fadeIn');

        // Add staggered animations to cards and other elements
        const cards = mainContent.querySelectorAll('.card, .stats-card, .custom-card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('animate-scaleIn');
            }, 100 * index);
        });

        // Add slide-in animations to sidebar elements
        const sidebarLinks = document.querySelectorAll('.sidebar-link');
        sidebarLinks.forEach((link, index) => {
            setTimeout(() => {
                link.classList.add('animate-slideInLeft');
            }, 50 * index);
        });
    }

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Password toggle visibility with enhanced UI
    const togglePassword = document.querySelector('#togglePassword');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const password = document.querySelector('#password');
            const confirmPassword = document.querySelector('#confirm_password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';

            password.setAttribute('type', type);
            if (confirmPassword) {
                confirmPassword.setAttribute('type', type);
            }

            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');

            // Add a nice transition effect
            this.classList.add('text-blue-500');
            setTimeout(() => {
                this.classList.remove('text-blue-500');
            }, 300);
        });
    }

    // Enhanced form validation with better user feedback
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        // Add input event listeners for real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.checkValidity()) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                } else {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            });
        });

        // Form submission validation
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();

                // Scroll to the first invalid element
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus({ preventScroll: true });
                }
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Event registration with modern confirmation dialog
    const registerButtons = document.querySelectorAll('.register-event-btn');
    registerButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            // Try to find the event title in different card structures
            let eventTitle = "this event";
            const card = this.closest('.card');

            if (card) {
                const titleElement = card.querySelector('.card-title');
                if (titleElement) {
                    eventTitle = titleElement.textContent;
                }
            }

            // Create a custom confirmation dialog
            const confirmDialog = document.createElement('div');
            confirmDialog.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            confirmDialog.innerHTML = `
                <div class="bg-white rounded-lg p-6 max-w-md mx-auto shadow-xl transform transition-all">
                    <h3 class="text-lg font-bold mb-4">Confirm Registration</h3>
                    <p class="mb-6">Are you sure you want to register for <span class="font-semibold">${eventTitle}</span>?</p>
                    <div class="flex justify-end space-x-3">
                        <button class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition-colors" id="cancelBtn">Cancel</button>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors" id="confirmBtn">Register</button>
                    </div>
                </div>
            `;

            document.body.appendChild(confirmDialog);
            document.body.style.overflow = 'hidden'; // Prevent scrolling

            // Handle dialog buttons
            document.getElementById('cancelBtn').addEventListener('click', function() {
                document.body.removeChild(confirmDialog);
                document.body.style.overflow = '';
            });

            document.getElementById('confirmBtn').addEventListener('click', function() {
                window.location.href = button.getAttribute('href');
            });
        });
    });

    // Delete confirmation with enhanced UI
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            // Create a custom confirmation dialog
            const confirmDialog = document.createElement('div');
            confirmDialog.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            confirmDialog.innerHTML = `
                <div class="bg-white rounded-lg p-6 max-w-md mx-auto shadow-xl">
                    <div class="text-red-500 text-center mb-4"><i class="fas fa-exclamation-triangle text-4xl"></i></div>
                    <h3 class="text-lg font-bold mb-2 text-center">Confirm Deletion</h3>
                    <p class="mb-6 text-center">Are you sure you want to delete this item? This action cannot be undone.</p>
                    <div class="flex justify-center space-x-4">
                        <button class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition-colors" id="cancelDeleteBtn">Cancel</button>
                        <button class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors" id="confirmDeleteBtn">Delete</button>
                    </div>
                </div>
            `;

            document.body.appendChild(confirmDialog);
            document.body.style.overflow = 'hidden';

            // Handle dialog buttons
            document.getElementById('cancelDeleteBtn').addEventListener('click', function() {
                document.body.removeChild(confirmDialog);
                document.body.style.overflow = '';
            });

            document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                window.location.href = button.getAttribute('href');
            });
        });
    });

    // Enhanced alerts with animations
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        // Add slide-in animation
        alert.style.animation = 'slideIn 0.3s ease-out forwards';

        // Auto-hide after 5 seconds with fade-out
        setTimeout(() => {
            alert.style.animation = 'fadeOut 0.5s ease-in forwards';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 500);
        }, 5000);

        // Add close button functionality
        const closeBtn = alert.querySelector('.close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                alert.style.animation = 'fadeOut 0.5s ease-in forwards';
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 500);
            });
        }
    });

    // Date picker enhancement
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        // Add custom styling
        input.classList.add('custom-date-input');

        // Set default value if empty
        if (input.value === '') {
            const today = new Date().toISOString().split('T')[0];
            input.value = today;
        }

        // Add calendar icon
        const wrapper = document.createElement('div');
        wrapper.className = 'relative';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        const icon = document.createElement('i');
        icon.className = 'fas fa-calendar absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none';
        wrapper.appendChild(icon);
    });

    // Enhanced profile image preview with drag and drop
    const profileImageInput = document.querySelector('#profile_image');
    const profileImagePreview = document.querySelector('#profile_image_preview');
    const imageDropArea = document.querySelector('#image-drop-area');

    if (profileImageInput && profileImagePreview) {
        // Handle file selection
        profileImageInput.addEventListener('change', function() {
            handleProfileImage(this.files[0]);
        });

        // Add drag and drop functionality if drop area exists
        if (imageDropArea) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                imageDropArea.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                imageDropArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                imageDropArea.addEventListener(eventName, unhighlight, false);
            });

            function highlight() {
                imageDropArea.classList.add('border-blue-500', 'bg-blue-50');
            }

            function unhighlight() {
                imageDropArea.classList.remove('border-blue-500', 'bg-blue-50');
            }

            imageDropArea.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const file = dt.files[0];
                handleProfileImage(file);
            }
        }

        function handleProfileImage(file) {
            if (file) {
                // Check if file is an image
                if (!file.type.match('image.*')) {
                    alert('Please select an image file (jpg, png, gif)');
                    return;
                }

                // Check file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size should be less than 2MB');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImagePreview.src = e.target.result;
                    profileImagePreview.style.display = 'block';

                    // Add animation
                    profileImagePreview.classList.add('scale-in');
                    setTimeout(() => {
                        profileImagePreview.classList.remove('scale-in');
                    }, 500);
                }
                reader.readAsDataURL(file);
            }
        }
    }

    // Enhanced event search with filtering options
    const eventSearchInput = document.querySelector('#eventSearch');
    const eventFilterSelect = document.querySelector('#eventFilter');

    if (eventSearchInput) {
        // Debounce function to limit search frequency
        function debounce(func, wait) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        // Search function
        const performSearch = debounce(function() {
            const searchValue = eventSearchInput.value.toLowerCase();
            const filterValue = eventFilterSelect ? eventFilterSelect.value : 'all';
            const eventCards = document.querySelectorAll('.event-card');

            eventCards.forEach(card => {
                const cardParent = card.closest('.col') || card.parentElement;
                const title = card.querySelector('.card-title').textContent.toLowerCase();
                const location = card.querySelector('.event-location').textContent.toLowerCase();
                const date = card.querySelector('.event-date').textContent.toLowerCase();
                const category = card.dataset.category ? card.dataset.category.toLowerCase() : '';

                // Check if card matches search text
                const matchesSearch = title.includes(searchValue) ||
                                     location.includes(searchValue) ||
                                     date.includes(searchValue);

                // Check if card matches filter
                const matchesFilter = filterValue === 'all' || category === filterValue;

                // Show/hide based on both conditions
                if (matchesSearch && matchesFilter) {
                    cardParent.style.display = '';
                    // Add a subtle animation
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transition = 'opacity 0.3s ease-in-out';
                    }, 50);
                } else {
                    cardParent.style.display = 'none';
                }
            });

            // Show message if no results
            const noResultsMsg = document.querySelector('#noResultsMessage');
            const visibleCards = document.querySelectorAll('.event-card:not([style*="display: none"])');

            if (noResultsMsg) {
                if (visibleCards.length === 0) {
                    noResultsMsg.style.display = 'block';
                } else {
                    noResultsMsg.style.display = 'none';
                }
            }
        }, 300);

        // Add event listeners
        eventSearchInput.addEventListener('keyup', performSearch);
        if (eventFilterSelect) {
            eventFilterSelect.addEventListener('change', performSearch);
        }
    }

    // Add smooth scrolling to all internal links
    document.querySelectorAll('a[href^="#"]:not([href="#"])').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                // Update URL hash without jumping
                history.pushState(null, null, this.getAttribute('href'));
            }
        });
    });

    // Add animations to stats cards
    const statsCards = document.querySelectorAll('.stats-card');
    if (statsCards.length > 0) {
        // Create intersection observer for stats cards
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const card = entry.target;
                    const numberElement = card.querySelector('h2');
                    if (numberElement) {
                        const finalNumber = parseInt(numberElement.textContent, 10);
                        if (!isNaN(finalNumber)) {
                            // Start from 0 and animate to the final number
                            let currentNumber = 0;
                            numberElement.textContent = '0';

                            const interval = setInterval(() => {
                                currentNumber += Math.ceil(finalNumber / 20);
                                if (currentNumber >= finalNumber) {
                                    currentNumber = finalNumber;
                                    clearInterval(interval);
                                }
                                numberElement.textContent = currentNumber;
                            }, 50);
                        }
                    }

                    // Add a bounce effect to the icon
                    const icon = card.querySelector('.stats-icon i');
                    if (icon) {
                        icon.classList.add('animate-bounce');
                    }

                    // Stop observing after animation
                    statsObserver.unobserve(card);
                }
            });
        }, { threshold: 0.2 });

        // Observe each stats card
        statsCards.forEach(card => {
            statsObserver.observe(card);
        });
    }

    // Add CSS animations for keyframes
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        @keyframes scale-in {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        .scale-in {
            animation: scale-in 0.3s ease-out forwards;
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        .shimmer {
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 20%, rgba(255,255,255,0) 40%);
            background-size: 1000px 100%;
            animation: shimmer 2s infinite linear;
        }

        .custom-date-input {
            padding-right: 30px;
        }

        /* Hover effects for cards */
        .card:hover .card-title, .stats-card:hover h5 {
            color: #3b82f6;
            transition: color 0.3s ease;
        }

        /* Improved focus styles */
        button:focus, a:focus, input:focus, select:focus, textarea:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
    `;
    document.head.appendChild(style);
});
