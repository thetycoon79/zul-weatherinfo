/**
 * ZUL Weather Info - Frontend Scripts
 *
 * @package Zul\Weather
 */

(function() {
    'use strict';

    // Initialize weather widgets when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initWeatherWidgets();
    });

    function initWeatherWidgets() {
        var widgets = document.querySelectorAll('.zul-weather-widget');

        widgets.forEach(function(widget) {
            // Add loaded class for any future animations
            widget.classList.add('zul-weather-loaded');
        });
    }
})();
