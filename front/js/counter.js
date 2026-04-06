// Simple counter animation script
window.onload = function() {
    console.log("Counter script loaded");

    // Function to animate a counter
    function animateCounter(element) {
        console.log("Animating counter:", element);

        // Get the target value from the data attribute or use a default
        var targetValue = 100;
        try {
            var dataValue = element.getAttribute('data-value');
            if (dataValue) {
                targetValue = parseInt(dataValue);
                if (isNaN(targetValue)) {
                    targetValue = 100;
                }
            }
        } catch (e) {
            console.error("Error parsing data-value:", e);
            targetValue = 100;
        }

        console.log("Target value:", targetValue);

        // Determine if the original text has a plus sign or percentage
        var originalText = element.textContent || '';
        var hasPlus = originalText.indexOf('+') !== -1;
        var hasPercent = originalText.indexOf('%') !== -1;

        // Create the suffix
        var suffix = (hasPlus ? '+' : '') + (hasPercent ? '%' : '');

        // Start from 0
        var currentValue = 0;

        // Calculate the increment
        var duration = 2000; // 2 seconds
        var framesPerSecond = 30;
        var totalFrames = duration / 1000 * framesPerSecond;
        var increment = targetValue / totalFrames;

        // Set initial value
        element.textContent = '0' + suffix;

        // Set up the animation interval
        var interval = setInterval(function() {
            currentValue += increment;

            // If we've reached or exceeded the target, set to the final value and stop
            if (currentValue >= targetValue) {
                element.textContent = targetValue.toLocaleString() + suffix;
                clearInterval(interval);
            } else {
                // Otherwise update with the current value
                element.textContent = Math.floor(currentValue).toLocaleString() + suffix;
            }
        }, 1000 / framesPerSecond);
    }

    // Find all counter elements
    var counters = document.querySelectorAll('.counter');
    console.log("Found counters:", counters.length);

    // Animate each counter
    for (var i = 0; i < counters.length; i++) {
        animateCounter(counters[i]);
    }
};
