/**
 * YGB Category Showcase JavaScript
 * Version: 3.4.0
 * CORRECCIÓN: Eliminada dependencia de jQuery - Vanilla JS moderno
 * Mejoras de accesibilidad, rendimiento y Clipboard API moderna
 */

(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        
        // Mejorar accesibilidad: añadir aria-labels si no existen
        document.querySelectorAll('.ygb-link').forEach(function(link) {
            if (!link.getAttribute('aria-label')) {
                var nameElement = link.querySelector('.ygb-name');
                if (nameElement && nameElement.textContent) {
                    link.setAttribute('aria-label', 'Ver productos en ' + nameElement.textContent.trim());
                }
            }
        });
        
        // Lazy loading con Intersection Observer para mejor rendimiento
        if ('IntersectionObserver' in window) {
            var imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        var src = img.getAttribute('data-src');
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
            
            document.querySelectorAll('.ygb-image img').forEach(function(img) {
                var currentSrc = img.getAttribute('src');
                
                if (currentSrc && currentSrc.indexOf('data:image/svg+xml') === -1) {
                    img.setAttribute('data-src', currentSrc);
                    img.removeAttribute('src');
                    img.setAttribute('src', 'data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22300%22%20height%3D%22300%22%20viewBox%3D%220%200%20300%20300%22%3E%3Crect%20width%3D%22300%22%20height%3D%22300%22%20fill%3D%22%23f0f0f0%22%2F%3E%3C%2Fsvg%3E');
                    imageObserver.observe(img);
                }
            });
        } else {
            // Fallback para navegadores sin Intersection Observer
            document.querySelectorAll('.ygb-image img').forEach(function(img) {
                img.setAttribute('loading', 'lazy');
            });
        }
        
        // Prevenir clics duplicados - por elemento, no global
        document.querySelectorAll('.ygb-link').forEach(function(link) {
            var clickTimeout = false;
            
            link.addEventListener('click', function(e) {
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
        
        // Debounce para eventos de resize
        var resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                document.querySelectorAll('.ygb-card').forEach(function(card) {
                    card.style.height = 'auto';
                });
            }, 250);
        });
        
    });
    
})();