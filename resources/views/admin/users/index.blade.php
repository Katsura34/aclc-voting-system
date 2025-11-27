<x-admin-layout title="Manage Users">
    <x-slot name="styles">
        <style>
            /* ===== USERS PAGE STYLES ===== */
            .filter-card {
                background: white;
                border-radius: var(--radius-lg);
                border: 1px solid var(--gray-200);
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .filter-card .form-label {
                font-size: 0.8125rem;
                font-weight: 600;
                color: var(--gray-600);
                margin-bottom: 0.375rem;
            }

            .user-type-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
                padding: 0.375rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
            }

            .user-type-badge.admin {
                background: #fee2e2;
                color: #991b1b;
            }

            .user-type-badge.student {
                background: #dbeafe;
                color: #1e40af;
            }

            .vote-status-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
                padding: 0.375rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
            }

            .vote-status-badge.voted {
                background: #d1fae5;
                color: #065f46;
            }

            .vote-status-badge.not-voted {
                background: var(--gray-100);
                color: var(--gray-600);
            }

            .user-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--aclc-blue) 0%, var(--aclc-light-blue) 100%);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
                font-size: 0.875rem;
                flex-shrink: 0;
            }

            .user-info {
                flex: 1;
                min-width: 0;
            }

            .user-name {
                font-weight: 600;
                color: var(--gray-800);
                font-size: 0.9375rem;
            }

            .user-email {
                font-size: 0.75rem;
                color: var(--gray-500);
            }

            .empty-state {
                background: white;
                border-radius: var(--radius-lg);
                border: 1px solid var(--gray-200);
                padding: 4rem 2rem;
                text-align: center;
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
            }
        </style>
    </x-slot>

    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-person-badge-fill"></i>
                    Manage Users
                </h1>
                <p class="page-subtitle">View and manage all system users</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#resetAllVotesModal">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Reset All Votes
                </button>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i>
                    Add User
                </a>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
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

    <!-- Filter Section -->
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.users.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">
                        <i class="bi bi-search"></i> Search
                    </label>
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           placeholder="Student ID, Name, Email..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="bi bi-person-badge"></i> User Type
                    </label>
                    <select class="form-select" name="user_type">
                        <option value="">All Types</option>
                        <option value="student" {{ request('user_type') == 'student' ? 'selected' : '' }}>Student</option>
                        <option value="admin" {{ request('user_type') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="bi bi-check-circle"></i> Voting Status
                    </label>
                    <select class="form-select" name="has_voted">
                        <option value="">All Status</option>
                        <option value="yes" {{ request('has_voted') == 'yes' ? 'selected' : '' }}>Has Voted</option>
                        <option value="no" {{ request('has_voted') == 'no' ? 'selected' : '' }}>Not Voted</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-table"></i> Users List
            </h5>
            <span class="badge bg-primary">{{ $users->total() }} users</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Voting Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->student_id }}</strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="user-avatar">
                                            {{ strtoupper(substr($user->first_name ?? $user->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="user-info">
                                            <div class="user-name">{{ $user->first_name ?? '' }} {{ $user->last_name ?? $user->name ?? 'N/A' }}</div>
                                            <div class="user-email">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($user->user_type === 'admin')
                                        <span class="user-type-badge admin">
                                            <i class="bi bi-shield-fill"></i>
                                            Admin
                                        </span>
                                    @else
                                        <span class="user-type-badge student">
                                            <i class="bi bi-person-fill"></i>
                                            Student
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $user->course ?? '—' }}</td>
                                <td>{{ $user->year_level ?? '—' }}</td>
                                <td>
                                    @if($user->has_voted)
                                        <span class="vote-status-badge voted">
                                            <i class="bi bi-check-circle-fill"></i>
                                            Voted
                                        </span>
                                    @else
                                        <span class="vote-status-badge not-voted">
                                            <i class="bi bi-x-circle"></i>
                                            Not Voted
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="btn btn-sm btn-warning btn-icon"
                                           data-bs-toggle="tooltip"
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        @if($user->has_voted && $user->user_type === 'student')
                                            <form action="{{ route('admin.users.reset-vote', $user) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Reset voting status for this user?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-info btn-icon"
                                                        data-bs-toggle="tooltip"
                                                        title="Reset Vote">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <form action="{{ route('admin.users.destroy', $user) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger btn-icon"
                                                    data-bs-toggle="tooltip"
                                                    title="Delete"
                                                    {{ $user->id === auth()->user()->id ? 'disabled' : '' }}>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <h3>No Users Found</h3>
                                        <p>No users match your search criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Reset All Votes Modal -->
    <div class="modal fade" id="resetAllVotesModal" tabindex="-1" aria-labelledby="resetAllVotesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resetAllVotesModalLabel">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                        Reset All Votes
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.users.reset-all-votes') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to reset voting status for <strong>ALL students</strong>?</p>
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Warning:</strong> This action will allow all students to vote again. This cannot be undone!
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Reset All Votes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
