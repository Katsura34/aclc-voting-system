<x-admin-layout title="Audit Logs - Voter Records">
    <x-slot name="styles">
        <style>
            .audit-table {
                font-size: 0.9rem;
            }
            .audit-table th {
                background-color: #f8f9fa;
                font-weight: 600;
                position: sticky;
                top: 0;
                z-index: 10;
            }
            .audit-table tbody tr:hover {
                background-color: #f8f9fa;
            }
            .export-buttons {
                gap: 10px;
            }
            @media print {
                .no-print {
                    display: none !important;
                }
            }
        </style>
    </x-slot>

    <div class="container-fluid py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="mb-1">
                                    <i class="bi bi-file-earmark-text me-2"></i>
                                    Audit Logs - Voter Records
                                </h4>
                                <p class="text-muted mb-0">View detailed voting records for manual counting and verification</p>
                            </div>
                        </div>

                        <!-- Election Selection Form -->
                        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-3">
                            <div class="col-md-6">
                                <label for="election_id" class="form-label">Select Election</label>
                                <select name="election_id" id="election_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Select an Election --</option>
                                    @foreach($elections as $election)
                                        <option value="{{ $election->id }}" 
                                            {{ request('election_id') == $election->id ? 'selected' : '' }}>
                                            {{ $election->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @if($selectedElection)
                                <div class="col-md-4">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" 
                                           name="search" 
                                           id="search" 
                                           class="form-control" 
                                           placeholder="Search by USN, name, or candidate..."
                                           value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search me-1"></i> Search
                                    </button>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if($selectedElection)
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-primary">{{ $totalVotes }}</h3>
                            <p class="text-muted mb-0">Total Vote Records</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-success">{{ $auditLogs->unique('user_id')->count() }}</h3>
                            <p class="text-muted mb-0">Unique Voters</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-info">{{ $selectedElection->positions->count() }}</h3>
                            <p class="text-muted mb-0">Positions</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="row mb-3 no-print">
                <div class="col-12">
                    <div class="d-flex export-buttons">
                        <a href="{{ route('admin.audit-logs.export', ['election_id' => $selectedElection->id]) }}" 
                           class="btn btn-success">
                            <i class="bi bi-file-earmark-excel me-2"></i>
                            Export to CSV
                        </a>
                        <a href="{{ route('admin.audit-logs.print', ['election_id' => $selectedElection->id]) }}" 
                           target="_blank"
                           class="btn btn-info">
                            <i class="bi bi-printer me-2"></i>
                            Print View
                        </a>
                    </div>
                </div>
            </div>

            <!-- Audit Logs Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            @if($auditLogs->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover audit-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Timestamp</th>
                                                <th>Student USN</th>
                                                <th>Student Name</th>
                                                <th>Position</th>
                                                <th>Candidate Voted</th>
                                                <th>IP Address</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($auditLogs as $index => $log)
                                                <tr>
                                                    <td>{{ $auditLogs->firstItem() + $index }}</td>
                                                    <td>{{ $log->voted_at->format('M d, Y h:i A') }}</td>
                                                    <td><strong>{{ $log->user_usn }}</strong></td>
                                                    <td>{{ $log->user_name }}</td>
                                                    <td><span class="badge bg-primary">{{ $log->position_name }}</span></td>
                                                    <td>{{ $log->candidate_name }}</td>
                                                    <td><small class="text-muted">{{ $log->ip_address }}</small></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="mt-3">
                                    {{ $auditLogs->appends(request()->query())->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3">No audit logs found for this election.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Please select an election to view audit logs.
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-admin-layout>
