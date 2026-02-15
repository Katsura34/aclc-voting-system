<x-admin-layout title="Manage Users">
    <x-slot name="styles">
        <style>
            .user-card {
                transition: all 0.3s ease;
            }
            .user-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            .status-badge {
                padding: 5px 10px;
                border-radius: 15px;
                font-size: 0.85rem;
            }
            .filter-card {
                background: #f8f9fa;
                border-left: 4px solid var(--aclc-red);
            }
            #import-loading-overlay {
                display: none;
                position: fixed;
                top: 0; left: 0;
                width: 100vw; height: 100vh;
                background: rgba(255,255,255,0.8);
                z-index: 2000;
                align-items: center;
                justify-content: center;
                flex-direction: column;
            }
        </style>
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-people-fill"></i> Manage Students</h2>
                <p class="text-muted mb-0">View and manage all system Student</p>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-upload"></i> Import CSV
                </button>
                {{-- <a href="{{ route('admin.users.download-template') }}" class="btn btn-info">
                    <i class="bi bi-download"></i> Download Template
                </a> --}}
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#resetAllVotesModal">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset All Votes
                </button>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New Student
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Search and Filter -->
        <div class="card filter-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label"><i class="bi bi-search"></i> Search</label>
                        <input type="text" class="form-control" name="search" 
                               placeholder="USN, Name, Email..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><i class="bi bi-person-badge"></i> User Type</label>
                        <select class="form-select" name="user_type">
                            <option value="">All Types</option>
                            <option value="student" {{ request('user_type') == 'student' ? 'selected' : '' }}>Student</option>
                            <option value="admin" {{ request('user_type') == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><i class="bi bi-check-circle"></i> Voting Status</label>
                        <select class="form-select" name="has_voted">
                            <option value="">All Status</option>
                            <option value="yes" {{ request('has_voted') == 'yes' ? 'selected' : '' }}>Has Voted</option>
                            <option value="no" {{ request('has_voted') == 'no' ? 'selected' : '' }}>Not Voted</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-table"></i> Users List ({{ $users->total() }} users)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>USN</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Strand</th>
                                <th>Year</th>
                                <th>Gender</th>
                                <th>Voting Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td><strong>{{ $user->usn }}</strong></td>
                                    <td>{{ $user->firstname }} {{ $user->lastname }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->user_type === 'admin')
                                            <span class="badge bg-danger">
                                                <i class="bi bi-shield-fill"></i> Admin
                                            </span>
                                        @else
                                            <span class="badge bg-primary">
                                                <i class="bi bi-person"></i> Student
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $user->strand ?? 'N/A' }}</td>
                                    <td>{{ $user->year ?? 'N/A' }}</td>
                                    <td>{{ $user->gender ?? 'N/A' }}</td>
                                    <td>
                                        @if($user->has_voted)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle-fill"></i> Voted
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-x-circle"></i> Not Voted
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.users.edit', $user) }}" 
                                               class="btn btn-warning"
                                               title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            
                                            @if($user->has_voted && $user->user_type === 'student')
                                                <form action="{{ route('admin.users.reset-vote', $user) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Reset voting status for this user?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-info" title="Reset Vote">
                                                        <i class="bi bi-arrow-counterclockwise"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <form action="{{ route('admin.users.destroy', $user) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-danger"
                                                        title="Delete"
                                                        {{ $user->id === auth()->user()->id ? 'disabled' : '' }}>
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2">No Student found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($users->hasPages())
                <div class="card-footer">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Reset All Votes Modal -->
    <div class="modal fade" id="resetAllVotesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle"></i> Reset All Votes
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.users.reset-all-votes') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to reset voting status for <strong>ALL students</strong>?</p>
                        <p class="text-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i> 
                            This action will allow all students to vote again. This cannot be undone!
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset All Votes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import CSV Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-upload"></i> Import Users from CSV
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>CSV Format:</strong> usn, lastname, firstname, course, year_level, gender, password
                        </div>
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Select CSV File</label>
                            <input type="file" 
                                   class="form-control" 
                                   id="csv_file" 
                                   name="csv_file" 
                                   accept=".csv"
                                   required>
                            <small class="text-muted">Maximum file size: 2MB</small>
                        </div>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> 
                            Make sure your CSV file follows the template format. Download the template if you haven't already.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-upload"></i> Import Users
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Loading Spinner Overlay with Numeric Progress -->
    <style>
#import-loading-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    background: rgba(255,255,255,0.8);
    z-index: 2000;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}
</style>
<div id="import-loading-overlay">
  <div style="text-align:center; width:420px;">
    <div class="spinner-border text-success" style="width:4rem;height:4rem;" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>

    <div class="mt-3 fw-bold text-success">Importing users, please wait...</div>

    <div class="progress mt-3" style="height: 22px;">
      <div id="import-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
           role="progressbar" style="width:0%">0%</div>
    </div>

    <div id="import-progress-numeric" class="mt-2 fw-bold text-dark" style="font-size:1.2rem;">0/0</div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
  const importForm = document.querySelector('#importModal form');
  const overlay = document.getElementById('import-loading-overlay');
  const progressNumeric = document.getElementById('import-progress-numeric');
  const progressBar = document.getElementById('import-progress-bar');

  let pollTimer = null;

  function setProgress(done, total) {
    const pct = total > 0 ? Math.floor((done / total) * 100) : 0;
    progressNumeric.textContent = `${done}/${total}`;
    progressBar.style.width = pct + '%';
    progressBar.textContent = pct + '%';
  }

  async function pollProgress(token) {
    try {
      const res = await fetch(`/admin/users/import-progress/${token}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await res.json();

      setProgress(data.processed ?? 0, data.total ?? 0);

      if (data.status === 'done') {
        clearInterval(pollTimer);
        pollTimer = null;
        overlay.style.display = 'none';
        window.location.reload();
      }

      if (data.status === 'error') {
        clearInterval(pollTimer);
        pollTimer = null;
        overlay.style.display = 'none';
        alert(data.message || 'Import failed.');
      }
    } catch (e) {
      // keep polling; transient errors happen
    }
  }

  if (importForm) {
    importForm.addEventListener('submit', function(e) {
      e.preventDefault();
      overlay.style.display = 'flex';
      setProgress(0, 0);

      const fileInput = document.getElementById('csv_file');
      const file = fileInput?.files?.[0];
      if (!file) {
        overlay.style.display = 'none';
        alert('Please choose a CSV file.');
        return;
      }

      // Count total rows client-side (optional, UX only)
      const reader = new FileReader();
      reader.onload = function(evt) {
        const lines = evt.target.result.split(/\r?\n/).filter(l => l.trim() !== '');
        const total = Math.max(0, lines.length - 1); // minus header
        setProgress(0, total);
      };
      reader.readAsText(file);

      const formData = new FormData(importForm);

      fetch(importForm.action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(r => r.json())
      .then(data => {
        if (!data.token) throw new Error('No token returned.');
        // Start polling every 1 second
        pollTimer = setInterval(() => pollProgress(data.token), 1000);
      })
      .catch(err => {
        overlay.style.display = 'none';
        alert('Import start failed. Please check your CSV and try again.');
      });
    });
  }
});
</script>


</x-admin-layout>
