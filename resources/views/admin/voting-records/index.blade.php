<x-admin-layout>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Voting Records</h1>
            <div>
                @if(request('election_id'))
                    <a href="{{ route('admin.voting-records.export', ['election_id' => request('election_id')]) }}" 
                       class="btn btn-success">
                        <i class="bi bi-download"></i> Export to CSV
                    </a>
                @else
                    <a href="{{ route('admin.voting-records.export') }}" 
                       class="btn btn-success">
                        <i class="bi bi-download"></i> Export All to CSV
                    </a>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.voting-records.index') }}">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="election_id" class="form-label">Filter by Election</label>
                            <select name="election_id" id="election_id" class="form-select">
                                <option value="">All Elections</option>
                                @foreach($elections as $election)
                                    <option value="{{ $election->id }}" {{ request('election_id') == $election->id ? 'selected' : '' }}>
                                        {{ $election->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.voting-records.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Records Table -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-list-check"></i> Voting Records for Manual Counting
                </h5>
                <small>This record shows which students voted (not who they voted for)</small>
            </div>
            <div class="card-body">
                @if($records->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Election</th>
                                    <th>Student USN</th>
                                    <th>Student Name</th>
                                    <th>Voted At</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $index => $record)
                                    <tr>
                                        <td>{{ $records->firstItem() + $index }}</td>
                                        <td>{{ $record->election->title }}</td>
                                        <td>{{ $record->student->usn }}</td>
                                        <td>{{ $record->student->name }}</td>
                                        <td>{{ $record->voted_at->format('M d, Y h:i A') }}</td>
                                        <td>{{ $record->ip_address ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $records->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-4 text-muted"></i>
                        <p class="mt-3 text-muted">No voting records found.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Info Box -->
        <div class="alert alert-info mt-4">
            <h5><i class="bi bi-info-circle"></i> About Voting Records</h5>
            <p class="mb-0">
                This page shows a record of students who have voted for each election. This serves as a backup for manual counting
                if the system fails. The records show <strong>who voted</strong> but not <strong>who they voted for</strong>, 
                maintaining ballot secrecy while ensuring election integrity.
            </p>
        </div>
    </div>
</x-admin-layout>
