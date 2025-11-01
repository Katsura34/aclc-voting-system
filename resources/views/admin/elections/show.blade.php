<x-admin-layout title="{{ $election->title }}">
    <x-slot name="styles">
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--aclc-blue) 0%, var(--aclc-light-blue) 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            flex: 0 0 200px;
            font-weight: 600;
            color: #666;
        }

        .info-value {
            flex: 1;
            color: var(--aclc-blue);
        }

        .position-card {
            background: #f8f9fa;
            border-left: 4px solid var(--aclc-red);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .position-title {
            color: var(--aclc-blue);
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }

        .candidate-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }

        .candidate-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .candidate-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--aclc-blue) 0%, var(--aclc-light-blue) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .candidate-info {
            flex: 1;
        }

        .candidate-name {
            font-weight: 600;
            color: var(--aclc-blue);
            margin-bottom: 3px;
        }

        .candidate-party {
            font-size: 0.85rem;
            color: #666;
        }
    </x-slot>

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
        
        <div class="info-row">
            <div class="info-label">Allow Abstain</div>
            <div class="info-value">
                @if($election->allow_abstain)
                    <span class="badge bg-success">Yes</span>
                @else
                    <span class="badge bg-secondary">No</span>
                @endif
            </div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Show Live Results</div>
            <div class="info-value">
                @if($election->show_live_results)
                    <span class="badge bg-success">Yes</span>
                @else
                    <span class="badge bg-secondary">No</span>
                @endif
            </div>
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
