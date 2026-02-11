<div class="sidebar">
    <div class="sidebar-brand">
        <h4><i class="bi bi-shield-check"></i> ACLC Admin</h4>
        <small>Voting System</small>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
               href="{{ route('admin.dashboard') }}">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.elections.*') ? 'active' : '' }}" 
               href="{{ route('admin.elections.index') }}">
                <i class="bi bi-calendar-event"></i>
                Elections
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.candidates.*') ? 'active' : '' }}" 
               href="{{ route('admin.candidates.index') }}">
                <i class="bi bi-people"></i>
                Candidates
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.parties.*') ? 'active' : '' }}" 
               href="{{ route('admin.parties.index') }}">
                <i class="bi bi-flag"></i>
                Parties
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.positions.*') ? 'active' : '' }}" 
               href="{{ route('admin.positions.index') }}">
                <i class="bi bi-award"></i>
                Positions
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.results.*') ? 'active' : '' }}" 
               href="{{ route('admin.results.index') }}">
                <i class="bi bi-bar-chart"></i>
                Results
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}" 
               href="{{ route('admin.audit-logs.index') }}">
                <i class="bi bi-file-earmark-text"></i>
                Voter Records
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" 
               href="{{ route('admin.users.index') }}">
                <i class="bi bi-person-badge"></i>
                Students
            </a>
        </li>
        <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </button>
        </form>
        </li>
    </ul>
    

</div>

<style>
    :root {
        --aclc-blue: #003366;
        --aclc-light-blue: #00509E;
        --aclc-red: #CC0000;
        --sidebar-width: 250px;
    }

    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: var(--sidebar-width);
        background: linear-gradient(180deg, var(--aclc-blue) 0%, var(--aclc-light-blue) 100%);
        padding: 20px 0;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .sidebar-brand {
        padding: 0 20px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 20px;
    }

    .sidebar-brand h4 {
        color: white;
        font-weight: 700;
        margin: 0;
        font-size: 1.3rem;
    }

    .sidebar-brand small {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.85rem;
    }

    .nav-item {
        margin: 5px 10px;
    }

    .nav-link {
        color: rgba(255, 255, 255, 0.8);
        padding: 12px 20px;
        border-radius: 8px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
    }

    .nav-link:hover, .nav-link.active {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .nav-link i {
        font-size: 1.1rem;
    }

    .sidebar-footer {
        position: absolute;
        bottom: 20px;
        left: 0;
        right: 0;
        padding: 0 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 20px;
    }

    .btn-logout {
        width: 100%;
        padding: 12px 20px;
        background: rgba(204, 0, 0, 0.9);
        border: none;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-logout:hover {
        background: var(--aclc-red);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(204, 0, 0, 0.3);
    }

    .btn-logout i {
        font-size: 1.1rem;
    }
</style>