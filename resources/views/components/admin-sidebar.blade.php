<!-- Mobile Menu Toggle Button -->
<button class="sidebar-toggle d-lg-none" id="sidebarToggle" aria-label="Toggle navigation">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay d-lg-none" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <!-- Brand Section -->
    <div class="sidebar-brand">
        <div class="brand-logo">
            <i class="bi bi-shield-check"></i>
        </div>
        <div class="brand-text">
            <h1>ACLC Admin</h1>
            <span>Voting System v2.0</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="nav-section">
            <span class="nav-section-title">Main Menu</span>
            
            <a class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
               href="{{ route('admin.dashboard') }}"
               aria-current="{{ request()->routeIs('admin.dashboard') ? 'page' : 'false' }}">
                <span class="nav-icon">
                    <i class="bi bi-grid-1x2-fill"></i>
                </span>
                <span class="nav-text">Dashboard</span>
                @if(request()->routeIs('admin.dashboard'))
                    <span class="nav-indicator"></span>
                @endif
            </a>
        </div>

        <div class="nav-section">
            <span class="nav-section-title">Election Management</span>
            
            <a class="nav-item {{ request()->routeIs('admin.elections.*') ? 'active' : '' }}" 
               href="{{ route('admin.elections.index') }}"
               aria-current="{{ request()->routeIs('admin.elections.*') ? 'page' : 'false' }}">
                <span class="nav-icon">
                    <i class="bi bi-calendar-event-fill"></i>
                </span>
                <span class="nav-text">Elections</span>
                @if(request()->routeIs('admin.elections.*'))
                    <span class="nav-indicator"></span>
                @endif
            </a>

            <a class="nav-item {{ request()->routeIs('admin.positions.*') ? 'active' : '' }}" 
               href="{{ route('admin.positions.index') }}"
               aria-current="{{ request()->routeIs('admin.positions.*') ? 'page' : 'false' }}">
                <span class="nav-icon">
                    <i class="bi bi-award-fill"></i>
                </span>
                <span class="nav-text">Positions</span>
                @if(request()->routeIs('admin.positions.*'))
                    <span class="nav-indicator"></span>
                @endif
            </a>

            <a class="nav-item {{ request()->routeIs('admin.parties.*') ? 'active' : '' }}" 
               href="{{ route('admin.parties.index') }}"
               aria-current="{{ request()->routeIs('admin.parties.*') ? 'page' : 'false' }}">
                <span class="nav-icon">
                    <i class="bi bi-flag-fill"></i>
                </span>
                <span class="nav-text">Parties</span>
                @if(request()->routeIs('admin.parties.*'))
                    <span class="nav-indicator"></span>
                @endif
            </a>

            <a class="nav-item {{ request()->routeIs('admin.candidates.*') ? 'active' : '' }}" 
               href="{{ route('admin.candidates.index') }}"
               aria-current="{{ request()->routeIs('admin.candidates.*') ? 'page' : 'false' }}">
                <span class="nav-icon">
                    <i class="bi bi-people-fill"></i>
                </span>
                <span class="nav-text">Candidates</span>
                @if(request()->routeIs('admin.candidates.*'))
                    <span class="nav-indicator"></span>
                @endif
            </a>
        </div>

        <div class="nav-section">
            <span class="nav-section-title">Reports & Users</span>
            
            <a class="nav-item {{ request()->routeIs('admin.results.*') ? 'active' : '' }}" 
               href="{{ route('admin.results.index') }}"
               aria-current="{{ request()->routeIs('admin.results.*') ? 'page' : 'false' }}">
                <span class="nav-icon">
                    <i class="bi bi-bar-chart-fill"></i>
                </span>
                <span class="nav-text">Results</span>
                @if(request()->routeIs('admin.results.*'))
                    <span class="nav-indicator"></span>
                @endif
            </a>

            <a class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" 
               href="{{ route('admin.users.index') }}"
               aria-current="{{ request()->routeIs('admin.users.*') ? 'page' : 'false' }}">
                <span class="nav-icon">
                    <i class="bi bi-person-badge-fill"></i>
                </span>
                <span class="nav-text">Users</span>
                @if(request()->routeIs('admin.users.*'))
                    <span class="nav-indicator"></span>
                @endif
            </a>
        </div>
    </nav>

    <!-- User Section & Logout -->
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
            </div>
            <div class="user-details">
                <span class="user-name">{{ Auth::user()->name ?? 'Admin' }}</span>
                <span class="user-role">Administrator</span>
            </div>
        </div>
        
        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button type="submit" class="btn-logout" aria-label="Logout from admin panel">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
            </button>
        </form>
    </div>
