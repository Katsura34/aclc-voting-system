<x-admin-layout title="Manage Users">
    <x-slot name="styles">
        <style>
            .user-card {
                transition: all 0.3s ease;
            }
            .user-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            .status-badge {
                padding: 5px 10px;
                border-radius: 15px;
                font-size: 0.85rem;
            }
            .filter-card {
                background: #f8f9fa;
                border-left: 4px solid var(--aclc-red);
            }
        </style>
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-people-fill"></i> Manage Users</h2>
                <p class="text-muted mb-0">View and manage all system users</p>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#resetAllVotesModal">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset All Votes
                </button>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New User
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
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

        <!-- Search and Filter -->
        <div class="card filter-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label"><i class="bi bi-search"></i> Search</label>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Student ID, Name, Email..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><i class="bi bi-person-badge"></i> User Type</label>
                        <select class="form-select" name="user_type">
                            <option value="">All Types</option>
                            <option value="student" {{ request('user_type') == 'student' ? 'selected' : '' }}>Student</option>
                            <option value="admin" {{ request('user_type') == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><i class="bi bi-check-circle"></i> Voting Status</label>
                        <select class="form-select" name="has_voted">
                            <option value="">All Status</option>
                            <option value="yes" {{ request('has_voted') == 'yes' ? 'selected' : '' }}>Has Voted</option>
                            <option value="no" {{ request('has_voted') == 'no' ? 'selected' : '' }}>Not Voted</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-table"></i> Users List ({{ $users->total() }} users)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Course</th>
                                <th>Year Level</th>
                                <th>Voting Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td><strong>{{ $user->student_id }}</strong></td>
                                    <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->user_type === 'admin')
                                            <span class="badge bg-danger">
                                                <i class="bi bi-shield-fill"></i> Admin
                                            </span>
                                        @else
                                            <span class="badge bg-primary">
                                                <i class="bi bi-person"></i> Student
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $user->course ?? 'N/A' }}</td>
                                    <td>{{ $user->year_level ?? 'N/A' }}</td>
                                    <td>
                                        @if($user->has_voted)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle-fill"></i> Voted
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-x-circle"></i> Not Voted
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.users.edit', $user) }}" 
                                               class="btn btn-warning"
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
                                                    <button type="submit" class="btn btn-info" title="Reset Vote">
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
                                                        class="btn btn-danger"
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
                                    <td colspan="8" class="text-center py-4">
                                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2">No users found</p>
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
    </div>

    <!-- Reset All Votes Modal -->
    <div class="modal fade" id="resetAllVotesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle"></i> Reset All Votes
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.users.reset-all-votes') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to reset voting status for <strong>ALL students</strong>?</p>
                        <p class="text-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i> 
                            This action will allow all students to vote again. This cannot be undone!
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset All Votes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
