/* assets/js/main.js */
document.addEventListener("DOMContentLoaded", function() {
    // Sidebar Toggle
    const menuToggle = document.getElementById("menu-toggle");
    const wrapper = document.getElementById("wrapper");

    // Sidebar Toggle Logic
    const toggleSidebar = function(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        document.body.classList.toggle("sidebar-toggled");
        const mainWrapper = document.getElementById("wrapper");
        if (mainWrapper) {
            mainWrapper.classList.toggle("toggled");
        }
    };

    if (menuToggle) {
        menuToggle.onclick = toggleSidebar;
    }

    // Fullscreen Toggle
    const fsToggle = document.getElementById("fullscreen-toggle");

    // Helper: enter fullscreen and save state
    function enterFullscreen() {
        document.documentElement.requestFullscreen().then(function() {
            localStorage.setItem('fsState', 'on');
            if (fsToggle) fsToggle.innerHTML = '<i class="fas fa-compress"></i>';
        }).catch(function(err) {
            console.error('Error entering fullscreen:', err.message);
        });
    }

    // Helper: exit fullscreen and clear state
    function exitFullscreen() {
        if (document.exitFullscreen) {
            document.exitFullscreen().then(function() {
                localStorage.setItem('fsState', 'off');
                if (fsToggle) fsToggle.innerHTML = '<i class="fas fa-expand"></i>';
            }).catch(function(err) {
                console.error('Error exiting fullscreen:', err.message);
            });
        }
    }

    // Auto-restore fullscreen on page load if user had it on
    if (localStorage.getItem('fsState') === 'on') {
        // Small delay to ensure the page is ready for the fullscreen request
        setTimeout(function() {
            if (!document.fullscreenElement) {
                enterFullscreen();
            }
        }, 300);
    }

    // Keep button icon in sync if user exits fullscreen via Escape key
    document.addEventListener('fullscreenchange', function() {
        if (!document.fullscreenElement) {
            // Fullscreen was exited (e.g. via Escape key)
            // Only clear saved state if it was intentionally closed (handled by exitFullscreen()),
            // but Escape key won't call exitFullscreen(), so we detect it here.
            if (fsToggle) fsToggle.innerHTML = '<i class="fas fa-expand"></i>';
            // Clear saved state so next page load does NOT auto-enter fullscreen
            localStorage.setItem('fsState', 'off');
        } else {
            if (fsToggle) fsToggle.innerHTML = '<i class="fas fa-compress"></i>';
            localStorage.setItem('fsState', 'on');
        }
    });

    // Fullscreen Toggle Button Click Logic
    if (fsToggle) {
        fsToggle.onclick = function(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            if (!document.fullscreenElement) {
                enterFullscreen();
            } else {
                exitFullscreen();
            }
        };
    }

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll(".alert");
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
