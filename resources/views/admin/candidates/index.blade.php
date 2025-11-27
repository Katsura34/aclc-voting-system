<x-admin-layout title="Candidates Management">
    <x-slot name="styles">
        <style>
            /* ===== CANDIDATES PAGE STYLES ===== */
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

            .stats-row {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 1rem;
                margin-bottom: 1.5rem;
            }

            .stat-card {
                background: white;
                border-radius: var(--radius-md);
                border: 1px solid var(--gray-200);
                padding: 1.25rem;
                text-align: center;
            }

            .stat-card-icon {
                font-size: 1.75rem;
                margin-bottom: 0.5rem;
            }

            .stat-card-icon.blue { color: var(--aclc-blue); }
            .stat-card-icon.red { color: var(--aclc-red); }
            .stat-card-icon.green { color: var(--success); }
            .stat-card-icon.orange { color: var(--warning); }

            .stat-card-value {
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--gray-800);
                margin-bottom: 0.25rem;
            }

            .stat-card-label {
                font-size: 0.75rem;
                color: var(--gray-500);
                text-transform: uppercase;
                letter-spacing: 0.025em;
            }

            .party-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
                padding: 0.375rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
                color: white;
            }

            .party-badge img {
                width: 16px;
                height: 16px;
                object-fit: contain;
                background: white;
                border-radius: 3px;
                padding: 1px;
            }

            .position-badge {
                display: inline-flex;
                align-items: center;
                background: #dbeafe;
                color: #1e40af;
                padding: 0.375rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
            }

            .candidate-avatar {
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

            .candidate-info {
                flex: 1;
                min-width: 0;
            }

            .candidate-name {
                font-weight: 600;
                color: var(--gray-800);
                font-size: 0.9375rem;
            }

            .candidate-subtitle {
                font-size: 0.75rem;
                color: var(--gray-500);
            }

            .active-filters {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid var(--gray-200);
            }

            .active-filters-label {
                font-size: 0.8125rem;
                font-weight: 600;
                color: var(--gray-600);
                margin-right: 0.5rem;
            }

            .filter-tag {
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
                padding: 0.25rem 0.625rem;
                background: var(--gray-100);
                border-radius: 9999px;
                font-size: 0.75rem;
                color: var(--gray-700);
            }

            .filter-tag a {
                display: flex;
                align-items: center;
                color: var(--gray-500);
                text-decoration: none;
            }

            .filter-tag a:hover {
                color: var(--danger);
            }

            .party-group-card {
                background: white;
                border-radius: var(--radius-md);
                border: 1px solid var(--gray-200);
                overflow: hidden;
                margin-bottom: 1rem;
            }

            .party-group-header {
                padding: 0.875rem 1rem;
                color: white;
                font-weight: 600;
                font-size: 0.875rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .party-group-body {
                padding: 0.75rem;
            }

            .party-member {
                display: flex;
                align-items: flex-start;
                gap: 0.5rem;
                padding: 0.5rem;
                font-size: 0.8125rem;
            }

            .party-member i {
                color: var(--gray-400);
                margin-top: 2px;
            }

            .party-member-name {
                font-weight: 600;
                color: var(--gray-700);
            }

            .party-member-position {
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
                margin-bottom: 1.5rem;
            }

            .results-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1rem;
            }

            .results-count {
                font-size: 0.9375rem;
                font-weight: 600;
                color: var(--gray-700);
            }

            .results-count .badge {
                font-weight: 500;
            }
        </style>
    </x-slot>

    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-people-fill"></i>
                    Candidates Management
                </h1>
                <p class="page-subtitle">Manage election candidates</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-file-earmark-arrow-up"></i>
                    Import CSV
                </button>
                <a href="{{ route('admin.candidates.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i>
                    Add Candidate
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

    @if(session('import_errors'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Import Errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.candidates.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">
                        <i class="bi bi-search"></i> Search
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search by name...">
                </div>

                <div class="col-md-3">
                    <label for="election_id" class="form-label">
                        <i class="bi bi-calendar-event"></i> Election
                    </label>
                    <select class="form-select" id="election_id" name="election_id">
                        <option value="">All Elections</option>
                        @foreach($elections as $election)
                            <option value="{{ $election->id }}" 
                                    {{ request('election_id') == $election->id ? 'selected' : '' }}>
                                {{ $election->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="position_id" class="form-label">
                        <i class="bi bi-award"></i> Position
                    </label>
                    <select class="form-select" id="position_id" name="position_id">
                        <option value="">All Positions</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->id }}" 
                                    data-election="{{ $position->election_id }}"
                                    {{ request('position_id') == $position->id ? 'selected' : '' }}>
                                {{ $position->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="party_id" class="form-label">
                        <i class="bi bi-flag"></i> Party
                    </label>
                    <select class="form-select" id="party_id" name="party_id">
                        <option value="">All Parties</option>
                        @foreach($parties as $party)
                            <option value="{{ $party->id }}" 
                                    {{ request('party_id') == $party->id ? 'selected' : '' }}>
                                {{ $party->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('admin.candidates.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>

            @if(request()->hasAny(['search', 'election_id', 'position_id', 'party_id']))
                <div class="active-filters">
                    <span class="active-filters-label">Active Filters:</span>
                    @if(request('search'))
                        <span class="filter-tag">
                            Search: "{{ request('search') }}"
                            <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}"><i class="bi bi-x"></i></a>
                        </span>
                    @endif
                    @if(request('election_id'))
                        <span class="filter-tag">
                            Election: {{ $elections->find(request('election_id'))->name ?? 'N/A' }}
                            <a href="{{ request()->fullUrlWithQuery(['election_id' => null]) }}"><i class="bi bi-x"></i></a>
                        </span>
                    @endif
                    @if(request('position_id'))
                        <span class="filter-tag">
                            Position: {{ $positions->find(request('position_id'))->name ?? 'N/A' }}
                            <a href="{{ request()->fullUrlWithQuery(['position_id' => null]) }}"><i class="bi bi-x"></i></a>
                        </span>
                    @endif
                    @if(request('party_id'))
                        <span class="filter-tag">
                            Party: {{ $parties->find(request('party_id'))->name ?? 'N/A' }}
                            <a href="{{ request()->fullUrlWithQuery(['party_id' => null]) }}"><i class="bi bi-x"></i></a>
                        </span>
                    @endif
                </div>
            @endif
        </form>
    </div>

    <!-- Statistics -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-card-icon blue">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-card-value">{{ $candidates->count() }}</div>
            <div class="stat-card-label">Total Candidates</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon red">
                <i class="bi bi-flag-fill"></i>
            </div>
            <div class="stat-card-value">{{ $candidates->pluck('party_id')->unique()->count() }}</div>
            <div class="stat-card-label">Parties Represented</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon orange">
                <i class="bi bi-award-fill"></i>
            </div>
            <div class="stat-card-value">{{ $candidates->pluck('position_id')->unique()->count() }}</div>
            <div class="stat-card-label">Positions</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon green">
                <i class="bi bi-calendar-event-fill"></i>
            </div>
            <div class="stat-card-value">{{ $candidates->pluck('position.election_id')->unique()->count() }}</div>
            <div class="stat-card-label">Elections</div>
        </div>
    </div>

    <!-- Candidates List -->
    @if($candidates->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bi bi-people"></i>
            </div>
            @if(request()->hasAny(['search', 'election_id', 'position_id', 'party_id']))
                <h3>No Candidates Found</h3>
                <p>No candidates match your search criteria.</p>
                <a href="{{ route('admin.candidates.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            @else
                <h3>No Candidates Yet</h3>
                <p>Get started by adding your first candidate.</p>
                <a href="{{ route('admin.candidates.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Candidate
                </a>
            @endif
        </div>
    @else
        <!-- Results Header -->
        <div class="results-header">
            <div class="results-count">
                <i class="bi bi-list-ul"></i>
                Candidates List
                <span class="badge bg-primary ms-2">{{ $candidates->count() }} {{ Str::plural('result', $candidates->count()) }}</span>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Candidate</th>
                                <th>Position</th>
                                <th>Party</th>
                                <th>Election</th>
                                <th>Bio</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($candidates as $candidate)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="candidate-avatar">
                                                {{ strtoupper(substr($candidate->first_name, 0, 1)) }}
                                            </div>
                                            <div class="candidate-info">
                                                <div class="candidate-name">{{ $candidate->full_name }}</div>
                                                <div class="candidate-subtitle">
                                                    {{ $candidate->first_name }} {{ $candidate->last_name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="position-badge">
                                            {{ $candidate->position->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($candidate->party)
                                            <span class="party-badge" style="background: {{ $candidate->party->color }};">
                                                @if($candidate->party->logo)
                                                    <img src="{{ asset('storage/' . $candidate->party->logo) }}" 
                                                         alt="{{ $candidate->party->acronym }}">
                                                @endif
                                                {{ $candidate->party->acronym }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">No Party</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $candidate->position->election->name ?? 'N/A' }}
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ Str::limit($candidate->bio, 50) }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.candidates.show', $candidate) }}" 
                                               class="btn btn-sm btn-info btn-icon"
                                               data-bs-toggle="tooltip"
                                               title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.candidates.edit', $candidate) }}" 
                                               class="btn btn-sm btn-warning btn-icon"
                                               data-bs-toggle="tooltip"
                                               title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger btn-icon" 
                                                    onclick="deleteCandidate({{ $candidate->id }}, '{{ $candidate->full_name }}')"
                                                    data-bs-toggle="tooltip"
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

        <!-- Hidden delete forms -->
        @foreach($candidates as $candidate)
            <form id="delete-form-{{ $candidate->id }}" 
                  action="{{ route('admin.candidates.destroy', $candidate) }}" 
                  method="POST" 
                  style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        <!-- Candidates by Party -->
        <h4 class="section-title mt-4">
            <i class="bi bi-flag"></i> Candidates by Party
        </h4>
        <div class="row">
            @foreach($candidates->groupBy('party.name') as $partyName => $partyCandidates)
                <div class="col-md-6 col-lg-4">
                    <div class="party-group-card">
                        <div class="party-group-header" style="background: {{ $partyCandidates->first()->party->color ?? '#6c757d' }};">
                            <span>{{ $partyName }}</span>
                            <span class="badge bg-white text-dark">{{ $partyCandidates->count() }}</span>
                        </div>
                        <div class="party-group-body">
                            @foreach($partyCandidates as $candidate)
                                <div class="party-member">
                                    <i class="bi bi-person-fill"></i>
                                    <div>
                                        <div class="party-member-name">{{ $candidate->full_name }}</div>
                                        <div class="party-member-position">{{ $candidate->position->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Import CSV Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="bi bi-file-earmark-arrow-up text-success me-2"></i>
                        Import Candidates from CSV
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.candidates.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <strong>CSV Format:</strong> Your CSV file must include these columns:
                            <ul class="mb-0 mt-2">
                                <li><strong>first_name</strong> - First name (required)</li>
                                <li><strong>last_name</strong> - Last name (required)</li>
                                <li><strong>middle_name</strong> - Middle name (optional)</li>
                                <li><strong>bio</strong> - Biography (optional)</li>
                                <li><strong>position_id</strong> - Position ID number (required)</li>
                                <li><strong>party_id</strong> - Party ID number (required)</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Select CSV File <span class="text-danger">*</span></label>
                            <input type="file" 
                                   class="form-control @error('csv_file') is-invalid @enderror" 
                                   id="csv_file" 
                                   name="csv_file" 
                                   accept=".csv,.txt"
                                   required>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text">Accepted formats: .csv, .txt (Max: 2MB)</small>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('admin.candidates.download-template') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-download"></i> Download Sample Template
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-upload"></i> Import Candidates
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            function deleteCandidate(id, name) {
                if (confirm(`Are you sure you want to delete "${name}"?\n\nThis action cannot be undone and will fail if the candidate has votes.`)) {
                    document.getElementById('delete-form-' + id).submit();
                }
            }

            // Election filter affects position dropdown
            document.getElementById('election_id').addEventListener('change', function() {
                const selectedElection = this.value;
                const positionSelect = document.getElementById('position_id');
                const positionOptions = positionSelect.querySelectorAll('option');
                
                positionOptions.forEach(option => {
                    if (option.value === '') {
                        option.style.display = 'block';
                        return;
                    }
                    
                    if (selectedElection === '' || option.dataset.election === selectedElection) {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'none';
                    }
                });
                
                const selectedOption = positionSelect.options[positionSelect.selectedIndex];
                if (selectedOption && selectedOption.style.display === 'none') {
                    positionSelect.value = '';
                }
            });

            document.addEventListener('DOMContentLoaded', function() {
                const electionFilter = document.getElementById('election_id');
                if (electionFilter.value) {
                    electionFilter.dispatchEvent(new Event('change'));
                }
            });
        </script>
    </x-slot>
</x-admin-layout>
