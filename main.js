// =========================================
// FIGHTSMP - HAUPT-SCRIPT
// =========================================

document.addEventListener('DOMContentLoaded', () => {

    // 1. Mobile Navigation Toggle
    const mobileBtn = document.getElementById('mobile-menu-btn');
    const navLinks = document.getElementById('nav-links');

    if (mobileBtn && navLinks) {
        mobileBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            // Icon Wechsel (Burger zu X)
            if(navLinks.classList.contains('active')) {
                mobileBtn.innerHTML = '✕';
            } else {
                mobileBtn.innerHTML = '☰';
            }
        });
    }

    // 2. Scroll Animation (Fade-in & Slide-up)
    // Alle Elemente mit der Klasse 'fade-in-scroll' werden animiert, sobald sie ins Bild scrollen
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target); // Nur einmal animieren
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fade-in-scroll').forEach(el => {
        observer.observe(el);
    });

});

// 3. IP Kopieren Funktion (Global verfügbar)
window.copyIP = function() {
    navigator.clipboard.writeText("fightsmp.de").then(() => {
        alert("Server-IP 'fightsmp.de' erfolgreich kopiert! Wir sehen uns in der Arena.");
    }).catch(err => {
        console.error('Fehler beim Kopieren', err);
    });
};
