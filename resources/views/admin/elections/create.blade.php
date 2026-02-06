<x-admin-layout title="Create Election">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="bi bi-plus-circle"></i> Create New Election
        </h1>
    </div>

    <!-- Form -->
    <div class="card">
        <form action="{{ route('admin.elections.store') }}" method="POST">
            @csrf

            <!-- Basic Information -->
            <div class="section-title">
                <i class="bi bi-info-circle"></i> Basic Information
            </div>

            <div class="mb-4">
                <label for="title" class="form-label">Election Title <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control @error('title') is-invalid @enderror" 
                       id="title" 
                       name="title" 
                       value="{{ old('title') }}" 
                       placeholder="e.g., Student Council Election 2025"
                       required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" 
                          name="description" 
                          rows="3"
                          placeholder="Brief description of the election">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="help-text">Provide a brief description that will be shown to voters.</div>
            </div>

            <!-- Date Settings -->
            <div class="section-title mt-4">
                <i class="bi bi-calendar-range"></i> Schedule
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="start_date" class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                    <input type="datetime-local" 
                           class="form-control @error('start_date') is-invalid @enderror" 
                           id="start_date" 
                           name="start_date" 
                           value="{{ old('start_date') }}"
                           required>
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="end_date" class="form-label">End Date & Time <span class="text-danger">*</span></label>
                    <input type="datetime-local" 
                           class="form-control @error('end_date') is-invalid @enderror" 
                           id="end_date" 
                           name="end_date" 
                           value="{{ old('end_date') }}"
                           required>
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Election Settings -->
            <div class="section-title mt-4">
                <i class="bi bi-gear"></i> Election Settings
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        <strong>Set as Active Election</strong>
                        <div class="help-text">Only one election can be active at a time. Activating this will deactivate others.</div>
                    </label>
                </div>
            </div>

            <!-- Positions Selection -->
            <div class="section-title mt-4">
                <i class="bi bi-award"></i> Positions
            </div>

            <div class="mb-4">
                <label class="form-label">Select Positions for this Election</label>
                <div class="help-text mb-2">Choose which positions will be part of this election. Each position can only be selected once.</div>
                @if($positions->count() > 0)
                    <div class="row">
                        @foreach($positions as $position)
                            <div class="col-md-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="positions[]" 
                                           value="{{ $position->id }}" 
                                           id="position_{{ $position->id }}"
                                           {{ in_array($position->id, old('positions', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="position_{{ $position->id }}">
                                        {{ $position->name }}
                                        @if($position->description)
                                            <small class="text-muted d-block">{{ $position->description }}</small>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No positions available. <a href="{{ route('admin.positions.create') }}">Create positions first.</a></p>
                @endif
                @error('positions')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Parties Selection -->
            <div class="section-title mt-4">
                <i class="bi bi-flag"></i> Parties
            </div>

            <div class="mb-4">
                <label class="form-label">Select Parties for this Election</label>
                <div class="help-text mb-2">Choose which parties will participate in this election.</div>
                @if($parties->count() > 0)
                    <div class="row">
                        @foreach($parties as $party)
                            <div class="col-md-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="parties[]" 
                                           value="{{ $party->id }}" 
                                           id="party_{{ $party->id }}"
                                           {{ in_array($party->id, old('parties', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="party_{{ $party->id }}">
                                        {{ $party->name }}
                                        @if($party->acronym)
                                            <span class="badge bg-secondary">{{ $party->acronym }}</span>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No parties available. <a href="{{ route('admin.parties.create') }}">Create parties first.</a></p>
                @endif
                @error('parties')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Create Election
                </button>
                <a href="{{ route('admin.elections.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>

    <x-slot name="scripts">
        <script>
            // Set minimum date to today for start_date
            document.getElementById('start_date').min = new Date().toISOString().slice(0, 16);
            
            // Update end_date minimum when start_date changes
            document.getElementById('start_date').addEventListener('change', function() {
                document.getElementById('end_date').min = this.value;
            });
        </script>
    </x-slot>
</x-admin-layout>
