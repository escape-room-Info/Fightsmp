/**
 * FightSMP - Globales Script
 * Behandelt Animationen, Server-Status und UI-Interaktionen.
 */
document.addEventListener('DOMContentLoaded', () => {

    // --- 1. Clean Page Transitions (Für ALLE Seiten) ---
    document.querySelectorAll('a[href]').forEach(link => {
        link.addEventListener('click', e => {
            const target = link.getAttribute('href');
            // Ignoriere externe Links, Anker und Links die in neuen Tabs öffnen
            if (target && !target.startsWith('http') && !target.startsWith('#') && link.target !== '_blank') {
                e.preventDefault(); 
                document.body.classList.add('fade-out'); 
                setTimeout(() => { window.location.href = target; }, 400); 
            }
        });
    });

    // --- 2. Mobile Menu Toggle ---
    window.toggleMobileMenu = () => {
        const m = document.getElementById('navMenu');
        const i = document.getElementById('menu-icon');
        if (m && i) {
            m.classList.toggle('active');
            i.className = m.classList.contains('active') ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
        }
    };

    // --- 3. Server Status Checker ---
    const updateServerStatus = async () => {
        const countElement = document.getElementById('player-count-num');
        const dotElement = document.getElementById('status-indicator');
        const staffDot = document.getElementById('owner-status-dot');
        const staffText = document.getElementById('owner-status-text');
        
        // Bricht ab, wenn wir auf einer Seite ohne Status-Widget sind (z.B. Login/Dashboard)
        if (!countElement) return;

        try {
            countElement.style.opacity = '0.5';
            const res = await fetch('https://api.mcsrvstat.us/3/fightsmp.de');
            if (!res.ok) throw new Error("API nicht erreichbar");
            const data = await res.json();
            
            if (data.online) {
                countElement.innerText = `${data.players.online} / ${data.players.max}`;
                countElement.style.color = '#10b981';
                if (dotElement) {
                    dotElement.style.backgroundColor = '#10b981';
                    dotElement.classList.add('pulse');
                }

                // Modal Infos (Version & Map)
                const versionEl = document.getElementById('server-version');
                const mapEl = document.getElementById('server-map');
                if (versionEl) versionEl.innerText = "Version: " + data.version;
                if (mapEl) mapEl.innerText = "Map: " + (data.map || "Standard");

                // Owner Status im Footer
                if (staffDot && staffText) {
                    if (data.players.list && data.players.list.some(p => p.name === "DerOwnerName")) {
                        staffDot.classList.add('online'); 
                        staffText.innerText = "Im Spiel"; 
                        staffText.style.color = "#10b981";
                    } else {
                        staffDot.classList.remove('online'); 
                        staffText.innerText = "Offline"; 
                        staffText.style.color = "#ef4444";
                    }
                }
            } else { 
                throw new Error("Server ist offline"); 
            }
        } catch (error) {
            countElement.innerText = 'Wartungsarbeiten'; 
            countElement.style.color = '#ef4444';
            if (dotElement) { 
                dotElement.style.backgroundColor = '#ef4444'; 
                dotElement.classList.remove('pulse'); 
            }
            if (staffDot && staffText) { 
                staffDot.classList.remove('online'); 
                staffText.innerText = "Offline"; 
                staffText.style.color = "#ef4444"; 
            }
        } finally {
            countElement.style.opacity = '1';
        }
    };

    // Starte den Status-Checker nur, wenn das Element auf der Seite existiert
    if (document.getElementById('player-count-num')) {
        updateServerStatus();
        setInterval(updateServerStatus, 60000);
    }

    // --- 4. Elegantes Copy-IP mit Orange Toast ---
    window.copyServerIP = () => {
        const ip = "fightsmp.de";
        navigator.clipboard.writeText(ip).then(() => {
            const toast = document.createElement('div');
            toast.innerText = `✅ IP '${ip}' erfolgreich kopiert!`;
            toast.style.cssText = `
                position:fixed; bottom:30px; left:50%; transform:translateX(-50%) translateY(100px);
                background:rgba(249,115,22,0.9); backdrop-filter:blur(10px); color:#fff;
                padding:12px 24px; border-radius:8px; font-weight:600; font-size:15px;
                box-shadow:0 10px 30px rgba(249,115,22,0.4); transition:all 0.4s ease;
                opacity:0; z-index:9999; pointer-events:none;
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => { toast.style.transform = 'translateX(-50%) translateY(0)'; toast.style.opacity = '1'; }, 10);
            setTimeout(() => { toast.style.transform = 'translateX(-50%) translateY(100px)'; toast.style.opacity = '0'; setTimeout(() => toast.remove(), 400); }, 3000);
        }).catch(err => prompt('Bitte kopiere die IP manuell:', ip));
    };

    // --- 5. FAQ Toggle (Nur für die Support-Seite relevant) ---
    window.toggleFaq = (btn) => {
        const item = btn.closest('.faq-item');
        if(!item) return;
        const isOpen = item.classList.contains('open');
        document.querySelectorAll('.faq-item.open').forEach(el => el.classList.remove('open'));
        if (!isOpen) item.classList.add('open');
    };
});
