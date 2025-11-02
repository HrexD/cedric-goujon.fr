// CV Modern JavaScript Enhancements

document.addEventListener('DOMContentLoaded', function() {
    // Initialize CV enhancements
    initScrollAnimations();
    initSkillInteractions();
    initPrintOptimization();
    initThemeToggle();
});

// Force PDF download
function downloadPDF(event, link) {
    event.preventDefault();
    
    // Créer un lien temporaire avec attribut download
    const tempLink = document.createElement('a');
    tempLink.href = link.href;
    tempLink.download = 'CV_Cedric_Goujon.pdf';
    tempLink.style.display = 'none';
    
    // Ajouter au DOM, cliquer et supprimer
    document.body.appendChild(tempLink);
    tempLink.click();
    document.body.removeChild(tempLink);
    
    // Afficher une notification
    const notification = createNotification();
    notification.show('Téléchargement du CV en cours...', 'success');
}

// Share CV function
function shareCV() {
    if (navigator.share) {
        navigator.share({
            title: 'CV de Cédric Goujon',
            text: 'Développeur Fullstack Junior - PHP/Symfony & JavaScript/React',
            url: window.location.href
        }).catch(console.error);
    } else {
        // Fallback: copier l'URL
        navigator.clipboard.writeText(window.location.href).then(() => {
            const notification = createNotification();
            notification.show('Lien copié dans le presse-papiers !', 'success');
        });
    }
}

function createNotification() {
    return {
        show: function(message, type = 'info') {
            // Créer une notification toast
            const toast = document.createElement('div');
            toast.className = `notification ${type}`;
            toast.textContent = message;
            
            // Styles inline pour la notification
            Object.assign(toast.style, {
                position: 'fixed',
                top: '20px',
                right: '20px',
                padding: '12px 20px',
                borderRadius: '8px',
                color: 'white',
                fontWeight: '500',
                zIndex: '10000',
                transform: 'translateX(100%)',
                transition: 'transform 0.3s ease',
                backgroundColor: type === 'success' ? '#10b981' : 
                               type === 'error' ? '#ef4444' : '#3b82f6'
            });
            
            document.body.appendChild(toast);
            
            // Animation d'entrée
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
            }, 100);
            
            // Suppression automatique
            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (toast.parentNode) {
                        document.body.removeChild(toast);
                    }
                }, 300);
            }, 3000);
        }
    };
}

// Smooth scroll animations for sections
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);

    // Observe all CV sections
    document.querySelectorAll('.cv-section').forEach((section, index) => {
        section.style.setProperty('--animation-delay', `${index * 0.1}s`);
        observer.observe(section);
    });
}

// Enhanced skill tag interactions
function initSkillInteractions() {
    document.querySelectorAll('.skill-tag').forEach(tag => {
        // Add click effect
        tag.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Create ripple effect
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            this.appendChild(ripple);
            
            // Remove ripple after animation
            setTimeout(() => ripple.remove(), 600);
            
            // Add temporary highlight
            this.classList.add('skill-clicked');
            setTimeout(() => this.classList.remove('skill-clicked'), 300);
        });
        
        // Enhanced hover effects
        tag.addEventListener('mouseenter', function() {
            this.style.setProperty('--hover-scale', '1.08');
        });
        
        tag.addEventListener('mouseleave', function() {
            this.style.setProperty('--hover-scale', '1');
        });
    });
}

// Optimized print functionality
function initPrintOptimization() {
    // Ne plus intercepter les clics sur btn-download
    // car maintenant c'est pour télécharger, pas imprimer
    
    // Listen for print events
    window.addEventListener('beforeprint', function() {
        document.body.classList.add('printing');
        
        // Expand all collapsible content
        document.querySelectorAll('.timeline-item').forEach(item => {
            item.style.pageBreakInside = 'avoid';
        });
    });
    
    window.addEventListener('afterprint', function() {
        document.body.classList.remove('printing');
    });
}

function optimizedPrint() {
    // Hide non-essential elements
    const elementsToHide = [
        'nav',
        '#theme-toggle',
        '.hero-actions',
        '.btn-download',
        '.btn-contact'
    ];
    
    elementsToHide.forEach(selector => {
        const elements = document.querySelectorAll(selector);
        elements.forEach(el => {
            el.style.setProperty('display', 'none', 'important');
        });
    });
    
    // Optimize layout for print
    document.body.classList.add('print-optimized');
    
    // Trigger print
    setTimeout(() => {
        window.print();
    }, 100);
    
    // Restore after print
    setTimeout(() => {
        elementsToHide.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(el => {
                el.style.removeProperty('display');
            });
        });
        document.body.classList.remove('print-optimized');
    }, 500);
}

// Theme toggle enhancement
function initThemeToggle() {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            // Add visual feedback
            this.style.transform = 'scale(0.9)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    }
}

// Utility functions
function createRippleEffect(element, event) {
    const ripple = document.createElement('div');
    ripple.classList.add('ripple');
    
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;
    
    ripple.style.cssText = `
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        width: ${size}px;
        height: ${size}px;
        left: ${x}px;
        top: ${y}px;
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    `;
    
    element.style.position = 'relative';
    element.style.overflow = 'hidden';
    element.appendChild(ripple);
    
    setTimeout(() => ripple.remove(), 600);
}

// Add CSS for ripple animation
const style = document.createElement('style');
style.textContent = `
    .cv-section {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.6s ease var(--animation-delay, 0s), 
                    transform 0.6s ease var(--animation-delay, 0s);
    }
    
    .cv-section.animate-in {
        opacity: 1;
        transform: translateY(0);
    }
    
    .skill-tag {
        position: relative;
        overflow: hidden;
        transform: scale(var(--hover-scale, 1));
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .skill-tag.skill-clicked {
        background: var(--primary-color);
        color: white;
        transform: scale(1.1);
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
    
    @media print {
        .cv-section {
            opacity: 1 !important;
            transform: none !important;
            animation: none !important;
            transition: none !important;
        }
        
        .print-optimized .cv-hero {
            background: var(--text-dark) !important;
            color: white !important;
        }
    }
    
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    }
`;

document.head.appendChild(style);