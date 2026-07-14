// =========================================
// FIGHTSMP - HAUPT-SCRIPT
// =========================================

// Respektiert die Nutzer-Einstellung "Bewegungen reduzieren"
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const isTouchDevice = window.matchMedia('(hover: none)').matches;

document.addEventListener('DOMContentLoaded', () => {

    // 0. Sanfter Seiteneinstieg (kein hartes Aufpoppen)
    document.body.style.opacity = '0';
    requestAnimationFrame(() => {
        document.body.style.transition = 'opacity 0.4s ease';
        document.body.style.opacity = '1';
    });

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

    // 3. Sanfte Seitenübergänge bei internen Links (statt hartem Sprung)
    document.querySelectorAll('a[href]').forEach(link => {
        const href = link.getAttribute('href');
        const isInternal = href && !href.startsWith('http') && !href.startsWith('#') && !href.startsWith('mailto:');
        if (isInternal && link.target !== '_blank') {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                document.body.style.transition = 'opacity 0.25s ease';
                document.body.style.opacity = '0';
                setTimeout(() => { window.location.href = href; }, 220);
            });
        }
    });

    initEmberCanvas();
    initCursorGlow();
});

// =========================================
// 4. SIGNATURE-EFFEKT: GLUT-FUNKEN (Ember-Canvas)
// Passend zum Arena/PvP-Thema steigen dezent Funken auf.
// =========================================
function initEmberCanvas() {
    if (prefersReducedMotion) return;

    const canvas = document.createElement('canvas');
    canvas.id = 'ember-canvas';
    document.body.prepend(canvas);
    const ctx = canvas.getContext('2d');

    let width, height, embers;

    function resize() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    }

    function createEmber() {
        return {
            x: Math.random() * width,
            y: height + 20,
            radius: Math.random() * 2 + 0.5,
            speed: Math.random() * 0.8 + 0.3,
            drift: (Math.random() - 0.5) * 0.6,
            life: 1,
            decay: Math.random() * 0.004 + 0.002
        };
    }

    function init() {
        resize();
        const count = isTouchDevice ? 18 : 36;
        embers = Array.from({ length: count }, () => {
            const e = createEmber();
            e.y = Math.random() * height; // Beim Start verteilt über den Screen
            return e;
        });
    }

    function tick() {
        ctx.clearRect(0, 0, width, height);
        embers.forEach(e => {
            e.y -= e.speed;
            e.x += e.drift;
            e.life -= e.decay;

            if (e.life <= 0 || e.y < -20) Object.assign(e, createEmber());

            ctx.beginPath();
            ctx.arc(e.x, e.y, e.radius, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(255, 140, 60, ${Math.max(e.life, 0) * 0.8})`;
            ctx.shadowBlur = 6;
            ctx.shadowColor = 'rgba(255,102,0,0.6)';
            ctx.fill();
        });
        requestAnimationFrame(tick);
    }

    init();
    window.addEventListener('resize', resize);
    tick();
}

// =========================================
// 5. Dezenter Cursor-Glow (folgt der Maus, Desktop only)
// =========================================
function initCursorGlow() {
    if (prefersReducedMotion || isTouchDevice) return;

    const glow = document.createElement('div');
    glow.id = 'cursor-glow';
    document.body.appendChild(glow);

    let mouseX = window.innerWidth / 2, mouseY = window.innerHeight / 2;
    let currentX = mouseX, currentY = mouseY;

    document.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
    });

    function animate() {
        currentX += (mouseX - currentX) * 0.12;
        currentY += (mouseY - currentY) * 0.12;
        glow.style.transform = `translate(${currentX}px, ${currentY}px) translate(-50%, -50%)`;
        requestAnimationFrame(animate);
    }
    animate();
}

// =========================================
// 6. Toast-Benachrichtigungen (ersetzt alert())
// =========================================
window.showToast = function(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 300);
    }, 3200);
};

// 3. IP Kopieren Funktion (Global verfügbar)
window.copyIP = function() {
    navigator.clipboard.writeText("fightsmp.de").then(() => {
        showToast("IP 'fightsmp.de' kopiert! Wir sehen uns in der Arena.", 'success');
    }).catch(err => {
        console.error('Fehler beim Kopieren', err);
        showToast("Kopieren fehlgeschlagen. Bitte manuell kopieren.", 'error');
    });
};
