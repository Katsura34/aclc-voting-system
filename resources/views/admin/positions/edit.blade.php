<x-admin-layout title="Edit Position">
    <x-slot name="styles">
        <style>
            .form-section {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            .preview-card {
                position: sticky;
                top: 20px;
            }
        </style>
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-pencil"></i> Edit Position</h2>
                <p class="text-muted mb-0">Update position details</p>
            </div>
            <a href="{{ route('admin.positions.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Positions
            </a>
        </div>

        <!-- Form -->
        <div class="row">
            <div class="col-lg-8">
                <div class="form-section">
                    <form action="{{ route('admin.positions.update', $position) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3"><i class="bi bi-info-circle"></i> Position Information</h5>
                        
                        <!-- Position Name -->
                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold">
                                Position Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $position->name) }}"
                                   placeholder="e.g., President, Vice President, Secretary"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">
                                Description
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      placeholder="Brief description of the position's responsibilities...">{{ old('description', $position->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Election -->
                        <div class="mb-4">
                            <label for="election_id" class="form-label fw-bold">
                                Election <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('election_id') is-invalid @enderror" 
                                    id="election_id" 
                                    name="election_id" 
                                    required>
                                <option value="">Select Election</option>
                                @foreach($elections as $election)
                                    <option value="{{ $election->id }}" 
                                            {{ old('election_id', $position->election_id) == $election->id ? 'selected' : '' }}>
                                        {{ $election->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('election_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($position->candidates->count() > 0)
                                <small class="text-warning">
                                    <i class="bi bi-exclamation-triangle"></i> 
                                    Warning: This position has {{ $position->candidates->count() }} candidate(s). 
                                    Changing the election may cause issues.
                                </small>
                            @endif
                        </div>

                        <div class="row">
                            <!-- Max Votes -->
                            <div class="col-md-6 mb-4">
                                <label for="max_votes" class="form-label fw-bold">
                                    Maximum Votes <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('max_votes') is-invalid @enderror" 
                                       id="max_votes" 
                                       name="max_votes" 
                                       value="{{ old('max_votes', $position->max_votes) }}"
                                       min="1"
                                       required>
                                <small class="text-muted">Number of candidates a voter can select for this position</small>
                                @error('max_votes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Display Order -->
                            <div class="col-md-6 mb-4">
                                <label for="display_order" class="form-label fw-bold">
                                    Display Order
                                </label>
                                <input type="number" 
                                       class="form-control @error('display_order') is-invalid @enderror" 
                                       id="display_order" 
                                       name="display_order" 
                                       value="{{ old('display_order', $position->display_order) }}"
                                       min="0">
                                <small class="text-muted">Order in which this position appears (lower numbers first)</small>
                                @error('display_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Position
                            </button>
                            <a href="{{ route('admin.positions.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="col-lg-4">
                <div class="preview-card">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-eye"></i> Preview</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="bi bi-award-fill" style="font-size: 4rem; color: var(--aclc-blue);"></i>
                            </div>
                            <h4 class="text-center mb-3" id="preview-name">{{ $position->name }}</h4>
                            <p class="text-muted text-center" id="preview-description">{{ $position->description ?: 'Description will appear here...' }}</p>
                            
                            <hr>
                            
                            <div class="mb-2">
                                <strong>Election:</strong>
                                <span id="preview-election" class="float-end badge bg-success">{{ $position->election->title ?? 'Not selected' }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Max Votes:</strong>
                                <span id="preview-max-votes" class="float-end badge bg-info">{{ $position->max_votes }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Display Order:</strong>
                                <span id="preview-display-order" class="float-end badge bg-secondary">{{ $position->display_order ?? 0 }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Candidates:</strong>
                                <span class="float-end badge bg-primary">{{ $position->candidates->count() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Help Card -->
                    <div class="card mt-3">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Help</h6>
                        </div>
                        <div class="card-body">
                            <small>
                                <strong>Max Votes:</strong> Set to 1 if voters can choose only one candidate. 
                                Set higher for positions where multiple winners are allowed.<br><br>
                                <strong>Display Order:</strong> Lower numbers appear first in the voting interface.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            // Live preview
            document.getElementById('name').addEventListener('input', function() {
                document.getElementById('preview-name').textContent = this.value || 'Position Name';
            });

            document.getElementById('description').addEventListener('input', function() {
                document.getElementById('preview-description').textContent = this.value || 'Description will appear here...';
            });

            document.getElementById('election_id').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                document.getElementById('preview-election').textContent = selectedOption.text === 'Select Election' ? 'Not selected' : selectedOption.text;
            });

            document.getElementById('max_votes').addEventListener('input', function() {
                document.getElementById('preview-max-votes').textContent = this.value || '1';
            });

            document.getElementById('display_order').addEventListener('input', function() {
                document.getElementById('preview-display-order').textContent = this.value || '0';
            });
        </script>
    </x-slot>
</x-admin-layout>
