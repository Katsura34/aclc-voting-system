<x-admin-layout title="Party Details">
    <x-slot name="styles">
        <style>
            .party-header {
                background: linear-gradient(135deg, {{ $party->color }}15 0%, {{ $party->color }}30 100%);
                border-left: 5px solid {{ $party->color }};
                padding: 30px;
                border-radius: 10px;
                margin-bottom: 30px;
            }
            .party-logo-large {
                width: 120px;
                height: 120px;
                object-fit: contain;
                background: white;
                padding: 15px;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            .stat-card {
                background: white;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                text-align: center;
                transition: transform 0.2s;
            }
            .stat-card:hover {
                transform: translateY(-5px);
            }
            .stat-icon {
                font-size: 2.5rem;
                margin-bottom: 10px;
            }
            .candidate-card {
                transition: transform 0.2s;
            }
            .candidate-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            }
        </style>
    </x-slot>

    <div class="container-fluid">
        <!-- Header Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-flag"></i> Party Details</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.parties.edit', $party) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit Party
                </a>
                <a href="{{ route('admin.parties.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Parties
                </a>
            </div>
        </div>

        <!-- Party Header -->
        <div class="party-header">
            <div class="row align-items-center">
                <div class="col-auto">
                    @if($party->logo)
                        <img src="{{ asset('storage/' . $party->logo) }}" 
                             alt="{{ $party->name }}" 
                             class="party-logo-large">
                    @else
                        <div class="party-logo-large d-flex align-items-center justify-content-center">
                            <i class="bi bi-flag-fill" style="font-size: 3.5rem; color: {{ $party->color }};"></i>
                        </div>
                    @endif
                </div>
                <div class="col">
                    <h1 class="mb-2">{{ $party->name }}</h1>
                    <span class="badge fs-5" style="background: {{ $party->color }}; color: white;">
                        {{ $party->acronym }}
                    </span>
                    @if($party->description)
                        <p class="mt-3 mb-0">{{ $party->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="color: {{ $party->color }};">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h3 class="mb-0">{{ $party->candidates->count() }}</h3>
                    <p class="text-muted mb-0">Total Candidates</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="color: {{ $party->color }};">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <h3 class="mb-0">{{ $party->candidates->pluck('position_id')->unique()->count() }}</h3>
                    <p class="text-muted mb-0">Positions Contested</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="color: {{ $party->color }};">
                        <i class="bi bi-palette-fill"></i>
                    </div>
                    <h3 class="mb-0" style="color: {{ $party->color }};">{{ $party->color }}</h3>
                    <p class="text-muted mb-0">Party Color</p>
                </div>
            </div>
        </div>

        <!-- Candidates Section -->
        <div class="card">
            <div class="card-header bg-white">
                <h4 class="mb-0">
                    <i class="bi bi-people"></i> Party Candidates
                    <span class="badge bg-primary">{{ $party->candidates->count() }}</span>
                </h4>
            </div>
            <div class="card-body">
                @if($party->candidates->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-people" style="font-size: 4rem; color: #dee2e6;"></i>
                        <h5 class="mt-3 text-muted">No Candidates Yet</h5>
                        <p class="text-muted">This party doesn't have any candidates yet.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Bio</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($party->candidates as $candidate)
                                    <tr class="candidate-card">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-person-circle" style="font-size: 1.5rem; color: {{ $party->color }};"></i>
                                                <div>
                                                    <strong>{{ $candidate->full_name }}</strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $candidate->position->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ Str::limit($candidate->bio, 60) }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: {{ $party->color }}; color: white;">
                                                {{ $party->acronym }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Candidates by Position -->
                    <div class="mt-4">
                        <h5 class="mb-3">Candidates by Position</h5>
                        <div class="row">
                            @foreach($party->candidates->groupBy('position.name') as $positionName => $candidates)
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="mb-3">
                                                <i class="bi bi-award"></i> {{ $positionName }}
                                                <span class="badge bg-primary">{{ $candidates->count() }}</span>
                                            </h6>
                                            <ul class="list-unstyled mb-0">
                                                @foreach($candidates as $candidate)
                                                    <li class="mb-2">
                                                        <i class="bi bi-person-fill" style="color: {{ $party->color }};"></i>
                                                        {{ $candidate->full_name }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('admin.parties.edit', $party) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit Party
            </a>
            <button type="button" class="btn btn-danger" onclick="deleteParty()">
                <i class="bi bi-trash"></i> Delete Party
            </button>
            <form id="delete-form" action="{{ route('admin.parties.destroy', $party) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            function deleteParty() {
                const candidatesCount = {{ $party->candidates->count() }};
                
                if (candidatesCount > 0) {
                    alert(`Cannot delete this party!\n\nThis party has ${candidatesCount} candidate(s). Please remove all candidates first.`);
                    return;
                }
                
                if (confirm('Are you sure you want to delete "{{ $party->name }}"?\n\nThis action cannot be undone.')) {
                    document.getElementById('delete-form').submit();
                }
            }
        </script>
    </x-slot>
</x-admin-layout>
