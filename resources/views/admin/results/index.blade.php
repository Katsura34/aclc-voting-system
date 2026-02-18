<x-admin-layout title="Election Results">
    <x-slot name="styles">
        <style>
            .winner-badge {
                background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
                color: #000;
                padding: 5px 15px;
                border-radius: 20px;
                font-weight: bold;
                display: inline-block;
                margin-left: 10px;
            }
            .result-card { transition: all 0.3s ease; }
            .result-card:hover { transform: translateY(-5px); box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
            .progress { height: 30px; font-size: 0.9rem; }
            .candidate-rank { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:1.2rem }
            .rank-1 { background: #FFD700; color: #000; }
            .rank-2 { background: #C0C0C0; color: #000; }
            .rank-3 { background: #CD7F32; color: #fff; }
            .rank-other { background: #6c757d; color: #fff; }
        </style>
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-bar-chart-fill"></i> Election Results</h2>
                <p class="text-muted mb-0">View voting results and statistics</p>
            </div>
            @if($selectedElection)
                <div class="btn-group">
                    <a href="{{ route('admin.results.print', ['election_id' => $selectedElection->id]) }}" class="btn btn-primary" target="_blank">
                        <i class="bi bi-printer"></i> Print (A4)
                    </a>
                    <a href="{{ route('admin.results.export', ['election_id' => $selectedElection->id]) }}" class="btn btn-success">
                        <i class="bi bi-download"></i> Export CSV
                    </a>
                </div>
            @endif
        </div>

        <!-- Election Selector -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.results.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-8">
                            <label for="election_id" class="form-label fw-bold">
                                <i class="bi bi-calendar-event"></i> Select Election
                            </label>
                            <select class="form-select" name="election_id" id="election_id" required>
                                <option value="">Choose an election...</option>
                                @foreach($elections as $election)
                                    <option value="{{ $election->id }}" {{ request('election_id') == $election->id ? 'selected' : '' }}>
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
        </div>

        @if($selectedElection)
            <!-- Election Info -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h3 class="mb-2">{{ $selectedElection->title }}</h3>
                            <p class="mb-0">{{ $selectedElection->description }}</p>
                            <hr class="bg-white">
                            <div class="row">
                                <div class="col-md-3"><small>Start Date</small><p class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($selectedElection->start_date)->format('M d, Y h:i A') }}</p></div>
                                <div class="col-md-3"><small>End Date</small><p class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($selectedElection->end_date)->format('M d, Y h:i A') }}</p></div>
                                <div class="col-md-3"><small>Status</small><p class="mb-0">@if($selectedElection->is_active)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Inactive</span>@endif</p></div>
                                <div class="col-md-3"><small>Positions</small><p class="mb-0 fw-bold">{{ $selectedElection->positions->count() }}</p></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3"><div class="card text-center"><div class="card-body"><i class="bi bi-people-fill" style="font-size:2.5rem;color:var(--aclc-blue);"></i><h3 class="mt-2 mb-0">{{ $totalVoters }}</h3><p class="text-muted mb-0">Total Voters</p></div></div></div>
                <div class="col-md-3"><div class="card text-center"><div class="card-body"><i class="bi bi-check-circle-fill" style="font-size:2.5rem;color:#28a745;"></i><h3 class="mt-2 mb-0">{{ $votedCount }}</h3><p class="text-muted mb-0">Votes Cast</p></div></div></div>
                <div class="col-md-3"><div class="card text-center"><div class="card-body"><i class="bi bi-percent" style="font-size:2.5rem;color:#ffc107;"></i><h3 class="mt-2 mb-0">{{ $totalVoters > 0 ? round(($votedCount / $totalVoters) * 100, 2) : 0 }}%</h3><p class="text-muted mb-0">Turnout Rate</p></div></div></div>
                <div class="col-md-3"><div class="card text-center"><div class="card-body"><i class="bi bi-award-fill" style="font-size:2.5rem;color:var(--aclc-red);"></i><h3 class="mt-2 mb-0">{{ count($results) }}</h3><p class="text-muted mb-0">Positions</p></div></div></div>
            </div>

            <!-- Results by Position -->
            @foreach($results as $result)
                <div class="card mb-4 result-card">
                    <div class="card-header bg-dark text-white">
                        <h4 class="mb-0"><i class="bi bi-award"></i> {{ $result['position']->name }} <span class="badge bg-light text-dark float-end">Total Votes: {{ $result['total_votes'] }}</span></h4>
                        @if($result['position']->description)<small>{{ $result['position']->description }}</small>@endif
                    </div>
                    <div class="card-body">
                        @php
                            $posNameLower = strtolower(trim($result['position']->name));
                            $isGrouped = $posNameLower === 'senators' || $posNameLower === 'representative' || strpos($posNameLower, 'house') !== false || isset($result['groups']);
                            if ($isGrouped) {
                                $groups = $result['groups'] ?? [];
                                $noVotes = empty($groups) || collect($groups)->sum('group_total_votes') == 0;
                            } else {
                                $noVotes = empty($result['candidates'] ?? []);
                            }
                        @endphp

                        @if($noVotes)
                            <div class="text-center py-4"><i class="bi bi-inbox" style="font-size:3rem;color:#ccc;"></i><p class="text-muted mt-2">No votes cast for this position</p></div>
                        @else
                            @if($isGrouped)
                                @php
                                    $groups = $result['groups'] ?? [];
                                    // Fallback grouping only for Representative if groups not present
                                    if (empty($groups) && isset($result['candidates']) && $posNameLower === 'representative') {
                                        $groups = collect($result['candidates'])->groupBy(function($c) {
                                            $course = $c['candidate']->course ?? 'Unknown';
                                            $year = $c['candidate']->year_level ?? 'Unknown';
                                            return $course . ' ' . $year;
                                        })->map(function($items, $key) {
                                            $parts = explode(' ', $key);
                                            return [
                                                'course' => $parts[0] ?? 'Unknown',
                                                'year' => $parts[1] ?? 'Unknown',
                                                'candidates' => $items->values()->all(),
                                                'group_total_votes' => array_sum(array_map(function($i){ return $i['votes']; }, $items->toArray())),
                                            ];
                                        })->toArray();
                                    }
                                @endphp

                                @foreach($groups as $groupKey => $group)
                                    @if(isset($group['course']))
                                        <h5 class="mt-4 mb-2">{{ $group['course'] }} {{ $group['year'] }}</h5>
                                    @elseif(isset($group['house']))
                                        <h5 class="mt-4 mb-2">{{ strtoupper($group['house'] ?? $groupKey) }}</h5>
                                    @else
                                        <h5 class="mt-4 mb-2">{{ $groupKey }}</h5>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="60">Rank</th>
                                                    <th>Candidate</th>
                                                    <th>Party</th>
                                                    <th>Votes</th>
                                                    <th width="40%">Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(empty($group['candidates']))
                                                    <tr><td colspan="5" class="center">No votes cast for this group</td></tr>
                                                @else
                                                    @foreach($group['candidates'] as $index => $candidateResult)
                                                        <tr>
                                                            <td><div class="candidate-rank rank-{{ $index + 1 > 3 ? 'other' : $index + 1 }}">{{ $index + 1 }}</div></td>
                                                            <td><strong>{{ $candidateResult['candidate']->full_name ?? $candidateResult['name'] }}</strong>
                                                                @if($index === 0 && ($candidateResult['votes'] ?? 0) > 0)
                                                                    <span class="winner-badge"><i class="bi bi-trophy-fill"></i> WINNER</span>
                                                                @endif
                                                            </td>
                                                            <td>@php $party = $candidateResult['candidate']->party ?? null; @endphp
                                                                @if(isset($party) && $party)
                                                                    <span class="badge" style="background-color: {{ $party->color }};">{{ $party->acronym }}</span>
                                                                @else
                                                                    <span class="badge bg-secondary">No Party</span>
                                                                @endif
                                                            </td>
                                                            <td><strong class="fs-5">{{ $candidateResult['votes'] ?? 0 }}</strong></td>
                                                            <td>
                                                                @php
                                                                    $groupTotal = $group['group_total_votes'] ?? array_sum(array_map(function($i){ return $i['votes']; }, $group['candidates']));
                                                                    $percentage = $groupTotal > 0 ? round((($candidateResult['votes'] ?? 0) / $groupTotal) * 100, 2) : 0;
                                                                @endphp
                                                                <div class="progress">
                                                                    <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%; background-color: {{ $candidateResult['candidate']->party->color ?? '#0d6efd' }};" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">{{ $percentage }}%</div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                @endforeach
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="60">Rank</th>
                                                <th>Candidate</th>
                                                <th>Party</th>
                                                <th>Votes</th>
                                                <th width="40%">Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($result['candidates'] as $index => $candidateResult)
                                                <tr>
                                                    <td><div class="candidate-rank rank-{{ $index + 1 > 3 ? 'other' : $index + 1 }}">{{ $index + 1 }}</div></td>
                                                    <td><strong>{{ $candidateResult['candidate']->full_name }}</strong>
                                                        @if($index === 0 && $candidateResult['votes'] > 0)
                                                            <span class="winner-badge"><i class="bi bi-trophy-fill"></i> WINNER</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($candidateResult['candidate']->party)
                                                            <span class="badge" style="background-color: {{ $candidateResult['candidate']->party->color }};">{{ $candidateResult['candidate']->party->acronym }}</span>
                                                        @else
                                                            <span class="badge bg-secondary">No Party</span>
                                                        @endif
                                                    </td>
                                                    <td><strong class="fs-5">{{ $candidateResult['votes'] }}</strong></td>
                                                    <td>
                                                        @php $percentage = $result['total_votes'] > 0 ? round(($candidateResult['votes'] / $result['total_votes']) * 100, 2) : 0; @endphp
                                                        <div class="progress">
                                                            <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%; background-color: {{ $candidateResult['candidate']->party->color ?? '#0d6efd' }};" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">{{ $percentage }}%</div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <!-- No Election Selected -->
            <div class="card text-center py-5">
                <div class="card-body">
                    <i class="bi bi-bar-chart" style="font-size: 5rem; color: var(--aclc-light-blue);"></i>
                    <h4 class="mt-4">No Election Selected</h4>
                    <p class="text-muted">Please select an election from the dropdown above to view results.</p>
                </div>
            </div>
        @endif
    </div>
</x-admin-layout>
