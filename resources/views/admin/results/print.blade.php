<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Election Results - {{ $election->title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #111; }
        .sheet { width: 100%; }
        .excel-table { border-collapse: collapse; width: 100%; margin-bottom: 18px; table-layout: fixed; }
        .excel-table th, .excel-table td { border: 1px solid #c6c6c6; padding: 8px 6px; vertical-align: middle; }
        .excel-table th { background: #f3f6fb; font-weight: 700; color: #111; }
        .excel-header { background: linear-gradient(#e9f0ff,#f3f6fb); border: 1px solid #c6c6c6; padding: 10px; margin-bottom: 12px; }
        .position-title { font-size: 14pt; font-weight: 700; margin: 10px 0; }
        .group-row { background: #f7f7f7; font-weight: 700; }
        .winner-row { background: #fff8dc !important; font-weight: 700; }
        .col-rank { width: 6%; text-align: center; }
        .col-name { width: 45%; text-align: left; }
        .col-party { width: 20%; text-align: left; }
        .col-votes { width: 10%; text-align: center; }
        .col-percent { width: 19%; text-align: center; }
        .right { text-align: right; }
        .center { text-align: center; }
        @media print {
            body { font-size: 10pt; }
            .excel-table th, .excel-table td { padding: 6px 4px; }
            .position-title { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="excel-header center">
        <div style="font-size:16pt; font-weight:700;">{{ $election->title }} — Election Results</div>
        <div style="margin-top:6px;">{{ \Carbon\Carbon::parse($election->start_date)->format('F d, Y') }} - {{ \Carbon\Carbon::parse($election->end_date)->format('F d, Y') }}</div>
        <div style="margin-top:4px;">Generated: {{ date('F d, Y h:i A') }}</div>
    </div>
    <table class="excel-table">
        <tr>
            <th class="col-name">Total Registered Voters</th>
            <th class="col-votes center">{{ $totalVoters }}</th>
            <th class="col-name">Votes Cast</th>
            <th class="col-votes center">{{ $votedCount }}</th>
            <th class="col-name">Voter Turnout</th>
            <th class="col-percent center">{{ $totalVoters > 0 ? round(($votedCount / $totalVoters) * 100, 2) : 0 }}%</th>
        </tr>
    </table>
    @foreach($results as $result)
        @php
            $posNameLower = strtolower(trim($result['position']->name));
            $isGrouped = $posNameLower === 'representative' || $posNameLower === 'senators' || strpos($posNameLower, 'house') !== false || isset($result['groups']);
            $winnersOnly = $winnersOnly ?? request()->boolean('winners');
            if ($isGrouped) {
                $groups = $result['groups'] ?? [];
            } else {
                $candidates = $result['candidates'] ?? [];
            }
        @endphp

        @if($isGrouped)
            <div class="position-title">{{ $result['position']->name }}</div>
            @foreach($groups as $group)
                <table class="excel-table">
                    <tr class="group-row">
                        <td colspan="5">
                            @if(isset($group['course']))
                                {{ $group['course'] ?? 'Unknown' }} — Year {{ $group['year'] ?? 'N/A' }}
                            @elseif(isset($group['house']))
                                {{ strtoupper($group['house'] ?? 'Unknown') }}
                            @else
                                {{ $loop->index ? $loop->index : 'Group' }}
                            @endif
                        </td>
                    </tr>
                    <thead>
                        <tr>
                            <th class="col-rank">Rank</th>
                            <th class="col-name">Candidate</th>
                            <th class="col-party">Party</th>
                            <th class="col-votes">Votes</th>
                            <th class="col-percent">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $groupCands = $group['candidates'] ?? [];
                            $groupTotal = $group['group_total_votes'] ?? array_sum(array_map(function($it){ return $it['votes']; }, $groupCands));
                            if ($winnersOnly) {
                                $maxVotes = 0;
                                foreach($groupCands as $cr) { $maxVotes = max($maxVotes, $cr['votes'] ?? 0); }
                                $displayCandidates = array_values(array_filter($groupCands, function($cr) use ($maxVotes) { return ($cr['votes'] ?? 0) === $maxVotes && $maxVotes > 0; }));
                            } else {
                                $displayCandidates = $groupCands;
                            }
                        @endphp

                        @if(empty($displayCandidates))
                            <tr><td colspan="5" class="center">No votes cast for this group</td></tr>
                        @else
                            @foreach($displayCandidates as $i => $candidateResult)
                                @php
                                    $percentage = $groupTotal > 0 ? round((($candidateResult['votes'] ?? 0) / $groupTotal) * 100, 2) : 0;
                                @endphp
                                <tr class="{{ $i === 0 && ($candidateResult['votes'] ?? 0) > 0 ? 'winner-row' : '' }}">
                                    <td class="col-rank">{{ $i + 1 }}</td>
                                    <td class="col-name">{{ $candidateResult['candidate']->full_name ?? $candidateResult['name'] }}</td>
                                    <td class="col-party">{{ $candidateResult['candidate']->party ? $candidateResult['candidate']->party->acronym : ($candidateResult['party'] ?? 'No Party') }}</td>
                                    <td class="col-votes center">{{ $candidateResult['votes'] ?? 0 }}</td>
                                    <td class="col-percent center">{{ $percentage }}%</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            @endforeach
            <table class="excel-table">
                <tr style="font-weight:bold; background:#eef2f8;">
                    <td colspan="3" class="center">TOTAL VOTES</td>
                    <td class="col-votes center">{{ $result['total_votes'] }}</td>
                    <td class="col-percent center">100%</td>
                </tr>
            </table>
        @else
            <div class="position-title">{{ $result['position']->name }}</div>
            <table class="excel-table">
                <thead>
                    <tr>
                        <th class="col-rank">Rank</th>
                        <th class="col-name">Candidate</th>
                        <th class="col-party">Party</th>
                        <th class="col-votes">Votes</th>
                        <th class="col-percent">%</th>
                    </tr>
                </thead>
                <tbody>
                    @if(empty($candidates))
                        <tr><td colspan="5" class="center">No votes cast for this position</td></tr>
                    @else
                        @foreach($candidates as $i => $candidateResult)
                            @php
                                $percentage = $result['total_votes'] > 0 ? round(($candidateResult['votes'] / $result['total_votes']) * 100, 2) : 0;
                            @endphp
                            <tr class="{{ $i === 0 && $candidateResult['votes'] > 0 ? 'winner-row' : '' }}">
                                <td class="col-rank">{{ $i + 1 }}</td>
                                <td class="col-name">{{ $candidateResult['candidate']->full_name }}</td>
                                <td class="col-party">{{ $candidateResult['candidate']->party ? $candidateResult['candidate']->party->acronym : 'No Party' }}</td>
                                <td class="col-votes center">{{ $candidateResult['votes'] }}</td>
                                <td class="col-percent center">{{ $percentage }}%</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        @endif
    @endforeach
    <p style="text-align:center; font-size:10pt; margin-top:40px;">This is an official document generated by the ACLC College Electronic Voting System<br>Printed on {{ date('F d, Y \a\t h:i A') }}</p>
</body>
</html>