</aside>

<style>
    /* ===== SIDEBAR VARIABLES ===== */
    :root {
        --sidebar-bg: linear-gradient(180deg, #1e3a5f 0%, #0f2744 100%);
        --sidebar-width: 260px;
        --sidebar-item-hover: rgba(255, 255, 255, 0.08);
        --sidebar-item-active: rgba(255, 255, 255, 0.12);
        --sidebar-accent: #3b82f6;
        --sidebar-text: rgba(255, 255, 255, 0.85);
        --sidebar-text-muted: rgba(255, 255, 255, 0.5);
    }

    /* ===== SIDEBAR CONTAINER ===== */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: var(--sidebar-width);
        background: var(--sidebar-bg);
        display: flex;
        flex-direction: column;
        z-index: 1050;
        transition: transform 0.3s ease;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
    }

    /* ===== MOBILE TOGGLE ===== */
    .sidebar-toggle {
        position: fixed;
        top: 16px;
        left: 16px;
        z-index: 1100;
        width: 44px;
        height: 44px;
        border: none;
        border-radius: 10px;
        background: #1e3a5f;
        color: white;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.2s ease;
    }

    .sidebar-toggle:hover {
        background: #2d5a87;
        transform: scale(1.05);
    }

    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1040;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .sidebar-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    /* ===== BRAND SECTION ===== */
    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 24px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .brand-logo {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .brand-text h1 {
        color: white;
        font-weight: 700;
        font-size: 1.125rem;
        margin: 0;
        line-height: 1.2;
    }

    .brand-text span {
        color: var(--sidebar-text-muted);
        font-size: 0.75rem;
        font-weight: 500;
    }

    /* ===== NAVIGATION ===== */
    .sidebar-nav {
        flex: 1;
        overflow-y: auto;
        padding: 16px 12px;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
    }

    .sidebar-nav::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-nav::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
    }

    .nav-section {
        margin-bottom: 24px;
    }

    .nav-section:last-child {
        margin-bottom: 0;
    }

    .nav-section-title {
        display: block;
        color: var(--sidebar-text-muted);
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 0 12px;
        margin-bottom: 8px;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 10px;
        color: var(--sidebar-text);
        text-decoration: none;
        font-size: 0.9375rem;
        font-weight: 500;
        transition: all 0.2s ease;
        position: relative;
        margin-bottom: 4px;
    }

    .nav-item:hover {
        background: var(--sidebar-item-hover);
        color: white;
    }

    .nav-item.active {
        background: var(--sidebar-item-active);
        color: white;
    }

    .nav-icon {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
        opacity: 0.8;
        flex-shrink: 0;
    }

    .nav-item:hover .nav-icon,
    .nav-item.active .nav-icon {
        opacity: 1;
    }

    .nav-text {
        flex: 1;
    }

    .nav-indicator {
        width: 6px;
        height: 6px;
        background: var(--sidebar-accent);
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* ===== FOOTER SECTION ===== */
    .sidebar-footer {
        padding: 16px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(0, 0, 0, 0.1);
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
        padding: 8px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.05);
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .user-details {
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .user-name {
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-role {
        color: var(--sidebar-text-muted);
        font-size: 0.75rem;
    }

    .logout-form {
        width: 100%;
    }

    .btn-logout {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 16px;
        background: rgba(239, 68, 68, 0.15);
        border: 1px solid rgba(239, 68, 68, 0.3);
        border-radius: 10px;
        color: #fca5a5;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-logout:hover {
        background: rgba(239, 68, 68, 0.25);
        color: #fecaca;
        transform: translateY(-1px);
    }

    .btn-logout i {
        font-size: 1.125rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 991.98px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.active {
            transform: translateX(0);
        }
    }

    @media (min-width: 992px) {
        .sidebar-toggle {
            display: none;
        }

        .sidebar-overlay {
            display: none;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        // Toggle sidebar on mobile
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
            });
        }

        // Close sidebar when clicking overlay
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            });
        }

        // Close sidebar when clicking a nav item on mobile
        const navItems = document.querySelectorAll('.sidebar .nav-item');
        navItems.forEach(function(item) {
            item.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });
    });
</script>