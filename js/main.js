// Sports Management System - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS (Animate On Scroll)
    AOS.init({
        duration: 800,
        easing: 'ease',
        once: true,
        offset: 100
    });
    
    // Mobile Navigation Toggle
    const mobileNavToggle = document.getElementById('mobile-nav-toggle');
    const mobileNav = document.getElementById('mobile-nav');
    
    if (mobileNavToggle && mobileNav) {
        mobileNavToggle.addEventListener('click', function() {
            mobileNav.classList.toggle('show');
            document.body.classList.toggle('nav-open');
        });
    }
    
    // Dashboard Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const dashboardSidebar = document.querySelector('.dashboard-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (sidebarToggle && dashboardSidebar) {
        sidebarToggle.addEventListener('click', function() {
            dashboardSidebar.classList.toggle('show');
            if (overlay) {
                overlay.classList.toggle('show');
            }
            document.body.classList.toggle('sidebar-open');
        });
        
        if (overlay) {
            overlay.addEventListener('click', function() {
                dashboardSidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            });
        }
    }
    
    // Close sidebar on window resize (if in mobile view)
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992 && dashboardSidebar) {
            dashboardSidebar.classList.remove('show');
            if (overlay) {
                overlay.classList.remove('show');
            }
            document.body.classList.remove('sidebar-open');
        }
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
                
                // Close mobile nav if open
                if (mobileNav && mobileNav.classList.contains('show')) {
                    mobileNav.classList.remove('show');
                    document.body.classList.remove('nav-open');
                }
            }
        });
    });
    
    // Testimonial Carousel
    const testimonialCarousel = document.querySelector('.testimonial-carousel');
    if (testimonialCarousel) {
        new Swiper(testimonialCarousel, {
            slidesPerView: 1,
            spaceBetween: 30,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                640: {
                    slidesPerView: 1,
                },
                768: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 3,
                },
            },
            autoplay: {
                delay: 5000,
            },
        });
    }
    
    // Events Carousel
    const eventsCarousel = document.querySelector('.events-carousel');
    if (eventsCarousel) {
        new Swiper(eventsCarousel, {
            slidesPerView: 1,
            spaceBetween: 30,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                640: {
                    slidesPerView: 1,
                },
                768: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 3,
                },
            },
        });
    }
    
    // Counter Animation
    const counters = document.querySelectorAll('.counter');
    if (counters.length > 0) {
        const counterObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.getAttribute('data-target'));
                    let count = 0;
                    const updateCounter = () => {
                        const increment = target / 100;
                        if (count < target) {
                            count += increment;
                            counter.innerText = Math.ceil(count);
                            setTimeout(updateCounter, 10);
                        } else {
                            counter.innerText = target;
                        }
                    };
                    updateCounter();
                    observer.unobserve(counter);
                }
            });
        }, { threshold: 0.5 });
        
        counters.forEach(counter => {
            counterObserver.observe(counter);
        });
    }
    
    // Form Validation
    const forms = document.querySelectorAll('.needs-validation');
    if (forms.length > 0) {
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }
    
    // Password Toggle
    const passwordToggles = document.querySelectorAll('.password-toggle');
    if (passwordToggles.length > 0) {
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const passwordField = document.querySelector(this.getAttribute('data-target'));
                if (passwordField) {
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                }
            });
        });
    }
    
    // Tooltip Initialization
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltips.length > 0) {
        Array.from(tooltips).forEach(tooltipTriggerEl => {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Popover Initialization
    const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
    if (popovers.length > 0) {
        Array.from(popovers).forEach(popoverTriggerEl => {
            new bootstrap.Popover(popoverTriggerEl);
        });
    }
    
    // Back to Top Button
    const backToTopButton = document.getElementById('back-to-top');
    if (backToTopButton) {
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });
        
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Animated Progress Bars
    const progressBars = document.querySelectorAll('.progress-bar');
    if (progressBars.length > 0) {
        const progressObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const progressBar = entry.target;
                    const width = progressBar.getAttribute('aria-valuenow') + '%';
                    progressBar.style.width = width;
                    observer.unobserve(progressBar);
                }
            });
        }, { threshold: 0.5 });
        
        progressBars.forEach(progressBar => {
            progressObserver.observe(progressBar);
        });
    }
    
    // Countdown Timer
    const countdownElements = document.querySelectorAll('.countdown');
    if (countdownElements.length > 0) {
        countdownElements.forEach(countdown => {
            const targetDate = new Date(countdown.getAttribute('data-target-date')).getTime();
            
            const updateCountdown = () => {
                const now = new Date().getTime();
                const distance = targetDate - now;
                
                if (distance < 0) {
                    countdown.innerHTML = 'Event has started!';
                    return;
                }
                
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                countdown.innerHTML = `
                    <div class="countdown-item">
                        <span class="countdown-value">${days}</span>
                        <span class="countdown-label">Days</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-value">${hours}</span>
                        <span class="countdown-label">Hours</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-value">${minutes}</span>
                        <span class="countdown-label">Minutes</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-value">${seconds}</span>
                        <span class="countdown-label">Seconds</span>
                    </div>
                `;
            };
            
            updateCountdown();
            setInterval(updateCountdown, 1000);
        });
    }
    
    // Image Lightbox
    const lightboxImages = document.querySelectorAll('.lightbox-image');
    if (lightboxImages.length > 0) {
        lightboxImages.forEach(image => {
            image.addEventListener('click', function() {
                const src = this.getAttribute('src');
                const alt = this.getAttribute('alt') || 'Image';
                
                const lightbox = document.createElement('div');
                lightbox.className = 'lightbox';
                lightbox.innerHTML = `
                    <div class="lightbox-content">
                        <img src="${src}" alt="${alt}">
                        <span class="lightbox-close">&times;</span>
                        <div class="lightbox-caption">${alt}</div>
                    </div>
                `;
                
                document.body.appendChild(lightbox);
                document.body.classList.add('lightbox-open');
                
                const close = lightbox.querySelector('.lightbox-close');
                close.addEventListener('click', function() {
                    document.body.removeChild(lightbox);
                    document.body.classList.remove('lightbox-open');
                });
                
                lightbox.addEventListener('click', function(e) {
                    if (e.target === lightbox) {
                        document.body.removeChild(lightbox);
                        document.body.classList.remove('lightbox-open');
                    }
                });
            });
        });
    }
    
    // Animated Numbers on Scroll
    const animatedNumbers = document.querySelectorAll('.animated-number');
    if (animatedNumbers.length > 0) {
        const numberObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const numberElement = entry.target;
                    const finalNumber = parseInt(numberElement.getAttribute('data-number'));
                    let currentNumber = 0;
                    const duration = 2000; // 2 seconds
                    const interval = Math.floor(duration / finalNumber);
                    
                    const timer = setInterval(() => {
                        currentNumber += 1;
                        numberElement.textContent = currentNumber;
                        
                        if (currentNumber >= finalNumber) {
                            clearInterval(timer);
                        }
                    }, interval);
                    
                    observer.unobserve(numberElement);
                }
            });
        }, { threshold: 0.5 });
        
        animatedNumbers.forEach(number => {
            numberObserver.observe(number);
        });
    }
});
