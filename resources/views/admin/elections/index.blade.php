<x-admin-layout title="Elections Management">
    <x-slot name="styles">
        <style>
            /* ===== ELECTIONS PAGE STYLES ===== */
            .election-card {
                background: white;
                border-radius: var(--radius-lg);
                border: 1px solid var(--gray-200);
                margin-bottom: 1rem;
                transition: all 0.2s ease;
                overflow: hidden;
            }

            .election-card:hover {
                border-color: var(--gray-300);
                box-shadow: var(--shadow-md);
            }

            .election-card.active {
                border-left: 4px solid var(--success);
            }

            .election-card-body {
                padding: 1.5rem;
            }

            .election-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 1rem;
                margin-bottom: 1rem;
            }

            .election-title {
                font-size: 1.25rem;
                font-weight: 700;
                color: var(--gray-800);
                margin: 0 0 0.25rem 0;
            }

            .election-description {
                font-size: 0.875rem;
                color: var(--gray-500);
                margin: 0;
                line-height: 1.5;
            }

            .election-status {
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
                padding: 0.375rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
                flex-shrink: 0;
            }

            .status-active {
                background: #d1fae5;
                color: #065f46;
            }

            .status-active::before {
                content: '';
                width: 6px;
                height: 6px;
                background: currentColor;
                border-radius: 50%;
                animation: pulse 2s infinite;
            }

            .status-inactive {
                background: var(--gray-100);
                color: var(--gray-600);
            }

            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }

            .election-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 1.5rem;
                margin-bottom: 1rem;
            }

            .meta-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.8125rem;
                color: var(--gray-600);
            }

            .meta-item i {
                color: var(--gray-400);
                font-size: 1rem;
            }

            .election-stats {
                display: flex;
                gap: 0.75rem;
                margin-bottom: 1rem;
            }

            .stat-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
                background: var(--gray-100);
                color: var(--gray-700);
                padding: 0.5rem 0.875rem;
                border-radius: var(--radius-sm);
                font-size: 0.8125rem;
                font-weight: 500;
            }

            .stat-badge i {
                color: var(--aclc-blue);
            }

            .action-buttons {
                display: flex;
                gap: 0.5rem;
                flex-wrap: wrap;
                padding-top: 1rem;
                border-top: 1px solid var(--gray-100);
            }

            .action-buttons .btn {
                font-size: 0.8125rem;
            }

            .empty-state {
                background: white;
                border-radius: var(--radius-lg);
                padding: 4rem 2rem;
                text-align: center;
                border: 1px solid var(--gray-200);
            }

            .empty-state-icon {
                width: 80px;
                height: 80px;
                background: var(--gray-100);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1.5rem;
            }

            .empty-state-icon i {
                font-size: 2rem;
                color: var(--gray-400);
            }

            .empty-state h3 {
                font-size: 1.25rem;
                font-weight: 600;
                color: var(--gray-700);
                margin-bottom: 0.5rem;
            }

            .empty-state p {
                color: var(--gray-500);
                margin-bottom: 1.5rem;
            }
        </style>
    </x-slot>

    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-calendar-event-fill"></i>
                    Elections Management
                </h1>
                <p class="page-subtitle">Create and manage election events</p>
            </div>
            <a href="{{ route('admin.elections.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
                Create Election
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle-fill"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Elections List -->
    @if($elections->count() > 0)
        @foreach($elections as $election)
            <div class="election-card {{ $election->is_active ? 'active' : '' }}">
                <div class="election-card-body">
                    <div class="election-header">
                        <div>
                            <h2 class="election-title">{{ $election->title }}</h2>
                            @if($election->description)
                                <p class="election-description">{{ $election->description }}</p>
                            @endif
                        </div>
                        <span class="election-status {{ $election->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $election->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="election-meta">
                        <div class="meta-item">
                            <i class="bi bi-calendar-check"></i>
                            <span>Start: {{ $election->start_date->format('M d, Y h:i A') }}</span>
                        </div>
                        <div class="meta-item">
                            <i class="bi bi-calendar-x"></i>
                            <span>End: {{ $election->end_date->format('M d, Y h:i A') }}</span>
                        </div>
                        @if($election->allow_abstain)
                            <div class="meta-item">
                                <i class="bi bi-dash-circle"></i>
                                <span>Abstain Allowed</span>
                            </div>
                        @endif
                        @if($election->show_live_results)
                            <div class="meta-item">
                                <i class="bi bi-eye"></i>
                                <span>Live Results</span>
                            </div>
                        @endif
                    </div>

                    <div class="election-stats">
                        <span class="stat-badge">
                            <i class="bi bi-award-fill"></i>
                            {{ $election->positions_count }} Positions
                        </span>
                        <span class="stat-badge">
                            <i class="bi bi-people-fill"></i>
                            {{ $election->candidates_count }} Candidates
                        </span>
                    </div>

                    <div class="action-buttons">
                        <a href="{{ route('admin.elections.show', $election) }}" class="btn btn-info btn-sm">
                            <i class="bi bi-eye"></i>
                            View Details
                        </a>
                        <a href="{{ route('admin.elections.edit', $election) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i>
                            Edit
                        </a>
                        <form action="{{ route('admin.elections.toggle-active', $election) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-{{ $election->is_active ? 'secondary' : 'success' }} btn-sm">
                                @if($election->is_active)
                                    <i class="bi bi-pause-circle"></i>
                                    Deactivate
                                @else
                                    <i class="bi bi-play-circle"></i>
                                    Activate
                                @endif
                            </button>
                        </form>
                        <form action="{{ route('admin.elections.destroy', $election) }}" method="POST" class="d-inline" 
                              onsubmit="return confirm('Are you sure you want to delete this election? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bi bi-calendar-x"></i>
            </div>
            <h3>No Elections Yet</h3>
            <p>Get started by creating your first election.</p>
            <a href="{{ route('admin.elections.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
                Create Election
            </a>
        </div>
    @endif
</x-admin-layout>
