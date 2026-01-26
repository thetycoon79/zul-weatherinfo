/**
 * ZUL Weather Info - Admin Scripts
 *
 * @package Zul\Weather
 */

(function($) {
    'use strict';

    // Document ready
    $(function() {
        // Copy shortcode to clipboard
        $('.zul-weather-table code, #location-shortcode').on('click', function() {
            var $this = $(this);
            var text = $this.text();

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    showCopiedNotice($this);
                });
            } else {
                // Fallback for older browsers
                var $temp = $('<textarea>');
                $('body').append($temp);
                $temp.val(text).select();
                document.execCommand('copy');
                $temp.remove();
                showCopiedNotice($this);
            }
        });

        function showCopiedNotice($element) {
            var originalText = $element.text();
            $element.text('Copied!');
            setTimeout(function() {
                $element.text(originalText);
            }, 1500);
        }

        // Add title attribute to shortcode codes
        $('.zul-weather-table code, #location-shortcode').attr('title', 'Click to copy');
        $('.zul-weather-table code, #location-shortcode').css('cursor', 'pointer');
    });

})(jQuery);
