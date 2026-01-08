<x-admin-layout title="Edit Election">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="bi bi-pencil"></i> Edit Election
        </h1>
    </div>

    <!-- Form -->
    <div class="card">
        <form action="{{ route('admin.elections.update', $election) }}" method="POST">
            @csrf
            @method('PUT')

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
                       value="{{ old('title', $election->title) }}" 
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
                          placeholder="Brief description of the election">{{ old('description', $election->description) }}</textarea>
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
                           value="{{ old('start_date', $election->start_date->format('Y-m-d\TH:i')) }}"
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
                           value="{{ old('end_date', $election->end_date->format('Y-m-d\TH:i')) }}"
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
                           {{ old('is_active', $election->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        <strong>Set as Active Election</strong>
                        <div class="help-text">Only one election can be active at a time. Activating this will deactivate others.</div>
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="show_live_results" 
                           name="show_live_results" 
                           value="1"
                           {{ old('show_live_results', $election->show_live_results) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_live_results">
                        <strong>Show Live Results</strong>
                        <div class="help-text">Display real-time voting results during the election period.</div>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update Election
                </button>
                <a href="{{ route('admin.elections.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>

    <x-slot name="scripts">
        <script>
            // Update end_date minimum when start_date changes
            document.getElementById('start_date').addEventListener('change', function() {
                document.getElementById('end_date').min = this.value;
            });
        </script>
    </x-slot>
</x-admin-layout>
