<x-admin-layout title="Edit Candidate">
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
            .candidate-preview {
                border: 2px solid #dee2e6;
                border-radius: 10px;
                padding: 20px;
                text-align: center;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            }
            .preview-avatar {
                width: 100px;
                height: 100px;
                border-radius: 50%;
                background: {{ $candidate->party->color ?? 'var(--aclc-blue)' }};
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 3rem;
                margin: 0 auto 15px;
            }
        </style>
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-pencil"></i> Edit Candidate</h2>
                <p class="text-muted mb-0">Update candidate information</p>
            </div>
            <a href="{{ route('admin.candidates.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Candidates
            </a>
        </div>

        <!-- Form -->
        <div class="row">
            <div class="col-lg-8">
                <div class="form-section">
                    <form action="{{ route('admin.candidates.update', $candidate) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3"><i class="bi bi-person"></i> Personal Information</h5>
                        
                        <div class="row">
                            <!-- First Name -->
                            <div class="col-md-6 mb-4">
                                <label for="first_name" class="form-label fw-bold">
                                    First Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('first_name') is-invalid @enderror" 
                                       id="first_name" 
                                       name="first_name" 
                                       value="{{ old('first_name', $candidate->first_name) }}"
                                       placeholder="e.g., Juan"
                                       required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div class="col-md-6 mb-4">
                                <label for="last_name" class="form-label fw-bold">
                                    Last Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('last_name') is-invalid @enderror" 
                                       id="last_name" 
                                       name="last_name" 
                                       value="{{ old('last_name', $candidate->last_name) }}"
                                       placeholder="e.g., Dela Cruz"
                                       required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Current Photo and Upload -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Candidate Photo
                            </label>
                            
                            @if($candidate->photo_path)
                                <div class="mb-3">
                                    <img src="{{ asset('storage/' . $candidate->photo_path) }}" 
                                         alt="{{ $candidate->full_name }}"
                                         id="currentPhoto"
                                         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; display: block; margin-bottom: 10px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remove_photo" id="remove_photo" value="1">
                                        <label class="form-check-label" for="remove_photo">
                                            Remove current photo
                                        </label>
                                    </div>
                                </div>
                            @endif
                            
                            <input type="file" 
                                   class="form-control @error('photo') is-invalid @enderror" 
                                   id="photo" 
                                   name="photo" 
                                   accept="image/jpeg,image/jpg,image/png">
                            <small class="text-muted">
                                @if($candidate->photo_path)
                                    Upload a new photo to replace the current one, or check the box above to remove it
                                @else
                                    Upload a photo for this candidate
                                @endif
                                (Accepted formats: JPG, JPEG, PNG, Max: 2MB)
                            </small>
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3"><i class="bi bi-award"></i> Election Details</h5>

                        <div class="row">
                            <!-- Election (for filtering positions) -->
                            <div class="col-md-12 mb-4">
                                <label for="election_filter" class="form-label fw-bold">
                                    Election
                                </label>
                                <select class="form-select" id="election_filter">
                                    <option value="">All Elections</option>
                                    @foreach($elections as $election)
                                        <option value="{{ $election->id }}" 
                                                {{ $candidate->position->election_id == $election->id ? 'selected' : '' }}>
                                            {{ $election->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Filter positions by election</small>
                            </div>

                            <!-- Position -->
                            <div class="col-md-6 mb-4">
                                <label for="position_id" class="form-label fw-bold">
                                    Position <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('position_id') is-invalid @enderror" 
                                        id="position_id" 
                                        name="position_id" 
                                        required>
                                    <option value="">Select Position</option>
                                    @foreach($positions as $position)
                                        <option value="{{ $position->id }}" 
                                                data-election="{{ $position->election_id }}"
                                                {{ old('position_id', $candidate->position_id) == $position->id ? 'selected' : '' }}>
                                            {{ $position->name }} - {{ $position->election->title ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('position_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Party -->
                            <div class="col-md-6 mb-4">
                                <label for="party_id" class="form-label fw-bold">
                                    Party <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('party_id') is-invalid @enderror" 
                                        id="party_id" 
                                        name="party_id" 
                                        required>
                                    <option value="">Select Party</option>
                                    @foreach($parties as $party)
                                        <option value="{{ $party->id }}" 
                                                data-color="{{ $party->color }}"
                                                data-acronym="{{ $party->acronym }}"
                                                {{ old('party_id', $candidate->party_id) == $party->id ? 'selected' : '' }}>
                                            {{ $party->name }} ({{ $party->acronym }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('party_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3"><i class="bi bi-file-text"></i> Candidate Information</h5>

                        <!-- Bio -->
                        <div class="mb-4">
                            <label for="bio" class="form-label fw-bold">
                                Biography
                            </label>
                            <textarea class="form-control @error('bio') is-invalid @enderror" 
                                      id="bio" 
                                      name="bio" 
                                      rows="3"
                                      placeholder="Brief background and qualifications...">{{ old('bio', $candidate->bio) }}</textarea>
                            <small class="text-muted">A short biography of the candidate</small>
                            @error('bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Platform -->
                        <div class="mb-4">
                            <label for="platform" class="form-label fw-bold">
                                Platform
                            </label>
                            <textarea class="form-control @error('platform') is-invalid @enderror" 
                                      id="platform" 
                                      name="platform" 
                                      rows="4"
                                      placeholder="Campaign promises and platform...">{{ old('platform', $candidate->platform) }}</textarea>
                            <small class="text-muted">Candidate's campaign platform and promises</small>
                            @error('platform')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Candidate
                            </button>
                            <a href="{{ route('admin.candidates.index') }}" class="btn btn-outline-secondary">
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
                            <h5 class="mb-0"><i class="bi bi-eye"></i> Live Preview</h5>
                        </div>
                        <div class="card-body">
                            <div class="candidate-preview">
                                @if($candidate->photo_path)
                                    <img src="{{ asset('storage/' . $candidate->photo_path) }}" 
                                         alt="{{ $candidate->full_name }}"
                                         id="previewImage"
                                         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 15px; display: block;">
                                    <div class="preview-avatar" id="previewAvatar" style="display: none;">
                                        {{ substr($candidate->first_name, 0, 1) }}
                                    </div>
                                @else
                                    <div class="preview-avatar" id="previewAvatar">
                                        {{ substr($candidate->first_name, 0, 1) }}
                                    </div>
                                    <img id="previewImage" src="" alt="Preview" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 15px; display: none;">
                                @endif
                                <h4 id="previewName" class="mb-2">{{ $candidate->full_name }}</h4>
                                <span class="badge mb-2" id="previewPosition" style="background: var(--aclc-light-blue);">
                                    {{ $candidate->position->name ?? 'Position' }}
                                </span>
                                <br>
                                <span class="badge mb-3" id="previewParty" style="background: {{ $candidate->party->color ?? 'var(--aclc-blue)' }};">
                                    {{ $candidate->party->acronym ?? 'Party' }}
                                </span>
                                <p class="text-muted small mb-2" id="previewBio">
                                    {{ $candidate->bio ?: 'Biography will appear here...' }}
                                </p>
                                <hr>
                                <p class="text-muted small mb-0" id="previewPlatform">
                                    {{ $candidate->platform ?: 'Platform will appear here...' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            // Preview elements
            const previewName = document.getElementById('previewName');
            const previewPosition = document.getElementById('previewPosition');
            const previewParty = document.getElementById('previewParty');
            const previewBio = document.getElementById('previewBio');
            const previewPlatform = document.getElementById('previewPlatform');
            const previewAvatar = document.getElementById('previewAvatar');
            const previewImage = document.getElementById('previewImage');

            // Form elements
            const firstNameInput = document.getElementById('first_name');
            const lastNameInput = document.getElementById('last_name');
            const positionSelect = document.getElementById('position_id');
            const partySelect = document.getElementById('party_id');
            const bioInput = document.getElementById('bio');
            const platformInput = document.getElementById('platform');
            const electionFilter = document.getElementById('election_filter');
            const photoInput = document.getElementById('photo');
            const removePhotoCheckbox = document.getElementById('remove_photo');
            const currentPhoto = document.getElementById('currentPhoto');

            // Handle photo upload preview
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewImage.style.display = 'block';
                        previewAvatar.style.display = 'none';
                        if (removePhotoCheckbox) {
                            removePhotoCheckbox.checked = false;
                            removePhotoCheckbox.disabled = true;
                        }
                    };
                    reader.readAsDataURL(file);
                } else {
                    @if($candidate->photo_path)
                        previewImage.src = "{{ asset('storage/' . $candidate->photo_path) }}";
                        previewImage.style.display = 'block';
                        previewAvatar.style.display = 'none';
                    @else
                        previewImage.style.display = 'none';
                        previewAvatar.style.display = 'flex';
                    @endif
                    if (removePhotoCheckbox) {
                        removePhotoCheckbox.disabled = false;
                    }
                }
            });

            // Handle remove photo checkbox
            if (removePhotoCheckbox) {
                removePhotoCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        previewImage.style.display = 'none';
                        previewAvatar.style.display = 'flex';
                        if (currentPhoto) {
                            currentPhoto.style.opacity = '0.3';
                        }
                    } else {
                        @if($candidate->photo_path)
                            previewImage.style.display = 'block';
                            previewAvatar.style.display = 'none';
                        @endif
                        if (currentPhoto) {
                            currentPhoto.style.opacity = '1';
                        }
                    }
                });
            }

            // Update name preview
            function updateNamePreview() {
                const firstName = firstNameInput.value || 'Candidate';
                const lastName = lastNameInput.value || 'Name';
                const fullName = `${firstName} ${lastName}`;
                previewName.textContent = fullName;
                
                // Update avatar initial
                const initial = firstName.charAt(0).toUpperCase();
                previewAvatar.textContent = initial || 'C';
            }

            firstNameInput.addEventListener('input', updateNamePreview);
            lastNameInput.addEventListener('input', updateNamePreview);

            // Update position preview
            positionSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const positionText = selectedOption.text.split(' - ')[0];
                previewPosition.textContent = positionText || 'Position';
            });

            // Update party preview
            partySelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const partyColor = selectedOption.dataset.color || '#003366';
                const partyAcronym = selectedOption.dataset.acronym || 'Party';
                
                previewParty.textContent = partyAcronym;
                previewParty.style.background = partyColor;
                previewAvatar.style.background = partyColor;
            });

            // Update bio preview
            bioInput.addEventListener('input', function() {
                previewBio.textContent = this.value || 'Biography will appear here...';
            });

            // Update platform preview
            platformInput.addEventListener('input', function() {
                previewPlatform.textContent = this.value || 'Platform will appear here...';
            });

            // Election filter
            electionFilter.addEventListener('change', function() {
                const selectedElection = this.value;
                const positionOptions = positionSelect.querySelectorAll('option');
                
                positionOptions.forEach(option => {
                    if (option.value === '') {
                        option.style.display = 'block';
                        return;
                    }
                    
                    if (selectedElection === '' || option.dataset.election === selectedElection) {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'none';
                    }
                });
                
                // Reset position selection if hidden
                const selectedOption = positionSelect.options[positionSelect.selectedIndex];
                if (selectedOption && selectedOption.style.display === 'none') {
                    positionSelect.value = '';
                }
            });
        </script>
    </x-slot>
</x-admin-layout>
