<x-admin-layout title="Elections Management">
    <x-slot name="styles">
        .election-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border-left: 5px solid #e0e0e0;
        }

        .election-card:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .election-card.active {
            border-left-color: var(--aclc-red);
            background: linear-gradient(135deg, rgba(204, 0, 0, 0.02) 0%, rgba(204, 0, 0, 0.05) 100%);
        }

        .election-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .election-title {
            color: var(--aclc-blue);
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 5px;
        }

        .election-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .election-meta {
            display: flex;
            gap: 25px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 0.9rem;
        }

        .meta-item i {
            color: var(--aclc-light-blue);
        }

        .election-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .stat-badge {
            background: var(--aclc-light-blue);
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .empty-state {
            background: white;
            border-radius: 15px;
            padding: 60px 30px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #999;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #bbb;
            margin-bottom: 20px;
        }
    </x-slot>

    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title">
                <i class="bi bi-calendar-event"></i> Elections Management
            </h1>
            <a href="{{ route('admin.elections.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create New Election
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Elections List -->
    @if($elections->count() > 0)
        @foreach($elections as $election)
            <div class="election-card {{ $election->is_active ? 'active' : '' }}">
                <div class="election-header">
                    <div>
                        <h2 class="election-title">{{ $election->title }}</h2>
                        <span class="election-status {{ $election->is_active ? 'status-active' : 'status-inactive' }}">
                            @if($election->is_active)
                                <i class="bi bi-check-circle"></i> Active
                            @else
                                <i class="bi bi-x-circle"></i> Inactive
                            @endif
                        </span>
                    </div>
                </div>

                @if($election->description)
                    <p class="text-muted mb-3">{{ $election->description }}</p>
                @endif

                <div class="election-meta">
                    <div class="meta-item">
                        <i class="bi bi-calendar-check"></i>
                        <span>Start: {{ $election->start_date->format('M d, Y') }}</span>
                    </div>
                    <div class="meta-item">
                        <i class="bi bi-calendar-x"></i>
                        <span>End: {{ $election->end_date->format('M d, Y') }}</span>
                    </div>
                </div>

                <div class="election-stats">
                    <span class="stat-badge">
                        <i class="bi bi-award"></i> {{ $election->positions_count }} Positions
                    </span>
                    <span class="stat-badge">
                        <i class="bi bi-people"></i> {{ $election->candidates_count }} Candidates
                    </span>
                </div>

                <div class="action-buttons">
                    <a href="{{ route('admin.elections.show', $election) }}" class="btn btn-info btn-sm">
                        <i class="bi bi-eye"></i> View Details
                    </a>
                    <a href="{{ route('admin.candidates.index', ['election_id' => $election->id]) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-people"></i> Candidates
                    </a>
                    <a href="{{ route('admin.elections.edit', $election) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <form action="{{ route('admin.elections.toggle-active', $election) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            @if($election->is_active)
                                <i class="bi bi-pause-circle"></i> Deactivate
                            @else
                                <i class="bi bi-play-circle"></i> Activate
                            @endif
                        </button>
                    </form>
                    <form action="{{ route('admin.elections.destroy', $election) }}" method="POST" class="d-inline" 
                          onsubmit="return confirm('Are you sure you want to delete this election? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    @else
        <div class="empty-state">
            <i class="bi bi-calendar-x"></i>
            <h3>No Elections Yet</h3>
            <p>Get started by creating your first election.</p>
            <a href="{{ route('admin.elections.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create Election
            </a>
        </div>
    @endif
</x-admin-layout>
