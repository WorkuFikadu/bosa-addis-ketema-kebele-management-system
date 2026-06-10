<?php
// includes/footer.php
?>
            </div> <!-- container-fluid end -->

            <!-- DASHBOARD DEVELOPER FOOTER -->
            <footer class="w-100 py-3 mt-auto border-top shadow-sm" style="background-color: #f8fafc; z-index: 10;">
                <div class="container-fluid d-flex flex-column flex-md-row justify-content-center align-items-center gap-3 text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    <span class="fw-bold text-uppercase">Developed by: <span class="text-primary">Worku Fikadu</span></span>
                    <span class="d-none d-md-inline opacity-25">|</span>
                    <span><i class="fas fa-phone text-primary me-1"></i> +251 934 953 593</span>
                    <span class="d-none d-md-inline opacity-25">|</span>
                    <span><i class="fas fa-envelope text-primary me-1"></i> workufikadu643@gmail.com</span>
                </div>
            </footer>
            <!-- END DEVELOPER FOOTER -->

        </div> <!-- page-content-wrapper end -->
    </div> <!-- wrapper end -->
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="/Bosa Addis/assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Global Search
            const searchInput = document.getElementById('globalSearchInput');
            const searchResults = document.getElementById('globalSearchResults');

            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const q = this.value;
                    if (q.length >= 2) {
                        fetch('/Bosa Addis/api/search-api.php?q=' + encodeURIComponent(q))
                            .then(response => response.json())
                            .then(data => {
                                searchResults.innerHTML = '';
                                if (data.length > 0) {
                                    data.forEach(item => {
                                        searchResults.innerHTML += `
                                            <a href="${item.url}" class="dropdown-item py-2 border-bottom text-wrap">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light rounded-circle p-2 me-3"><i class="fas ${item.icon} text-primary"></i></div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold">${item.title}</h6>
                                                        <small class="text-muted">${item.desc}</small>
                                                    </div>
                                                </div>
                                            </a>
                                        `;
                                    });
                                } else {
                                    searchResults.innerHTML = '<div class="p-3 text-center text-muted small">No results found.</div>';
                                }
                                searchResults.style.display = 'block';
                            });
                    } else {
                        searchResults.style.display = 'none';
                    }
                });

                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                        searchResults.style.display = 'none';
                    }
                });
            }

            // Notifications
            function fetchNotifications() {
                fetch('/Bosa Addis/api/notifications-api.php')
                    .then(response => response.json())
                    .then(data => {
                        if(data.error) return;
                        
                        const badge = document.getElementById('notif-badge');
                        const list = document.getElementById('notif-list');
                        
                        if (data.unread_count > 0) {
                            badge.style.display = 'block';
                            badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                        } else {
                            badge.style.display = 'none';
                        }
                        
                        list.innerHTML = '';
                        if (data.notifications.length > 0) {
                            data.notifications.forEach(n => {
                                const readClass = n.is_read ? 'text-muted' : 'fw-bold text-dark';
                                list.innerHTML += `
                                    <li><a class="dropdown-item py-3 border-bottom ${readClass}" href="${n.link || '#'}">
                                        <div class="small">${n.message}</div>
                                        <div class="text-end text-muted mt-1" style="font-size: 0.65rem;">
                                            <i class="far fa-clock"></i> ${new Date(n.created_at).toLocaleString()}
                                        </div>
                                    </a></li>
                                `;
                            });
                        } else {
                            list.innerHTML = '<li><a class="dropdown-item py-3 text-center text-muted small" href="#">No new notifications</a></li>';
                        }
                    });
            }

            if (document.getElementById('notif-badge')) {
                fetchNotifications();
                setInterval(fetchNotifications, 30000); // Check every 30s
                
                // Mark as read when clicking the bell
                document.querySelector('[data-bs-toggle="dropdown"]').addEventListener('click', function() {
                    fetch('/Bosa Addis/api/notifications-api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ mark_read: true })
                    }).then(() => {
                        document.getElementById('notif-badge').style.display = 'none';
                    });
                });
            }
        });
    </script>
</body>
</html>
<?php 
ob_end_flush(); 
?>
