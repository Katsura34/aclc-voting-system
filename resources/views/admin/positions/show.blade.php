<x-admin-layout title="Position Details">
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-award"></i> Position Details</h2>
                <p class="text-muted mb-0">View position information and candidates</p>
            </div>
            <div>
                <a href="{{ route('admin.positions.edit', $position) }}" class="btn btn-warning me-2">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="{{ route('admin.positions.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Position Information -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Position Information</h5>
                    </div>
                    <div class="card-body text-center">
                        <i class="bi bi-award-fill" style="font-size: 5rem; color: var(--aclc-blue);"></i>
                        <h3 class="mt-3 mb-3">{{ $position->name }}</h3>
                        
                        @if($position->description)
                            <p class="text-muted">{{ $position->description }}</p>
                        @else
                            <p class="text-muted fst-italic">No description provided</p>
                        @endif

                        <hr>

                        <div class="row text-start">
                            <div class="col-6 mb-3">
                                <small class="text-muted">Election</small>
                                <p class="mb-0">
                                    <span class="badge bg-success">{{ $position->election->title ?? 'N/A' }}</span>
                                </p>
                            </div>
                            <div class="col-6 mb-3">
                                <small class="text-muted">Max Votes</small>
                                <p class="mb-0">
                                    <span class="badge bg-info">{{ $position->max_votes }}</span>
                                </p>
                            </div>
                            <div class="col-6 mb-3">
                                <small class="text-muted">Display Order</small>
                                <p class="mb-0">
                                    <span class="badge bg-secondary">{{ $position->display_order ?? '-' }}</span>
                                </p>
                            </div>
                            <div class="col-6 mb-3">
                                <small class="text-muted">Candidates</small>
                                <p class="mb-0">
                                    <span class="badge bg-primary">{{ $position->candidates->count() }}</span>
                                </p>
                            </div>
                        </div>

                        <hr>

                        <div class="text-start">
                            <small class="text-muted">Created</small>
                            <p class="mb-2">{{ $position->created_at->format('M d, Y h:i A') }}</p>
                            <small class="text-muted">Last Updated</small>
                            <p class="mb-0">{{ $position->updated_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Candidates List -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-people"></i> Candidates for this Position
                            <span class="badge bg-white text-dark float-end">{{ $position->candidates->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($position->candidates->isEmpty())
                            <div class="text-center py-5">
                                <i class="bi bi-people" style="font-size: 4rem; color: var(--aclc-light-blue);"></i>
                                <h5 class="mt-3">No Candidates Yet</h5>
                                <p class="text-muted">There are no candidates assigned to this position.</p>
                                <a href="{{ route('admin.candidates.create') }}" class="btn btn-primary mt-2">
                                    <i class="bi bi-plus-circle"></i> Add Candidate
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Candidate Name</th>
                                            <th>Party</th>
                                            <th>Bio</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($position->candidates as $candidate)
                                            <tr>
                                                <td>
                                                    <strong>{{ $candidate->full_name }}</strong>
                                                </td>
                                                <td>
                                                    @if($candidate->party)
                                                        <span class="badge" style="background-color: {{ $candidate->party->color }};">
                                                            {{ $candidate->party->acronym }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">No Party</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($candidate->bio)
                                                        {{ Str::limit($candidate->bio, 50) }}
                                                    @else
                                                        <span class="text-muted fst-italic">No bio</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.candidates.show', $candidate) }}" 
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Candidates by Party -->
                @if($position->candidates->isNotEmpty())
                    <div class="card mt-4">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="bi bi-diagram-3"></i> Candidates by Party</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($position->candidates->groupBy('party_id') as $partyId => $partyCandidates)
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-header" 
                                                 style="background-color: {{ $partyCandidates->first()->party->color ?? '#6c757d' }}; color: white;">
                                                <h6 class="mb-0">
                                                    {{ $partyCandidates->first()->party->name ?? 'No Party' }}
                                                    <span class="badge bg-white text-dark float-end">{{ $partyCandidates->count() }}</span>
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($partyCandidates as $candidate)
                                                        <li class="mb-2">
                                                            <i class="bi bi-person-fill"></i>
                                                            {{ $candidate->full_name }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Election Information -->
                @if($position->election)
                    <div class="card mt-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Election Information</h5>
                        </div>
                        <div class="card-body">
                            <h5>{{ $position->election->title }}</h5>
                            @if($position->election->description)
                                <p class="text-muted">{{ $position->election->description }}</p>
                            @endif
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <small class="text-muted">Start Date</small>
                                    <p>{{ \Carbon\Carbon::parse($position->election->start_date)->format('M d, Y h:i A') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">End Date</small>
                                    <p>{{ \Carbon\Carbon::parse($position->election->end_date)->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted">Status</small>
                                    <p>
                                        @if($position->election->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
