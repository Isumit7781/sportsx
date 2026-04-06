// Main JavaScript for Sports Management System

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuToggle = document.getElementById('menu-toggle');
    const menuClose = document.getElementById('menu-close');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileLinks = document.querySelectorAll('.mobile-link');

    // Set index for staggered animations
    mobileLinks.forEach((link, index) => {
        link.style.setProperty('--index', index);
    });

    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', function() {
            mobileMenu.classList.add('open');
            document.body.style.overflow = 'hidden'; // Prevent scrolling when menu is open
        });
    }

    if (menuClose && mobileMenu) {
        menuClose.addEventListener('click', function() {
            mobileMenu.classList.remove('open');
            document.body.style.overflow = ''; // Re-enable scrolling
        });
    }

    // Close mobile menu when clicking on a link
    mobileLinks.forEach(link => {
        link.addEventListener('click', function() {
            mobileMenu.classList.remove('open');
            document.body.style.overflow = ''; // Re-enable scrolling
        });
    });

    // Navbar scroll effect
    const navbar = document.getElementById('navbar');

    function handleScroll() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }

    window.addEventListener('scroll', handleScroll);

    // Initial check for navbar
    handleScroll();

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            if (this.getAttribute('href') !== '#') {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // Testimonial slider navigation
    const sliderDots = document.querySelectorAll('.flex.justify-center.mt-8 button');
    const testimonials = document.querySelectorAll('.testimonial-slider > div');

    if (sliderDots.length && testimonials.length) {
        sliderDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                // Update active dot
                sliderDots.forEach(d => d.classList.remove('bg-primary'));
                sliderDots.forEach(d => d.classList.add('bg-gray-300'));
                dot.classList.remove('bg-gray-300');
                dot.classList.add('bg-primary');

                // Scroll to the corresponding testimonial
                if (testimonials[index]) {
                    testimonials[index].scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                        inline: 'center'
                    });
                }
            });
        });

        // Set first dot as active by default
        sliderDots[0].classList.remove('bg-gray-300');
        sliderDots[0].classList.add('bg-primary');
    }

    // Counter animation
    const counters = document.querySelectorAll('.counter');

    // This function is no longer used as we animate counters directly in the IntersectionObserver callback
    function animateCounter(counter) {
        // Get the target value from the data-value attribute
        const targetNumber = parseInt(counter.getAttribute('data-value') || '0');
        const target = counter.innerText;

        // Check if the target includes a percentage sign
        const isPercentage = target.includes('%');
        // Check if the target includes a plus sign
        const hasPlus = target.includes('+');
        // Determine the suffix
        const suffix = (isPercentage ? '%' : '') + (hasPlus ? '+' : '');

        // Set a default value if parsing failed
        const finalNumber = isNaN(targetNumber) ? 100 : targetNumber;

        let count = 0;
        const duration = 2000; // 2 seconds
        const increment = Math.ceil(finalNumber / (duration / 30)); // Update every 30ms

        const timer = setInterval(() => {
            count += increment;
            if (count >= finalNumber) {
                // Set to the exact target number with appropriate suffix
                counter.innerText = finalNumber.toLocaleString() + suffix;
                clearInterval(timer);
            } else {
                counter.innerText = count.toLocaleString() + suffix;
            }
        }, 30);
    }

    // Animate counters when they come into view
    // Find all counters first
    const allCounters = document.querySelectorAll('.counter');
    // Then find their parent sections (unique)
    const counterSections = new Set();
    allCounters.forEach(counter => {
        const section = counter.closest('section');
        if (section) {
            counterSections.add(section);
        }
    });

    if (counterSections.size > 0) {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.2
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Find all counters within this section
                    const sectionCounters = entry.target.querySelectorAll('.counter');
                    if (sectionCounters.length > 0) {
                        // Animate only the counters in this section
                        sectionCounters.forEach(counter => {
                            // Get the target value from the data-value attribute
                            const targetNumber = parseInt(counter.getAttribute('data-value') || '0');
                            const target = counter.innerText;

                            // Check if the target includes a percentage sign
                            const isPercentage = target.includes('%');
                            // Check if the target includes a plus sign
                            const hasPlus = target.includes('+');
                            // Determine the suffix
                            const suffix = (isPercentage ? '%' : '') + (hasPlus ? '+' : '');

                            // Set a default value if parsing failed
                            const finalNumber = isNaN(targetNumber) ? 100 : targetNumber;

                            let count = 0;
                            const duration = 2000; // 2 seconds
                            const increment = Math.ceil(finalNumber / (duration / 30)); // Update every 30ms

                            const timer = setInterval(() => {
                                count += increment;
                                if (count >= finalNumber) {
                                    // Set to the exact target number with appropriate suffix
                                    counter.innerText = finalNumber.toLocaleString() + suffix;
                                    clearInterval(timer);
                                } else {
                                    counter.innerText = count.toLocaleString() + suffix;
                                }
                            }, 30);
                        });
                    }
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe all sections that contain counters
        counterSections.forEach(section => {
            observer.observe(section);
        });
    }

    // Add hover effect to buttons
    const buttons = document.querySelectorAll('a[href="#"].px-8.py-4');
    buttons.forEach(button => {
        button.classList.add('btn-hover-slide');
    });

    // Add shape animation to decorative elements
    const shapes = document.querySelectorAll('.absolute.bg-blue-100, .absolute.bg-green-100, .absolute.bg-primary\\/5, .absolute.bg-secondary\\/5');
    shapes.forEach(shape => {
        shape.classList.add('shape-animation');
    });

    // Add parallax effect to background elements
    window.addEventListener('scroll', function() {
        const scrollPosition = window.pageYOffset;

        document.querySelectorAll('.bg-gradient-to-br, .bg-gradient-to-r').forEach(element => {
            const speed = 0.5;
            element.style.backgroundPosition = `0px ${scrollPosition * speed}px`;
        });
    });

    // Add hover effects to feature cards
    const featureCards = document.querySelectorAll('.feature-card');
    featureCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            const icon = card.querySelector('.feature-icon');
            if (icon) {
                icon.classList.add('bg-primary', 'text-white');
                icon.classList.remove('bg-primary/10');
            }
        });

        card.addEventListener('mouseleave', () => {
            const icon = card.querySelector('.feature-icon');
            if (icon) {
                icon.classList.remove('bg-primary', 'text-white');
                icon.classList.add('bg-primary/10');
            }
        });
    });

    // Dashboard window hover effects
    const dashboardWindows = document.querySelectorAll('.dashboard-window');
    dashboardWindows.forEach(window => {
        window.addEventListener('mouseenter', () => {
            window.classList.add('transform', 'translate-y-[-5px]', 'shadow-xl');
        });

        window.addEventListener('mouseleave', () => {
            window.classList.remove('transform', 'translate-y-[-5px]', 'shadow-xl');
        });
    });

    // Initialize AOS (Animate on Scroll) if available
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });
    }
});
