<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartStock | Supplier Directory</title>

    <!-- Google Fonts & Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1"
        rel="stylesheet">

    <!-- Custom Pure CSS (No Tailwind) -->
    <link rel="stylesheet" href="resources/styles/suppliers.css">
</head>

<body>

    <!-- Navigation Sidebar -->
    <?php require_once 'views/partials/sidenav.php' ?>

    <!-- Main Outer Layout Wrapper -->
    <div class="main-wrapper">

        <!-- Header / Top Navbar -->
        <header class="top-header">
            <div class="search-wrapper">
                <div class="search-box">
                    <span class="material-symbols-outlined">search</span>
                    <input class="search-input" placeholder="Search inventory or suppliers..." type="text">
                </div>
            </div>

            <div class="header-user-actions">
                <button class="icon-button">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="notification-badge"></span>
                </button>
                <div class="user-profile">
                    <div class="user-info">
                        <p class="user-name">Alex Sterling</p>
                        <p class="user-role">Logistics Director</p>
                    </div>
                    <div class="user-avatar">
                        AS
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Workspace View -->
        <main class="main-content">
            <div class="page-container">

                <!-- Page Title & Primary Actions -->
                <section class="page-header">
                    <div class="header-meta">
                        <span class="badge-label">Global Partnerships</span>
                        <h1 class="page-title">Supplier Directory</h1>
                        <p class="page-description">
                            Manage your ecosystem of manufacturing and logistics partners with real-time tracking and
                            centralized communication.
                        </p>
                    </div>
                    <div>
                        <button class="btn-primary" onclick="toggleSidePanel()">
                            <span class="material-symbols-outlined">person_add</span>
                            <span>Add New Supplier</span>
                        </button>
                    </div>
                </section>

                <!-- Filter Options Bar -->
                <section class="filter-bar">
                    <div class="filter-group">
                        <!-- Region Filter -->
                        <div class="select-wrapper">
                            <select class="filter-select">
                                <option>All Regions</option>
                                <option>North America</option>
                                <option>European Union</option>
                                <option>Asia Pacific</option>
                            </select>
                            <span class="material-symbols-outlined select-icon">expand_more</span>
                        </div>

                        <div class="filter-divider"></div>

                        <!-- Status Filter -->
                        <div class="select-wrapper">
                            <select class="filter-select">
                                <option>All Statuses</option>
                                <option>Active</option>
                                <option>On Hold</option>
                                <option>Inactive</option>
                            </select>
                            <span class="material-symbols-outlined select-icon">filter_list</span>
                        </div>
                    </div>

                    <p class="filter-info">Showing active filter list</p>
                </section>

                <!-- Supplier Data Table -->
                <section class="table-card">
                    <table class="data-table">
                        <thead>
                            <tr class="table-header-row">
                                <th>Supplier Name</th>
                                <th>Contact Details</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            <!-- Supplier Row 1 -->
                            <tr>
                                <td class="table-cell">
                                    <div class="cell-supplier">
                                        <div class="supplier-avatar av">AV</div>
                                        <div>
                                            <p class="supplier-name">Aether Ventures</p>
                                            <p class="supplier-location">Berlin, DE</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="table-cell">
                                    <div class="contact-info">
                                        <div class="contact-item">
                                            <span class="material-symbols-outlined">mail</span>
                                            <span>elena.f@aetherv.co</span>
                                        </div>
                                        <div class="contact-item">
                                            <span class="material-symbols-outlined">call</span>
                                            <span>+49 30 9234 110</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="table-cell">
                                    <span class="status-badge active">
                                        <span class="status-dot"></span>
                                        Active
                                    </span>
                                </td>
                                <td class="table-cell text-right">
                                    <button class="btn-row-action">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                </td>
                            </tr>

                            <!-- Supplier Row 2 -->
                            <tr>
                                <td class="table-cell">
                                    <div class="cell-supplier">
                                        <div class="supplier-avatar sk">SK</div>
                                        <div>
                                            <p class="supplier-name">Sanko Kyogyo</p>
                                            <p class="supplier-location">Osaka, JP</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="table-cell">
                                    <div class="contact-info">
                                        <div class="contact-item">
                                            <span class="material-symbols-outlined">mail</span>
                                            <span>h.tanaka@sanko-k.jp</span>
                                        </div>
                                        <div class="contact-item">
                                            <span class="material-symbols-outlined">call</span>
                                            <span>+81 6-6345-0012</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="table-cell">
                                    <span class="status-badge on-hold">
                                        <span class="status-dot"></span>
                                        On Hold
                                    </span>
                                </td>
                                <td class="table-cell text-right">
                                    <button class="btn-row-action">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </div>
        </main>
    </div>

    <!-- Add Supplier Modal / Drawer -->
    <div id="supplierSidePanel" class="side-panel">
        <div class="panel-header">
            <h2 class="panel-title">Add New Supplier</h2>
            <button class="btn-close" onclick="toggleSidePanel()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Form Modal Body -->
        <div class="panel-body">
            <!-- Company Info Section -->
            <div class="form-section">
                <h3 class="form-section-title">Company Information</h3>

                <div class="form-group">
                    <label class="form-label">Supplier Name</label>
                    <input class="form-control" placeholder="e.g. Aether Ventures" type="text">
                </div>

                <div class="form-group">
                    <label class="form-label">Location / Address</label>
                    <input class="form-control" placeholder="e.g. Berlin, DE" type="text">
                </div>
            </div>

            <!-- Contact Information Section -->
            <div class="form-section">
                <h3 class="form-section-title">Contact Information</h3>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input class="form-control" placeholder="contact@company.com" type="email">
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input class="form-control" placeholder="+1 (555) 000-0000" type="tel">
                </div>
            </div>

            <!-- Operational Details Section -->
            <div class="form-section">
                <h3 class="form-section-title">Operational Settings</h3>

                <div class="form-group">
                    <label class="form-label">Initial Status</label>
                    <select class="form-control select-control">
                        <option value="active">Active</option>
                        <option value="on-hold">On Hold</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="panel-footer">
            <button class="btn-cancel" onclick="toggleSidePanel()">Cancel</button>
            <button class="btn-submit">Save Supplier</button>
        </div>
    </div>

    <!-- Backdrop Overlay -->
    <div id="panelOverlay" class="backdrop-overlay" onclick="toggleSidePanel()"></div>

    <!-- Panel Drawer Script -->
    <script>
        function toggleSidePanel() {
            const panel = document.getElementById('supplierSidePanel');
            const overlay = document.getElementById('panelOverlay');

            panel.classList.toggle('open');

            if (panel.classList.contains('open')) {
                overlay.style.display = 'block';
                setTimeout(() => overlay.classList.add('active'), 10);
            } else {
                overlay.classList.remove('active');
                setTimeout(() => overlay.style.display = 'none', 300);
            }
        }
    </script>
</body>

</html>