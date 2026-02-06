<x-admin-layout title="Candidates Management">
    <x-slot name="styles">
        <style>
            .candidate-card {
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .candidate-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            }
            .party-badge {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 5px 12px;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 500;
                color: white;
            }
            .position-badge {
                background: var(--aclc-light-blue);
                color: white;
                padding: 5px 12px;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 500;
            }
            .filter-section {
                background: white;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                margin-bottom: 20px;
            }
        </style>
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-people"></i> Candidates Management</h2>
                <p class="text-muted mb-0">Manage election candidates</p>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-file-earmark-arrow-up"></i> Import CSV
                </button>
                <a href="{{ route('admin.candidates.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New Candidate
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
                <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
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

        <!-- Search and Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('admin.candidates.index') }}" id="filterForm">
                <div class="row g-3">
                    <!-- Search -->
                    <div class="col-md-3">
                        <label for="search" class="form-label fw-bold">
                            <i class="bi bi-search"></i> Search
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by name...">
                    </div>

                    <!-- Election Filter -->
                    <div class="col-md-3">
                        <label for="election_id" class="form-label fw-bold">
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

                    <!-- Position Filter -->
                    <div class="col-md-2">
                        <label for="position_id" class="form-label fw-bold">
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

                    <!-- Party Filter -->
                    <div class="col-md-2">
                        <label for="party_id" class="form-label fw-bold">
                            <i class="bi bi-flag"></i> Party
                        </label>
                        <select class="form-select" id="party_id" name="party_id">
                            <option value="">All Parties</option>
                            @foreach($parties as $party)
                                <option value="{{ $party->id }}" 
                                        {{ request('party_id') == $party->id ? 'selected' : '' }}>
                                    {{ $party->name }} ({{ $party->acronym }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100 mb-1">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('admin.candidates.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    </div>
                </div>
            </form>

            <!-- Active Filters Display -->
            @if(request()->hasAny(['search', 'election_id', 'position_id', 'party_id']))
                <div class="mt-3">
                    <strong>Active Filters:</strong>
                    @if(request('search'))
                        <span class="badge bg-primary">
                            Search: "{{ request('search') }}"
                            <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" 
                               class="text-white text-decoration-none ms-1">×</a>
                        </span>
                    @endif
                    @if(request('election_id'))
                        <span class="badge bg-info">
                            Election: {{ $elections->find(request('election_id'))->name ?? 'N/A' }}
                            <a href="{{ request()->fullUrlWithQuery(['election_id' => null]) }}" 
                               class="text-white text-decoration-none ms-1">×</a>
                        </span>
                    @endif
                    @if(request('position_id'))
                        <span class="badge bg-success">
                            Position: {{ $positions->find(request('position_id'))->name ?? 'N/A' }}
                            <a href="{{ request()->fullUrlWithQuery(['position_id' => null]) }}" 
                               class="text-white text-decoration-none ms-1">×</a>
                        </span>
                    @endif
                    @if(request('party_id'))
                        <span class="badge bg-warning text-dark">
                            Party: {{ $parties->find(request('party_id'))->name ?? 'N/A' }}
                            <a href="{{ request()->fullUrlWithQuery(['party_id' => null]) }}" 
                               class="text-dark text-decoration-none ms-1">×</a>
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-people-fill" style="font-size: 2.5rem; color: var(--aclc-blue);"></i>
                        <h3 class="mt-2 mb-0">{{ $candidates->count() }}</h3>
                        <p class="text-muted mb-0">Total Candidates</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-flag-fill" style="font-size: 2.5rem; color: var(--aclc-light-blue);"></i>
                        <h3 class="mt-2 mb-0">{{ $candidates->pluck('party_id')->unique()->count() }}</h3>
                        <p class="text-muted mb-0">Parties Represented</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-award-fill" style="font-size: 2.5rem; color: var(--aclc-red);"></i>
                        <h3 class="mt-2 mb-0">{{ $candidates->pluck('position_id')->unique()->count() }}</h3>
                        <p class="text-muted mb-0">Positions</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-calendar-event-fill" style="font-size: 2.5rem; color: #28a745;"></i>
                        <h3 class="mt-2 mb-0">{{ $candidates->pluck('position.election_id')->unique()->count() }}</h3>
                        <p class="text-muted mb-0">Elections</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Candidates List -->
        @if($candidates->isEmpty())
            <div class="card text-center py-5">
                <div class="card-body">
                    <i class="bi bi-people" style="font-size: 4rem; color: var(--aclc-light-blue);"></i>
                    @if(request()->hasAny(['search', 'election_id', 'position_id', 'party_id']))
                        <h4 class="mt-3">No Candidates Found</h4>
                        <p class="text-muted">No candidates match your search criteria.</p>
                        <a href="{{ route('admin.candidates.index') }}" class="btn btn-outline-primary mt-2">
                            <i class="bi bi-x-circle"></i> Clear Filters
                        </a>
                    @else
                        <h4 class="mt-3">No Candidates Found</h4>
                        <p class="text-muted">Get started by adding your first candidate.</p>
                        <a href="{{ route('admin.candidates.create') }}" class="btn btn-primary mt-2">
                            <i class="bi bi-plus-circle"></i> Add First Candidate
                        </a>
                    @endif
                </div>
            </div>
        @else
            <!-- Results Counter -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul"></i> Candidates List 
                    <span class="badge bg-primary">{{ $candidates->count() }} {{ Str::plural('result', $candidates->count()) }}</span>
                </h5>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Candidate Name</th>
                                        <th>Position</th>
                                        <th>Party</th>
                                        <th>Election</th>
                                        <th>Bio</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($candidates as $candidate)
                                    <tr class="candidate-card">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if($candidate->photo_path)
                                                    <img src="{{ asset('storage/' . $candidate->photo_path) }}" 
                                                         alt="{{ $candidate->full_name }}"
                                                         style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                                @else
                                                    <i class="bi bi-person-circle" style="font-size: 2rem; color: var(--aclc-blue);"></i>
                                                @endif
                                                <div>
                                                    <strong class="d-block">{{ $candidate->full_name }}</strong>
                                                    <small class="text-muted">
                                                        {{ $candidate->first_name }} {{ $candidate->last_name }}
                                                    </small>
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
                                                             alt="{{ $candidate->party->acronym }}"
                                                             style="width: 20px; height: 20px; object-fit: contain; background: white; border-radius: 3px; padding: 2px;">
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
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.candidates.show', $candidate) }}" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.candidates.edit', $candidate) }}" 
                                                   class="btn btn-sm btn-outline-warning"
                                                   title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteCandidate({{ $candidate->id }}, '{{ $candidate->full_name }}')"
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

            <!-- Hidden delete forms for individual candidates (outside bulk form) -->
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
            <div class="row mt-4">
                <div class="col-12">
                    <h4 class="mb-3"><i class="bi bi-flag"></i> Candidates by Party</h4>
                </div>
                @foreach($candidates->groupBy('party.name') as $partyName => $partyCandidates)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card">
                            <div class="card-header" style="background: {{ $partyCandidates->first()->party->color ?? '#6c757d' }}; color: white;">
                                <h6 class="mb-0">
                                    {{ $partyName }}
                                    <span class="badge bg-white text-dark float-end">{{ $partyCandidates->count() }}</span>
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    @foreach($partyCandidates as $candidate)
                                        <li class="mb-2">
                                            <i class="bi bi-person-fill"></i>
                                            <strong>{{ $candidate->full_name }}</strong>
                                            <br>
                                            <small class="text-muted ms-3">{{ $candidate->position->name ?? 'N/A' }}</small>
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
                        <i class="bi bi-file-earmark-arrow-up"></i> Import Candidates from CSV
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.candidates.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="election_id" value="{{ request('election_id') }}">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>CSV Format:</strong> Your CSV file must include these columns:
                            <ul class="mb-0 mt-2">
                                <li><strong>first_name</strong> - First name (required)</li>
                                <li><strong>last_name</strong> - Last name (required)</li>
                                <li><strong>middle_name</strong> - Middle name (optional)</li>
                                <li><strong>bio</strong> - Biography (optional)</li>
                                <li><strong>position_name</strong> - Position name (required, must match existing position)</li>
                            </ul>
                            <div class="mt-2">
                                <small class="text-muted">Select the party below to apply to all imported candidates.</small>
                            </div>
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

                        <div class="mb-3">
                            <label for="import_party_id" class="form-label fw-bold">Assign Party</label>
                            <select class="form-select @error('party_id') is-invalid @enderror" 
                                    id="import_party_id" 
                                    name="party_id" 
                                    required>
                                <option value="">Select Party</option>
                                @foreach($parties as $party)
                                    <option value="{{ $party->id }}">{{ $party->name }} ({{ $party->acronym }})</option>
                                @endforeach
                            </select>
                            @error('party_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">This party will be assigned to every imported candidate.</small>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('admin.candidates.download-template') }}" class="btn btn-outline-primary">
                                <i class="bi bi-download"></i> Download Sample Template
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancel
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
                
                // Reset position if it's now hidden
                const selectedOption = positionSelect.options[positionSelect.selectedIndex];
                if (selectedOption && selectedOption.style.display === 'none') {
                    positionSelect.value = '';
                }
            });

            // Trigger on page load to filter positions based on selected election
            document.addEventListener('DOMContentLoaded', function() {
                const electionFilter = document.getElementById('election_id');
                if (electionFilter.value) {
                    electionFilter.dispatchEvent(new Event('change'));
                }
            });

            // Auto-submit on filter change (optional - uncomment if you want instant filtering)
            /*
            document.querySelectorAll('#filterForm select').forEach(select => {
                select.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            });
            */
        </script>
    </x-slot>
</x-admin-layout>
