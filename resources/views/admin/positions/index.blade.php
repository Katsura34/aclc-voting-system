<x-admin-layout title="Positions Management">
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-award"></i> Positions Management</h2>
                <p class="text-muted mb-0">Manage election positions and their settings</p>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-file-earmark-arrow-up"></i> Import CSV
                </button>
                <a href="{{ route('admin.positions.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Position
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

        @if(session('import_errors'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <strong>Import Errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach(session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.positions.index') }}" method="GET" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" 
                                       class="form-control" 
                                       name="search" 
                                       placeholder="Search by position name..." 
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="election_id" id="election_id">
                                <option value="">All Elections</option>
                                @foreach($elections as $election)
                                    <option value="{{ $election->id }}" 
                                            {{ request('election_id') == $election->id ? 'selected' : '' }}>
                                        {{ $election->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Active Filters -->
        @if(request()->hasAny(['search', 'election_id']))
            <div class="mb-3">
                <span class="badge bg-secondary me-2">Active Filters:</span>
                @if(request('search'))
                    <span class="badge bg-info me-2">
                        Search: "{{ request('search') }}"
                        <a href="{{ route('admin.positions.index', array_merge(request()->except('search'))) }}" 
                           class="text-white ms-1" style="text-decoration: none;">×</a>
                    </span>
                @endif
                @if(request('election_id'))
                    <span class="badge bg-info me-2">
                        Election: {{ $elections->find(request('election_id'))->title ?? 'Unknown' }}
                        <a href="{{ route('admin.positions.index', array_merge(request()->except('election_id'))) }}" 
                           class="text-white ms-1" style="text-decoration: none;">×</a>
                    </span>
                @endif
                <a href="{{ route('admin.positions.index') }}" class="badge bg-danger">
                    Clear All
                </a>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-award-fill" style="font-size: 2.5rem; color: var(--aclc-blue);"></i>
                        <h3 class="mt-2 mb-0">{{ $positions->count() }}</h3>
                        <p class="text-muted mb-0">Total Positions</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-calendar-event-fill" style="font-size: 2.5rem; color: #28a745;"></i>
                        <h3 class="mt-2 mb-0">{{ $positions->pluck('election_id')->unique()->count() }}</h3>
                        <p class="text-muted mb-0">Elections</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-people-fill" style="font-size: 2.5rem; color: var(--aclc-red);"></i>
                        <h3 class="mt-2 mb-0">{{ $positions->sum(function($p) { return $p->candidates->count(); }) }}</h3>
                        <p class="text-muted mb-0">Total Candidates</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-check-circle-fill" style="font-size: 2.5rem; color: #ffc107;"></i>
                        <h3 class="mt-2 mb-0">{{ $positions->avg('max_votes') ?? 0 }}</h3>
                        <p class="text-muted mb-0">Avg Max Votes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Positions List -->
        @if($positions->isEmpty())
            <div class="card text-center py-5">
                <div class="card-body">
                    <i class="bi bi-award" style="font-size: 4rem; color: var(--aclc-light-blue);"></i>
                    @if(request()->hasAny(['search', 'election_id']))
                        <h4 class="mt-3">No Positions Found</h4>
                        <p class="text-muted">No positions match your search criteria.</p>
                        <a href="{{ route('admin.positions.index') }}" class="btn btn-outline-primary mt-2">
                            <i class="bi bi-x-circle"></i> Clear Filters
                        </a>
                    @else
                        <h4 class="mt-3">No Positions Found</h4>
                        <p class="text-muted">Get started by adding your first position.</p>
                        <a href="{{ route('admin.positions.create') }}" class="btn btn-primary mt-2">
                            <i class="bi bi-plus-circle"></i> Add First Position
                        </a>
                    @endif
                </div>
            </div>
        @else
            <!-- Results Counter -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul"></i> Positions List 
                    <span class="badge bg-primary">{{ $positions->count() }} {{ Str::plural('result', $positions->count()) }}</span>
                </h5>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Position Name</th>
                                    <th>Election</th>
                                    <th>Max Votes</th>
                                    <th>Display Order</th>
                                    <th>Candidates</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($positions as $position)
                                <tr>
                                    <td>
                                        <strong>{{ $position->name }}</strong>
                                        @if($position->description)
                                            <br><small class="text-muted">{{ Str::limit($position->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ $position->election->title ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $position->max_votes }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $position->display_order ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $position->candidates->count() }} candidates</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.positions.show', $position) }}" 
                                               class="btn btn-sm btn-outline-info"
                                               title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.positions.edit', $position) }}" 
                                               class="btn btn-sm btn-outline-warning"
                                               title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    onclick="deletePosition({{ $position->id }}, '{{ $position->name }}')"
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Positions by Election -->
            <h5 class="mt-5 mb-3"><i class="bi bi-calendar-event"></i> Positions by Election</h5>
            <div class="row">
                @foreach($positions->groupBy('election_id') as $electionId => $electionPositions)
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    {{ $electionPositions->first()->election->title ?? 'Unknown Election' }}
                                    <span class="badge bg-white text-dark float-end">{{ $electionPositions->count() }}</span>
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    @foreach($electionPositions->sortBy('display_order') as $position)
                                        <li class="mb-2">
                                            <i class="bi bi-award-fill text-warning"></i>
                                            <strong>{{ $position->name }}</strong>
                                            <br>
                                            <small class="text-muted ms-3">
                                                Max {{ $position->max_votes }} vote(s) • 
                                                {{ $position->candidates->count() }} candidate(s)
                                            </small>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Import CSV Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="bi bi-file-earmark-arrow-up"></i> Import Positions from CSV
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.positions.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>CSV Format:</strong> Your CSV file must include these columns:
                            <ul class="mb-0 mt-2">
                                <li><strong>name</strong> - Position name (required)</li>
                                <li><strong>description</strong> - Position description (optional)</li>
                                <li><strong>election_id</strong> - Election ID number (required)</li>
                                <li><strong>max_votes</strong> - Maximum votes allowed (required)</li>
                                <li><strong>display_order</strong> - Display order number (optional)</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <label for="csv_file" class="form-label fw-bold">Select CSV File</label>
                            <input type="file" 
                                   class="form-control @error('csv_file') is-invalid @enderror" 
                                   id="csv_file" 
                                   name="csv_file" 
                                   accept=".csv,.txt"
                                   required>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Accepted formats: .csv, .txt (Max: 2MB)</small>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('admin.positions.download-template') }}" class="btn btn-outline-primary">
                                <i class="bi bi-download"></i> Download Sample Template
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-upload"></i> Import Positions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden delete forms for individual positions -->
    @foreach($positions as $position)
        <form id="delete-form-{{ $position->id }}" 
              action="{{ route('admin.positions.destroy', $position) }}" 
              method="POST" 
              style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    <x-slot name="scripts">
        <script>
            function deletePosition(id, name) {
                if (confirm(`Are you sure you want to delete "${name}"?\n\nThis action cannot be undone and will fail if the position has candidates.`)) {
                    document.getElementById('delete-form-' + id).submit();
                }
            }
        </script>
    </x-slot>
</x-admin-layout>
