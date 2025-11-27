<x-admin-layout title="Admin Dashboard">
    <x-slot name="styles">
        <style>
            /* ===== DASHBOARD SPECIFIC STYLES ===== */
            .dashboard-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 1rem;
            }

            .dashboard-time {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: var(--gray-500);
                font-size: 0.875rem;
            }

            /* Stats Grid */
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }

            .stat-card {
                background: white;
                border-radius: var(--radius-lg);
                padding: 1.5rem;
                border: 1px solid var(--gray-200);
                display: flex;
                align-items: flex-start;
                gap: 1rem;
                transition: all 0.2s ease;
            }

            .stat-card:hover {
                border-color: var(--gray-300);
                box-shadow: var(--shadow-md);
            }

            .stat-icon {
                width: 52px;
                height: 52px;
                border-radius: var(--radius-md);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                flex-shrink: 0;
            }

            .stat-icon.blue {
                background: #dbeafe;
                color: #2563eb;
            }

            .stat-icon.green {
                background: #d1fae5;
                color: #059669;
            }

            .stat-icon.orange {
                background: #fef3c7;
                color: #d97706;
            }

            .stat-icon.red {
                background: #fee2e2;
                color: #dc2626;
            }

            .stat-content {
                flex: 1;
                min-width: 0;
            }

            .stat-label {
                font-size: 0.8125rem;
                font-weight: 500;
                color: var(--gray-500);
                margin-bottom: 0.25rem;
            }

            .stat-value {
                font-size: 1.75rem;
                font-weight: 700;
                color: var(--gray-800);
                line-height: 1.2;
            }

            .stat-trend {
                display: inline-flex;
                align-items: center;
                gap: 0.25rem;
                font-size: 0.75rem;
                font-weight: 600;
                margin-top: 0.5rem;
                padding: 0.125rem 0.5rem;
                border-radius: 9999px;
            }

            .stat-trend.up {
                background: #d1fae5;
                color: #059669;
            }

            .stat-trend.neutral {
                background: var(--gray-100);
                color: var(--gray-600);
            }

            /* Progress Section */
            .progress-card {
                background: white;
                border-radius: var(--radius-lg);
                padding: 1.5rem;
                border: 1px solid var(--gray-200);
                margin-bottom: 1.5rem;
            }

            .progress-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1rem;
            }

            .progress-title {
                font-size: 1rem;
                font-weight: 600;
                color: var(--gray-800);
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .progress-title i {
                color: var(--gray-400);
            }

            .progress-percentage {
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--aclc-blue);
            }

            .progress-bar-wrapper {
                background: var(--gray-100);
                border-radius: 9999px;
                height: 12px;
                overflow: hidden;
                margin-bottom: 0.75rem;
            }

            .progress-bar-fill {
                height: 100%;
                background: linear-gradient(90deg, var(--aclc-blue) 0%, var(--aclc-light-blue) 100%);
                border-radius: 9999px;
                transition: width 0.5s ease;
            }

            .progress-info {
                display: flex;
                justify-content: space-between;
                font-size: 0.8125rem;
                color: var(--gray-500);
            }

            /* Content Grid */
            .content-grid {
                display: grid;
                grid-template-columns: 1fr 380px;
                gap: 1.5rem;
            }

            @media (max-width: 1199.98px) {
                .content-grid {
                    grid-template-columns: 1fr;
                }
            }

            /* Election Card */
            .election-info-card {
                background: white;
                border-radius: var(--radius-lg);
                border: 1px solid var(--gray-200);
                overflow: hidden;
            }

            .election-info-header {
                background: linear-gradient(135deg, var(--aclc-blue) 0%, var(--aclc-light-blue) 100%);
                color: white;
                padding: 1.25rem 1.5rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .election-info-header i {
                font-size: 1.25rem;
            }

            .election-info-header h3 {
                font-size: 1rem;
                font-weight: 600;
                margin: 0;
            }

            .election-info-body {
                padding: 1.5rem;
            }

            .election-title-row {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 1rem;
            }

            .election-name {
                font-size: 1.125rem;
                font-weight: 700;
                color: var(--gray-800);
                margin-bottom: 0.25rem;
            }

            .election-desc {
                font-size: 0.875rem;
                color: var(--gray-500);
                margin-bottom: 1rem;
            }

            .election-dates {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
                padding: 1rem;
                background: var(--gray-50);
                border-radius: var(--radius-md);
                margin-bottom: 1.5rem;
            }

            .date-item {
                display: flex;
                flex-direction: column;
            }

            .date-label {
                font-size: 0.75rem;
                font-weight: 600;
                color: var(--gray-500);
                text-transform: uppercase;
                letter-spacing: 0.025em;
                margin-bottom: 0.25rem;
            }

            .date-value {
                font-size: 0.875rem;
                font-weight: 600;
                color: var(--gray-700);
            }

            /* Position Table */
            .position-table {
                width: 100%;
            }

            .position-table th {
                background: var(--gray-50);
                font-size: 0.75rem;
                font-weight: 600;
                color: var(--gray-600);
                text-transform: uppercase;
                letter-spacing: 0.025em;
                padding: 0.75rem 1rem;
                text-align: left;
            }

            .position-table td {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
                color: var(--gray-700);
                border-top: 1px solid var(--gray-100);
            }

            .position-table tr:hover td {
                background: var(--gray-50);
            }

            /* Recent Voters */
            .voters-card {
                background: white;
                border-radius: var(--radius-lg);
                border: 1px solid var(--gray-200);
                overflow: hidden;
            }

            .voters-header {
                background: var(--gray-50);
                padding: 1rem 1.25rem;
                border-bottom: 1px solid var(--gray-200);
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .voters-header i {
                color: var(--gray-400);
            }

            .voters-header h3 {
                font-size: 0.9375rem;
                font-weight: 600;
                color: var(--gray-800);
                margin: 0;
            }

            .voters-list {
                max-height: 400px;
                overflow-y: auto;
            }

            .voter-item {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.875rem 1.25rem;
                border-bottom: 1px solid var(--gray-100);
                transition: background 0.15s ease;
            }

            .voter-item:last-child {
                border-bottom: none;
            }

            .voter-item:hover {
                background: var(--gray-50);
            }

            .voter-avatar {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--aclc-blue) 0%, var(--aclc-light-blue) 100%);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
                font-size: 0.8125rem;
                flex-shrink: 0;
            }

            .voter-info {
                flex: 1;
                min-width: 0;
            }

            .voter-name {
                font-size: 0.875rem;
                font-weight: 600;
                color: var(--gray-800);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .voter-id {
                font-size: 0.75rem;
                color: var(--gray-500);
            }

            .empty-voters {
                padding: 2rem;
                text-align: center;
                color: var(--gray-400);
            }

            .empty-voters i {
                font-size: 2rem;
                margin-bottom: 0.5rem;
            }

            .empty-voters p {
                font-size: 0.875rem;
                margin: 0;
            }

            /* Status Badge */
            .status-active {
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
                padding: 0.25rem 0.75rem;
                background: #d1fae5;
                color: #059669;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
            }

            .status-active::before {
                content: '';
                width: 6px;
                height: 6px;
                background: currentColor;
                border-radius: 50%;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }

            /* No Election Alert */
            .no-election-alert {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 1.25rem 1.5rem;
                background: var(--info-light);
                border-radius: var(--radius-md);
                color: #1e40af;
            }

            .no-election-alert i {
                font-size: 1.5rem;
                color: var(--info);
            }

            .no-election-alert p {
                margin: 0;
                font-size: 0.9375rem;
            }
        </style>
    </x-slot>

    <!-- Page Header -->
    <div class="page-header">
        <div class="dashboard-header">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-grid-1x2-fill"></i>
                    Dashboard Overview
                </h1>
                <p class="page-subtitle">Welcome back! Here's what's happening with your election.</p>
            </div>
            <div class="dashboard-time">
                <i class="bi bi-clock"></i>
                <span id="currentTime">{{ now()->format('l, F d, Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Students</div>
                <div class="stat-value">{{ number_format($totalStudents) }}</div>
                <span class="stat-trend neutral">
                    <i class="bi bi-dash"></i> Registered voters
                </span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Students Voted</div>
                <div class="stat-value">{{ number_format($totalVoted) }}</div>
                <span class="stat-trend {{ $totalVoted > 0 ? 'up' : 'neutral' }}">
                    @if($totalVoted > 0)
                        <i class="bi bi-arrow-up"></i> Active participation
                    @else
                        <i class="bi bi-dash"></i> No votes yet
                    @endif
                </span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Candidates</div>
                <div class="stat-value">{{ number_format($totalCandidates) }}</div>
                <span class="stat-trend neutral">
                    <i class="bi bi-dash"></i> Running for positions
                </span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="bi bi-flag-fill"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Political Parties</div>
                <div class="stat-value">{{ number_format($totalParties) }}</div>
                <span class="stat-trend neutral">
                    <i class="bi bi-dash"></i> Registered parties
                </span>
            </div>
        </div>
    </div>

    <!-- Voting Progress -->
    <div class="progress-card">
        <div class="progress-header">
            <div class="progress-title">
                <i class="bi bi-graph-up-arrow"></i>
                Voting Progress
            </div>
            <div class="progress-percentage">{{ $votingPercentage }}%</div>
        </div>
        <div class="progress-bar-wrapper">
            <div class="progress-bar-fill" style="width: {{ $votingPercentage }}%;"></div>
        </div>
        <div class="progress-info">
            <span>{{ number_format($totalVoted) }} votes cast</span>
            <span>{{ number_format($totalStudents) }} total students</span>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
        <!-- Active Election Details -->
        <div class="election-info-card">
            <div class="election-info-header">
                <i class="bi bi-calendar-event"></i>
                <h3>Active Election</h3>
            </div>
            <div class="election-info-body">
                @if($activeElection)
                    <div class="election-title-row">
                        <div>
                            <div class="election-name">{{ $activeElection->title }}</div>
                            <p class="election-desc">{{ $activeElection->description ?? 'No description provided' }}</p>
                        </div>
                        <span class="status-active">Active</span>
                    </div>

                    <div class="election-dates">
                        <div class="date-item">
                            <span class="date-label">Start Date</span>
                            <span class="date-value">{{ $activeElection->start_date->format('M d, Y h:i A') }}</span>
                        </div>
                        <div class="date-item">
                            <span class="date-label">End Date</span>
                            <span class="date-value">{{ $activeElection->end_date->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>

                    <h4 class="section-title" style="font-size: 0.875rem; margin-bottom: 0.75rem;">
                        <i class="bi bi-award"></i> Positions & Candidates
                    </h4>
                    
                    <div class="table-responsive">
                        <table class="position-table">
                            <thead>
                                <tr>
                                    <th>Position</th>
                                    <th>Candidates</th>
                                    <th>Winners</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($positionStats as $stat)
                                    <tr>
                                        <td><strong>{{ $stat['name'] }}</strong></td>
                                        <td>{{ $stat['candidates'] }}</td>
                                        <td>{{ $stat['max_winners'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="no-election-alert">
                        <i class="bi bi-info-circle"></i>
                        <p>No active election at the moment. Create a new election to get started.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Voters -->
        <div class="voters-card">
            <div class="voters-header">
                <i class="bi bi-clock-history"></i>
                <h3>Recent Voters</h3>
            </div>
            <div class="voters-list">
                @if($recentVoters->count() > 0)
                    @foreach($recentVoters as $voter)
                        <div class="voter-item">
                            <div class="voter-avatar">
                                {{ strtoupper(substr($voter->name, 0, 1)) }}
                            </div>
                            <div class="voter-info">
                                <div class="voter-name">{{ $voter->name }}</div>
                                <div class="voter-id">{{ $voter->usn }}</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-voters">
                        <i class="bi bi-inbox"></i>
                        <p>No votes cast yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
