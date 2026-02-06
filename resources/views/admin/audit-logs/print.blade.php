<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log - {{ $election->title }}</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }
        
        .header h1 {
            font-size: 18pt;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .header h2 {
            font-size: 14pt;
            color: #555;
            font-weight: normal;
            margin-bottom: 10px;
        }
        
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .meta-info div {
            flex: 1;
        }
        
        .meta-info strong {
            display: block;
            color: #2c3e50;
            margin-bottom: 3px;
        }
        
        .position-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .position-header {
            background-color: #2c3e50;
            color: white;
            padding: 8px 12px;
            margin-bottom: 10px;
            font-size: 11pt;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        table thead {
            background-color: #f8f9fa;
        }
        
        table th {
            padding: 8px;
            text-align: left;
            border: 1px solid #dee2e6;
            font-weight: 600;
            font-size: 9pt;
        }
        
        table td {
            padding: 6px 8px;
            border: 1px solid #dee2e6;
            font-size: 9pt;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 5px;
            page-break-inside: avoid;
        }
        
        .summary h3 {
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-item .number {
            font-size: 16pt;
            font-weight: bold;
            color: #2c3e50;
            display: block;
        }
        
        .summary-item .label {
            font-size: 9pt;
            color: #666;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .no-print {
                display: none !important;
            }
            
            .position-section {
                page-break-inside: avoid;
            }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 11pt;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Print</button>
    
    <div class="header">
        <h1>AUDIT LOG - VOTER RECORDS</h1>
        <h2>{{ $election->title }}</h2>
        <div style="margin-top: 10px; font-size: 9pt; color: #666;">
            Generated on {{ now()->format('F d, Y \a\t h:i A') }}
        </div>
    </div>
    
    <div class="meta-info">
        <div>
            <strong>Election:</strong>
            {{ $election->title }}
        </div>
        <div>
            <strong>Total Voters:</strong>
            {{ $totalVoters }}
        </div>
        <div>
            <strong>Total Positions:</strong>
            {{ $auditLogs->count() }}
        </div>
    </div>
    
    @foreach($auditLogs as $positionId => $logs)
        @php
            $position = $logs->first()->position;
        @endphp
        <div class="position-section">
            <div class="position-header">
                Position: {{ $position->name }}
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 20%;">Timestamp</th>
                        <th style="width: 15%;">Student USN</th>
                        <th style="width: 25%;">Student Name</th>
                        <th style="width: 25%;">Candidate Voted</th>
                        <th style="width: 10%;">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $index => $log)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $log->voted_at->format('m/d/Y H:i') }}</td>
                            <td><strong>{{ $log->user_usn }}</strong></td>
                            <td>{{ $log->user_name }}</td>
                            <td>{{ $log->candidate_name }}</td>
                            <td style="font-size: 8pt;">{{ $log->ip_address }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div style="padding: 5px; background-color: #f8f9fa; font-size: 9pt;">
                <strong>Total votes for this position:</strong> {{ $logs->count() }}
            </div>
        </div>
    @endforeach
    
    <div class="summary">
        <h3>Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <span class="number">{{ $totalVoters }}</span>
                <span class="label">Total Voters</span>
            </div>
            <div class="summary-item">
                <span class="number">{{ $auditLogs->count() }}</span>
                <span class="label">Positions</span>
            </div>
            <div class="summary-item">
                <span class="number">{{ $auditLogs->flatten()->count() }}</span>
                <span class="label">Total Vote Records</span>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>This is an official audit log for manual counting and verification purposes.</p>
        <p>This document contains sensitive voter information and should be handled securely.</p>
    </div>
    
    <script>
        // Auto-print when loaded (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
