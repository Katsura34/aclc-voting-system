<x-admin-layout title="{{ $election->title }}">
  @vite([])

    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 class="page-title">{{ $election->title }}</h1>
                <span class="status-badge {{ $election->is_active ? 'status-active' : 'status-inactive' }}">
                    @if($election->is_active)
                        <i class="bi bi-check-circle"></i> Active
                    @else
                        <i class="bi bi-x-circle"></i> Inactive
                    @endif
                </span>
            </div>
            <div>
                <a href="{{ route('admin.elections.edit', $election) }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="{{ route('admin.elections.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Voting Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $election->positions->count() }}</div>
            <div class="stat-label">Positions</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $election->candidates->count() }}</div>
            <div class="stat-label">Candidates</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $votedCount }}</div>
            <div class="stat-label">Voters Participated</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $totalVoters > 0 ? number_format(($votedCount / $totalVoters) * 100, 1) : 0 }}%</div>
            <div class="stat-label">Turnout Rate</div>
        </div>
    </div>

    <!-- Election Details -->
    <div class="card">
        <h2 class="section-title"><i class="bi bi-info-circle"></i> Election Details</h2>
        
        @if($election->description)
            <div class="info-row">
                <div class="info-label">Description</div>
                <div class="info-value">{{ $election->description }}</div>
            </div>
        @endif
        
        <div class="info-row">
            <div class="info-label">Start Date</div>
            <div class="info-value">{{ $election->start_date->format('F d, Y - h:i A') }}</div>
        </div>
        
        <div class="info-row">
            <div class="info-label">End Date</div>
            <div class="info-value">{{ $election->end_date->format('F d, Y - h:i A') }}</div>
        </div>
    </div>

    <!-- Positions and Candidates -->
    <div class="card">
        <h2 class="section-title"><i class="bi bi-award"></i> Positions & Candidates</h2>
        
        @if($election->positions->count() > 0)
            @foreach($election->positions as $position)
                <div class="position-card">
                    <div class="position-title">
                        {{ $position->name }}
                        <small class="text-muted">(Max Winners: {{ $position->max_winners }})</small>
                    </div>
                    
                    @if($position->candidates->count() > 0)
                        <div class="candidate-list">
                            @foreach($position->candidates as $candidate)
                                <div class="candidate-item">
                                    <div class="candidate-avatar">
                                        {{ strtoupper(substr($candidate->first_name, 0, 1) . substr($candidate->last_name, 0, 1)) }}
                                    </div>
                                    <div class="candidate-info">
                                        <div class="candidate-name">{{ $candidate->full_name }}</div>
                                        @if($candidate->party)
                                            <div class="candidate-party">
                                                <i class="bi bi-flag"></i> {{ $candidate->party->name }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No candidates assigned to this position yet.</p>
                    @endif
                </div>
            @endforeach
        @else
            <p class="text-muted">No positions created for this election yet.</p>
        @endif
    </div>
</x-admin-layout>
