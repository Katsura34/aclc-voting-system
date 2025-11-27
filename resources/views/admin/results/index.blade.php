<x-admin-layout title="Election Results">
    <x-slot name="styles">
        <style>
            /* ===== RESULTS PAGE STYLES ===== */
            .selector-card {
                background: white;
                border-radius: var(--radius-lg);
                border: 1px solid var(--gray-200);
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .election-info-banner {
                background: linear-gradient(135deg, var(--aclc-blue) 0%, var(--aclc-light-blue) 100%);
                border-radius: var(--radius-lg);
                padding: 1.5rem;
                color: white;
                margin-bottom: 1.5rem;
            }

            .election-info-banner h2 {
                font-size: 1.5rem;
                font-weight: 700;
                margin: 0 0 0.5rem 0;
            }

            .election-info-banner p {
                margin: 0;
                opacity: 0.9;
            }

            .election-info-banner hr {
                border-color: rgba(255, 255, 255, 0.2);
                margin: 1rem 0;
            }

            .election-meta-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
                gap: 1rem;
            }

            .election-meta-item label {
                font-size: 0.75rem;
                opacity: 0.75;
                text-transform: uppercase;
                letter-spacing: 0.025em;
            }

            .election-meta-item span {
                font-weight: 600;
                font-size: 0.9375rem;
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
            .stat-card-icon.green { color: var(--success); }
            .stat-card-icon.yellow { color: var(--warning); }
            .stat-card-icon.red { color: var(--aclc-red); }

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

            .result-card {
                background: white;
                border-radius: var(--radius-lg);
                border: 1px solid var(--gray-200);
                margin-bottom: 1.5rem;
                overflow: hidden;
            }

            .result-card-header {
                background: var(--gray-800);
                color: white;
                padding: 1rem 1.5rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .result-card-header h4 {
                font-size: 1rem;
                font-weight: 600;
                margin: 0;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .result-card-header .total-votes {
                background: rgba(255, 255, 255, 0.15);
                padding: 0.375rem 0.75rem;
                border-radius: var(--radius-sm);
                font-size: 0.8125rem;
            }

            .result-card-body {
                padding: 0;
            }

            .candidate-rank {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 0.875rem;
                flex-shrink: 0;
            }

            .rank-1 { background: #fef3c7; color: #92400e; }
            .rank-2 { background: #e5e7eb; color: #374151; }
            .rank-3 { background: #fed7aa; color: #9a3412; }
            .rank-other { background: var(--gray-100); color: var(--gray-600); }

            .winner-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
                background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
                color: #92400e;
                padding: 0.375rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 700;
                margin-left: 0.5rem;
            }

            .progress-bar-wrapper {
                background: var(--gray-100);
                border-radius: 4px;
                height: 24px;
                overflow: hidden;
                position: relative;
            }

            .progress-bar-fill {
                height: 100%;
                border-radius: 4px;
                display: flex;
                align-items: center;
                justify-content: flex-end;
                padding-right: 0.5rem;
                font-size: 0.75rem;
                font-weight: 600;
                color: white;
                min-width: fit-content;
                transition: width 0.5s ease;
            }

            .abstain-row {
                background: #fef3c7;
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

            .live-indicator {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.375rem 0.75rem;
                background: var(--success);
                color: white;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.7; }
            }

            .no-results-cell {
                padding: 3rem 1.5rem !important;
            }
        </style>
    </x-slot>

    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-bar-chart-fill"></i>
                    Election Results
                    @if($selectedElection && $selectedElection->is_active)
                        <span class="live-indicator ms-2" id="live-indicator">
                            <span class="spinner-border spinner-border-sm"></span>
                            LIVE
                        </span>
                    @endif
                </h1>
                <p class="page-subtitle">View voting results and statistics • Auto-refreshes every 10 seconds</p>
            </div>
            @if($selectedElection)
                <div class="d-flex gap-2">
                    <button type="button" id="toggleAutoRefresh" class="btn btn-secondary" onclick="toggleAutoRefresh()">
                        <i class="bi bi-arrow-repeat"></i>
                        <span id="autoRefreshText">Disable</span> Auto-Refresh
                    </button>
                    <a href="{{ route('admin.results.print', ['election_id' => $selectedElection->id]) }}" 
                       class="btn btn-info"
                       target="_blank">
                        <i class="bi bi-printer"></i>
                        Print
                    </a>
                    <a href="{{ route('admin.results.export', ['election_id' => $selectedElection->id]) }}" 
                       class="btn btn-success">
                        <i class="bi bi-download"></i>
                        Export CSV
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Election Selector -->
    <div class="selector-card">
        <form action="{{ route('admin.results.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label for="election_id" class="form-label">
                        <i class="bi bi-calendar-event"></i> Select Election
                    </label>
                    <select class="form-select" name="election_id" id="election_id" required>
                        <option value="">Choose an election...</option>
                        @foreach($elections as $election)
                            <option value="{{ $election->id }}" 
                                    {{ request('election_id') == $election->id ? 'selected' : '' }}>
                                {{ $election->title }} - {{ \Carbon\Carbon::parse($election->start_date)->format('M d, Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> View Results
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if($selectedElection)
        <!-- Election Info Banner -->
        <div class="election-info-banner">
            <h2>{{ $selectedElection->title }}</h2>
            <p>{{ $selectedElection->description }}</p>
            <hr>
            <div class="election-meta-grid">
                <div class="election-meta-item">
                    <label>Start Date</label><br>
                    <span>{{ \Carbon\Carbon::parse($selectedElection->start_date)->format('M d, Y h:i A') }}</span>
                </div>
                <div class="election-meta-item">
                    <label>End Date</label><br>
                    <span>{{ \Carbon\Carbon::parse($selectedElection->end_date)->format('M d, Y h:i A') }}</span>
                </div>
                <div class="election-meta-item">
                    <label>Status</label><br>
                    @if($selectedElection->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
                <div class="election-meta-item">
                    <label>Positions</label><br>
                    <span>{{ $selectedElection->positions->count() }}</span>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-card-icon blue">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-card-value">{{ number_format($totalVoters) }}</div>
                <div class="stat-card-label">Total Voters</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon green">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="stat-card-value">{{ number_format($votedCount) }}</div>
                <div class="stat-card-label">Votes Cast</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon yellow">
                    <i class="bi bi-percent"></i>
                </div>
                <div class="stat-card-value">{{ $totalVoters > 0 ? round(($votedCount / $totalVoters) * 100, 1) : 0 }}%</div>
                <div class="stat-card-label">Turnout Rate</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon red">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div class="stat-card-value">{{ count($results) }}</div>
                <div class="stat-card-label">Positions</div>
            </div>
        </div>

        <!-- Results by Position -->
        @foreach($results as $result)
            <div class="result-card">
                <div class="result-card-header">
                    <h4>
                        <i class="bi bi-award"></i>
                        {{ $result['position']->name }}
                    </h4>
                    <span class="total-votes">
                        Total Votes: {{ number_format($result['total_votes']) }}
                    </span>
                </div>
                <div class="result-card-body">
                    @if(empty($result['candidates']) && $result['abstain_votes'] == 0)
                        <div class="no-results-cell text-center">
                            <i class="bi bi-inbox" style="font-size: 2.5rem; color: var(--gray-300);"></i>
                            <p class="text-muted mt-2 mb-0">No votes cast for this position</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">Rank</th>
                                        <th>Candidate</th>
                                        <th>Party</th>
                                        <th style="width: 100px;">Votes</th>
                                        <th style="width: 40%;">Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($result['candidates'] as $index => $candidateResult)
                                        <tr>
                                            <td>
                                                <div class="candidate-rank rank-{{ $index + 1 > 3 ? 'other' : $index + 1 }}">
                                                    {{ $index + 1 }}
                                                </div>
                                            </td>
                                            <td>
                                                <strong>{{ $candidateResult['candidate']->full_name }}</strong>
                                                @if($index === 0 && $candidateResult['votes'] > 0)
                                                    <span class="winner-badge">
                                                        <i class="bi bi-trophy-fill"></i> WINNER
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($candidateResult['candidate']->party)
                                                    <span class="badge" style="background-color: {{ $candidateResult['candidate']->party->color }};">
                                                        {{ $candidateResult['candidate']->party->acronym }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">No Party</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong class="fs-5">{{ number_format($candidateResult['votes']) }}</strong>
                                            </td>
                                            <td>
                                                @php
                                                    $percentage = $result['total_votes'] > 0 
                                                        ? round(($candidateResult['votes'] / $result['total_votes']) * 100, 1) 
                                                        : 0;
                                                @endphp
                                                <div class="progress-bar-wrapper">
                                                    <div class="progress-bar-fill" 
                                                         style="width: {{ max($percentage, 8) }}%; background-color: {{ $candidateResult['candidate']->party->color ?? 'var(--aclc-blue)' }};">
                                                        {{ $percentage }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if($result['abstain_votes'] > 0)
                                        <tr class="abstain-row">
                                            <td>—</td>
                                            <td><strong>Abstain</strong></td>
                                            <td>—</td>
                                            <td><strong class="fs-5">{{ number_format($result['abstain_votes']) }}</strong></td>
                                            <td>
                                                @php
                                                    $abstainPercentage = $result['total_votes'] > 0 
                                                        ? round(($result['abstain_votes'] / $result['total_votes']) * 100, 1) 
                                                        : 0;
                                                @endphp
                                                <div class="progress-bar-wrapper">
                                                    <div class="progress-bar-fill" 
                                                         style="width: {{ max($abstainPercentage, 8) }}%; background-color: var(--warning);">
                                                        {{ $abstainPercentage }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <!-- No Election Selected -->
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bi bi-bar-chart"></i>
            </div>
            <h3>No Election Selected</h3>
            <p>Please select an election from the dropdown above to view results.</p>
        </div>
    @endif

    @push('scripts')
    <script>
        let autoRefreshEnabled = true;
        let refreshInterval = null;

        function toggleAutoRefresh() {
            autoRefreshEnabled = !autoRefreshEnabled;
            const indicator = document.getElementById('live-indicator');
            const btnText = document.getElementById('autoRefreshText');
            
            if (autoRefreshEnabled) {
                if (indicator) indicator.style.display = 'inline-flex';
                btnText.textContent = 'Disable';
                startAutoRefresh();
            } else {
                if (indicator) indicator.style.display = 'none';
                btnText.textContent = 'Enable';
                stopAutoRefresh();
            }
        }

        function startAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
            
            refreshInterval = setInterval(() => {
                if (autoRefreshEnabled) {
                    location.reload();
                }
            }, 10000);
        }

        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const electionId = document.getElementById('election_id')?.value;
            
            if (electionId && autoRefreshEnabled) {
                startAutoRefresh();
            }
        });

        window.addEventListener('beforeunload', function() {
            stopAutoRefresh();
        });
    </script>
    @endpush
</x-admin-layout>
