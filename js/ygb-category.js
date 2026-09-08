/**
 * YGB Category Showcase JavaScript
 * Version: 3.2.0
 * Mejoras de accesibilidad, rendimiento y Clipboard API moderna
 * CORREGIDO: Timeout por elemento, no global
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Mejorar accesibilidad: añadir aria-labels si no existen
        $('.ygb-link').each(function() {
            if (!$(this).attr('aria-label')) {
                var name = $(this).find('.ygb-name').text();
                if (name) {
                    $(this).attr('aria-label', 'Ver productos en ' + name);
                }
            }
        });
        
        // Lazy loading con Intersection Observer para mejor rendimiento
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        const src = img.getAttribute('data-src');
                        if (src) {
                            img.src = src;
                            img.removeAttribute('data-src');
                        }
                        observer.unobserve(img);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });
            
            $('.ygb-image img').each(function() {
                const img = $(this);
                const currentSrc = img.attr('src');
                
                if (currentSrc && !currentSrc.includes('data:image/svg+xml')) {
                    img.attr('data-src', currentSrc);
                    img.removeAttr('src');
                    img.attr('src', 'data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22300%22%20height%3D%22300%22%20viewBox%3D%220%200%20300%20300%22%3E%3Crect%20width%3D%22300%22%20height%3D%22300%22%20fill%3D%22%23f0f0f0%22%2F%3E%3C%2Fsvg%3E');
                    imageObserver.observe(img[0]);
                }
            });
        } else {
            // Fallback para navegadores sin Intersection Observer
            $('.ygb-image img').each(function() {
                $(this).attr('loading', 'lazy');
            });
        }
        
        // Prevenir clics duplicados - CORREGIDO: por elemento, no global
        $('.ygb-link').each(function() {
            var $link = $(this);
            var clickTimeout = false;
            
            $link.on('click', function(e) {
                if (clickTimeout) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }
                clickTimeout = true;
                setTimeout(function() {
                    clickTimeout = false;
                }, 300);
            });
        });
        
        // Debounce para eventos de scroll/resize
        var resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                $('.ygb-card').each(function() {
                    $(this).css('height', 'auto');
                });
            }, 250);
        });
        
    });
    
})(jQuery);