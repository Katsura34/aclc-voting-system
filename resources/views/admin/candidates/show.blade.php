<x-admin-layout title="Candidate Details">
    <x-slot name="styles">
        <style>
            .candidate-header {
                background: linear-gradient(135deg, {{ $candidate->party->color ?? '#003366' }}15 0%, {{ $candidate->party->color ?? '#003366' }}30 100%);
                border-left: 5px solid {{ $candidate->party->color ?? '#003366' }};
                padding: 30px;
                border-radius: 10px;
                margin-bottom: 30px;
            }
            .candidate-avatar {
                width: 150px;
                height: 150px;
                border-radius: 50%;
                background: {{ $candidate->party->color ?? 'var(--aclc-blue)' }};
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 4rem;
                font-weight: bold;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            }
            .info-card {
                background: white;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                margin-bottom: 20px;
            }
            .info-label {
                font-weight: bold;
                color: var(--aclc-blue);
                margin-bottom: 5px;
            }
            .party-logo {
                width: 40px;
                height: 40px;
                object-fit: contain;
                background: white;
                padding: 5px;
                border-radius: 5px;
            }
        </style>
    </x-slot>

    <div class="container-fluid">
        <!-- Header Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-person"></i> Candidate Details</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.candidates.edit', $candidate) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit Candidate
                </a>
                <a href="{{ route('admin.candidates.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Candidates
                </a>
            </div>
        </div>

        <!-- Candidate Header -->
        <div class="candidate-header">
            <div class="row align-items-center">
                <div class="col-auto">
                    @if($candidate->photo_path)
                        <img src="{{ asset('storage/' . $candidate->photo_path) }}" 
                             alt="{{ $candidate->full_name }}"
                             style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                    @else
                        <div class="candidate-avatar">
                            {{ substr($candidate->first_name, 0, 1) }}{{ substr($candidate->last_name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div class="col">
                    <h1 class="mb-2">{{ $candidate->full_name }}</h1>
                    <div class="mb-2">
                        <span class="badge bg-primary me-2" style="font-size: 1rem;">
                            <i class="bi bi-award"></i> {{ $candidate->position->name ?? 'N/A' }}
                        </span>
                        @if($candidate->party)
                            <span class="badge me-2" style="background: {{ $candidate->party->color }}; font-size: 1rem;">
                                @if($candidate->party->logo)
                                    <img src="{{ asset('storage/' . $candidate->party->logo) }}" 
                                         alt="{{ $candidate->party->acronym }}"
                                         class="party-logo me-1">
                                @endif
                                {{ $candidate->party->name }} ({{ $candidate->party->acronym }})
                            </span>
                        @endif
                    </div>
                    <p class="mb-0 text-muted">
                        <i class="bi bi-calendar-event"></i> 
                        Election: {{ $candidate->election->title ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Biography -->
                <div class="info-card">
                    <h4 class="mb-3"><i class="bi bi-person-lines-fill"></i> Biography</h4>
                    @if($candidate->bio)
                        <p class="mb-0">{{ $candidate->bio }}</p>
                    @else
                        <p class="text-muted mb-0">No biography provided.</p>
                    @endif
                </div>

                <!-- Platform -->
                <div class="info-card">
                    <h4 class="mb-3"><i class="bi bi-megaphone-fill"></i> Campaign Platform</h4>
                    @if($candidate->platform)
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $candidate->platform }}</p>
                    @else
                        <p class="text-muted mb-0">No platform provided.</p>
                    @endif
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Quick Info -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Quick Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="info-label">Full Name</div>
                            <div>{{ $candidate->full_name }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">First Name</div>
                            <div>{{ $candidate->first_name }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Last Name</div>
                            <div>{{ $candidate->last_name }}</div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <div class="info-label">Position</div>
                            <div>
                                <span class="badge bg-primary">
                                    {{ $candidate->position->name ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Party</div>
                            <div>
                                @if($candidate->party)
                                    <span class="badge" style="background: {{ $candidate->party->color }};">
                                        {{ $candidate->party->name }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">No Party</span>
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                        <div class="info-label">Election</div>
                        <div>{{ $candidate->election->title ?? 'N/A' }}</div>
                    </div>
                        <hr>
                        <div class="mb-0">
                            <div class="info-label">Created</div>
                            <div>{{ $candidate->created_at->format('M d, Y h:i A') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Party Details -->
                @if($candidate->party)
                    <div class="card mb-3">
                        <div class="card-header text-white" style="background: {{ $candidate->party->color }};">
                            <h5 class="mb-0"><i class="bi bi-flag"></i> Party Details</h5>
                        </div>
                        <div class="card-body text-center">
                            @if($candidate->party->logo)
                                <img src="{{ asset('storage/' . $candidate->party->logo) }}" 
                                     alt="{{ $candidate->party->name }}"
                                     style="width: 100px; height: 100px; object-fit: contain; margin-bottom: 15px;">
                            @endif
                            <h5>{{ $candidate->party->name }}</h5>
                            <span class="badge mb-2" style="background: {{ $candidate->party->color }};">
                                {{ $candidate->party->acronym }}
                            </span>
                            @if($candidate->party->description)
                                <p class="text-muted small mt-2 mb-0">{{ $candidate->party->description }}</p>
                            @endif
                            <hr>
                            <a href="{{ route('admin.parties.show', $candidate->party) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View Party Details
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Statistics -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Statistics</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <h2 class="mb-0">{{ $candidate->votes()->count() }}</h2>
                            <p class="text-muted mb-0">Total Votes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('admin.candidates.edit', $candidate) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit Candidate
            </a>
            <button type="button" class="btn btn-danger" onclick="deleteCandidate()">
                <i class="bi bi-trash"></i> Delete Candidate
            </button>
            <form id="delete-form" action="{{ route('admin.candidates.destroy', $candidate) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            function deleteCandidate() {
                const votesCount = {{ $candidate->votes()->count() }};
                
                if (votesCount > 0) {
                    alert(`Cannot delete this candidate!\n\nThis candidate has ${votesCount} vote(s). Candidates with votes cannot be deleted.`);
                    return;
                }
                
                if (confirm('Are you sure you want to delete "{{ $candidate->full_name }}"?\n\nThis action cannot be undone.')) {
                    document.getElementById('delete-form').submit();
                }
            }
        </script>
    </x-slot>
</x-admin-layout>
