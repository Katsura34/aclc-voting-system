<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - {{ $election->title }}</title>
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
            table {
                page-break-inside: avoid;
            }
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h1 {
            color: #000;
            font-size: 20pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header h2 {
            color: #000;
            font-size: 16pt;
            margin-bottom: 5px;
        }

        .header p {
            color: #000;
            font-size: 10pt;
            margin: 2px 0;
        }

        .stats-box {
            background: #fff;
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 0;
        }

        .stats-box table {
            width: 100%;
            margin: 0;
        }

        .stats-box td {
            padding: 5px;
            border: none;
        }

        .position-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .position-header {
            background: #fff;
            color: #000;
            padding: 8px 12px;
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #000;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .results-table th {
            background: #fff;
            padding: 8px;
            text-align: left;
            border: 1px solid #000;
            font-weight: bold;
        }

        .results-table td {
            padding: 8px;
            border: 1px solid #000;
        }

        .results-table tr:nth-child(even) {
            background: #fff;
        }

        .winner-row {
            background: #fff !important;
            font-weight: bold;
            border-top: 2px double #000;
        }

        .winner-badge {
            background: #fff;
            color: #000;
            padding: 2px 8px;
            border-radius: 0;
            font-size: 9pt;
            font-weight: bold;
            border: 1px solid #000;
        }

        .rank-badge {
            background: #fff;
            color: #000;
            padding: 2px 8px;
            border-radius: 50%;
            font-weight: bold;
            display: inline-block;
            min-width: 25px;
            text-align: center;
            border: 1px solid #000;
        }

        .rank-1, .rank-2, .rank-3 { background: #fff; color: #000; }

        .party-badge {
            padding: 2px 8px;
            border-radius: 0;
            color: #000;
            font-size: 9pt;
            font-weight: 500;
            border: 1px solid #000;
            background: #fff;
        }

        .abstain-row {
            background: #fff !important;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #000;
            font-size: 9pt;
            color: #000;
        }

        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin: 40px auto 5px;
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 1000;">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Print
        </button>
        <a href="{{ route('admin.results.index', ['election_id' => $election->id]) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <!-- Header -->
    <div class="header">
        <h1>ACLC COLLEGE</h1>
        <h2>ELECTION RESULTS</h2>
        <p><strong>{{ $election->title }}</strong></p>
        <p>{{ \Carbon\Carbon::parse($election->start_date)->format('F d, Y') }} - 
           {{ \Carbon\Carbon::parse($election->end_date)->format('F d, Y') }}</p>
        <p>Generated: {{ date('F d, Y h:i A') }}</p>
    </div>

    <!-- Statistics Summary -->
    <div class="stats-box">
        <table>
            <tr>
                <td><strong>Total Registered Voters:</strong></td>
                <td>{{ $totalVoters }}</td>
                <td><strong>Votes Cast:</strong></td>
                <td>{{ $votedCount }}</td>
            </tr>
            <tr>
                <td><strong>Voter Turnout:</strong></td>
                <td>{{ $totalVoters > 0 ? round(($votedCount / $totalVoters) * 100, 2) : 0 }}%</td>
                <td><strong>Total Positions:</strong></td>
                <td>{{ count($results) }}</td>
            </tr>
        </table>
    </div>

    <!-- Results by Position -->
    @foreach($results as $index => $result)
        <div class="position-section">
            <div class="position-header">
                {{ $result['position']->name }}
                @if($result['position']->description)
                    <small style="font-weight: normal; font-size: 10pt;"> - {{ $result['position']->description }}</small>
                @endif
            </div>

            <table class="results-table">
                <thead>
                    <tr>
                        <th width="60">Rank</th>
                        <th>Candidate Name</th>
                        <th width="150">Party</th>
                        <th width="80">Votes</th>
                        <th width="100">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @if(empty($result['candidates']) && $result['abstain_votes'] == 0)
                        <tr>
                            <td colspan="5" style="text-align: center; color: #999;">No votes cast for this position</td>
                        </tr>
                    @else
                        @foreach($result['candidates'] as $i => $candidateResult)
                            @php
                                $percentage = $result['total_votes'] > 0 
                                    ? round(($candidateResult['votes'] / $result['total_votes']) * 100, 2) 
                                    : 0;
                            @endphp
                            <tr class="{{ $i === 0 && $candidateResult['votes'] > 0 ? 'winner-row' : '' }}">
                                <td style="text-align: center;">
                                    <span class="rank-badge rank-{{ $i + 1 > 3 ? 'other' : $i + 1 }}">
                                        {{ $i + 1 }}
                                    </span>
                                </td>
                                <td>
                                    {{ $candidateResult['candidate']->full_name }}
                                    @if($i === 0 && $candidateResult['votes'] > 0)
                                        <span class="winner-badge">★ WINNER</span>
                                    @endif
                                </td>
                                <td>
                                    @if($candidateResult['candidate']->party)
                                        <span class="party-badge">
                                            {{ $candidateResult['candidate']->party->acronym }} - {{ $candidateResult['candidate']->party->name }}
                                        </span>
                                    @else
                                        <span style="color: #999;">No Party</span>
                                    @endif
                                </td>
                                <td style="text-align: center; font-weight: bold;">{{ $candidateResult['votes'] }}</td>
                                <td style="text-align: center;">{{ $percentage }}%</td>
                            </tr>
                        @endforeach

                        @if($result['abstain_votes'] > 0)
                            @php
                                $abstainPercentage = $result['total_votes'] > 0 
                                    ? round(($result['abstain_votes'] / $result['total_votes']) * 100, 2) 
                                    : 0;
                            @endphp
                            <tr class="abstain-row">
                                <td style="text-align: center;">-</td>
                                <td><strong>ABSTAIN</strong></td>
                                <td>-</td>
                                <td style="text-align: center; font-weight: bold;">{{ $result['abstain_votes'] }}</td>
                                <td style="text-align: center;">{{ $abstainPercentage }}%</td>
                            </tr>
                        @endif

                        <tr style="background: #e9ecef; font-weight: bold;">
                            <td colspan="3" style="text-align: right;">TOTAL VOTES:</td>
                            <td style="text-align: center;">{{ $result['total_votes'] }}</td>
                            <td style="text-align: center;">100%</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if(($index + 1) % 2 == 0 && ($index + 1) < count($results))
            <div class="page-break"></div>
        @endif
    @endforeach

    <!-- Signature Section -->
    <div class="signature-section">
        <table style="width: 100%; margin-top: 50px;">
            <tr>
                <td style="width: 50%; text-align: center;">
                    <div class="signature-line"></div>
                    <p style="margin: 5px 0; font-size: 10pt;"><strong>Election Committee Head</strong></p>
                    <p style="margin: 0; font-size: 9pt; color: #666;">Signature over Printed Name</p>
                </td>
                <td style="width: 50%; text-align: center;">
                    <div class="signature-line"></div>
                    <p style="margin: 5px 0; font-size: 10pt;"><strong>School Administrator</strong></p>
                    <p style="margin: 0; font-size: 9pt; color: #666;">Signature over Printed Name</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This is an official document generated by the ACLC College Electronic Voting System</p>
        <p>Printed on {{ date('F d, Y \a\t h:i A') }}</p>
    </div>

    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> --}}
</body>
</html>
