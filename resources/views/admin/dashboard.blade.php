<x-admin-layout title="Admin Dashboard">
    <x-slot name="styles">
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            text-align: center;
            height: 100%;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .stats-card.red {
            border-top: 4px solid var(--aclc-red);
        }

        .stats-card.green {
            border-top: 4px solid #28a745;
        }

        .stats-card.orange {
            border-top: 4px solid #fd7e14;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 15px;
        }

        .stats-icon.blue {
            background: rgba(0, 51, 102, 0.1);
            color: var(--aclc-blue);
        }

        .stats-icon.red {
            background: rgba(204, 0, 0, 0.1);
            color: var(--aclc-red);
        }

        .stats-icon.green {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .stats-icon.orange {
            background: rgba(253, 126, 20, 0.1);
            color: #fd7e14;
        }

        .stats-card h3 {
            font-size: 32px;
            font-weight: 700;
            margin: 10px 0 5px 0;
            color: #333;
        }

        .stats-card p {
            color: #6c757d;
            margin: 0;
            font-size: 14px;
        }

        .card-header {
            background: linear-gradient(135deg, var(--aclc-blue) 0%, var(--aclc-light-blue) 100%);
            color: white;
            font-weight: 600;
            padding: 15px 20px;
            border-radius: 15px 15px 0 0;
        }

        .card-body {
            padding: 20px;
        }

        .progress {
            height: 25px;
            border-radius: 10px;
            background: #e9ecef;
        }

        .progress-bar {
            background: linear-gradient(135deg, var(--aclc-red) 0%, #990000 100%);
            border-radius: 10px;
            font-weight: 600;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--aclc-blue) 0%, var(--aclc-light-blue) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .list-group-item {
            border: none;
            border-bottom: 1px solid #f0f0f0;
            padding: 12px 0;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        .table-responsive {
            margin-top: 15px;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--aclc-blue);
        }
    </x-slot>

    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="bi bi-speedometer2"></i> Admin Dashboard
        </h1>
    </div>

    <!-- Stats Cards Row -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stats-card">
                <div class="stats-icon blue">
                    <i class="bi bi-people"></i>
                </div>
                <h3>{{ $totalStudents }}</h3>
                <p>Total Students</p>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="stats-card green">
                <div class="stats-icon green">
                    <i class="bi bi-check-circle"></i>
                </div>
                <h3>{{ $totalVoted }}</h3>
                <p>Students Voted</p>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="stats-card orange">
                <div class="stats-icon orange">
                    <i class="bi bi-person-badge"></i>
                </div>
                <h3>{{ $totalCandidates }}</h3>
                <p>Total Candidates</p>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="stats-card red">
                <div class="stats-icon red">
                    <i class="bi bi-flag"></i>
                </div>
                <h3>{{ $totalParties }}</h3>
                <p>Political Parties</p>
            </div>
        </div>
    </div>

    <!-- Voting Progress -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-graph-up"></i> Voting Progress
                </div>
                <div class="card-body">
                    <h5>Overall Turnout: <strong>{{ $votingPercentage }}%</strong></h5>
                    <div class="progress mb-3">
                        <div class="progress-bar" role="progressbar" style="width: {{ $votingPercentage }}%;" 
                             aria-valuenow="{{ $votingPercentage }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $votingPercentage }}%
                        </div>
                    </div>
                    <p class="text-muted mb-0">
                        <i class="bi bi-info-circle"></i> 
                        {{ $totalVoted }} out of {{ $totalStudents }} students have voted
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Active Election -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-calendar-event"></i> Active Election
                </div>
                <div class="card-body">
                    @if($activeElection)
                        <h5>{{ $activeElection->title }}</h5>
                        <p class="text-muted">{{ $activeElection->description }}</p>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="bi bi-calendar-check"></i> Start Date:</strong><br>
                                {{ $activeElection->start_date->format('F d, Y h:i A') }}
                            </div>
                            <div class="col-md-6">
                                <strong><i class="bi bi-calendar-x"></i> End Date:</strong><br>
                                {{ $activeElection->end_date->format('F d, Y h:i A') }}
                            </div>
                        </div>

                        <h6 class="mt-4">Positions & Candidates:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
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
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No active election at the moment.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Voters -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history"></i> Recent Voters
                </div>
                <div class="card-body">
                    @if($recentVoters->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentVoters as $voter)
                                <div class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            {{ substr($voter->firstname, 0, 1) }}
                                        </div>
                                        <div>
                                            <div style="font-size: 14px; font-weight: 500;">{{ $voter->firstname }} {{ $voter->lastname }}</div>
                                            <small class="text-muted">{{ $voter->usn }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center">No votes cast yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
