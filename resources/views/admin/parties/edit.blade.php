<x-admin-layout title="Edit Party">
    <x-slot name="styles">
        <style>
            .form-section {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            .color-preview {
                width: 100px;
                height: 100px;
                border-radius: 10px;
                border: 3px solid #dee2e6;
                transition: all 0.3s ease;
            }
            .logo-preview-container {
                width: 150px;
                height: 150px;
                border: 2px dashed #dee2e6;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f8f9fa;
                overflow: hidden;
            }
            .logo-preview {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
            }
            .current-logo {
                max-width: 150px;
                max-height: 150px;
                object-fit: contain;
            }
        </style>
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-pencil"></i> Edit Party</h2>
                <p class="text-muted mb-0">Update party information</p>
            </div>
            <a href="{{ route('admin.parties.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Parties
            </a>
        </div>

        <!-- Form -->
        <div class="row">
            <div class="col-lg-8">
                <div class="form-section">
                    <form action="{{ route('admin.parties.update', $party) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Party Name -->
                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold">
                                Party Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $party->name) }}"
                                   placeholder="e.g., Liberal Democratic Party"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Acronym -->
                        <div class="mb-4">
                            <label for="acronym" class="form-label fw-bold">
                                Acronym <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('acronym') is-invalid @enderror" 
                                   id="acronym" 
                                   name="acronym" 
                                   value="{{ old('acronym', $party->acronym) }}"
                                   placeholder="e.g., LDP"
                                   maxlength="10"
                                   required>
                            <small class="text-muted">Short form of the party name (max 10 characters)</small>
                            @error('acronym')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Color Picker -->
                        <div class="mb-4">
                            <label for="color" class="form-label fw-bold">
                                Party Color <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="color" 
                                       class="form-control form-control-color @error('color') is-invalid @enderror" 
                                       id="color" 
                                       name="color" 
                                       value="{{ old('color', $party->color) }}"
                                       required
                                       style="width: 80px; height: 50px;">
                                <div class="color-preview" id="colorPreview"></div>
                                <div>
                                    <small class="text-muted d-block">This color will be used for party branding</small>
                                    <small class="text-muted d-block">Selected: <strong id="colorValue">{{ old('color', $party->color) }}</strong></small>
                                </div>
                            </div>
                            @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Current Logo -->
                        @if($party->logo)
                            <div class="mb-4">
                                <label class="form-label fw-bold">Current Logo</label>
                                <div>
                                    <img src="{{ asset('storage/' . $party->logo) }}" 
                                         alt="{{ $party->name }}" 
                                         class="current-logo border rounded p-2">
                                </div>
                            </div>
                        @endif

                        <!-- Logo Upload -->
                        <div class="mb-4">
                            <label for="logo" class="form-label fw-bold">
                                {{ $party->logo ? 'Replace Logo' : 'Party Logo' }}
                            </label>
                            <input type="file" 
                                   class="form-control @error('logo') is-invalid @enderror" 
                                   id="logo" 
                                   name="logo"
                                   accept="image/*">
                            <small class="text-muted">Supported formats: JPG, PNG, GIF (Max 2MB)</small>
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Logo Preview -->
                            <div class="mt-3">
                                <div class="logo-preview-container" id="logoPreviewContainer">
                                    @if($party->logo)
                                        <img src="{{ asset('storage/' . $party->logo) }}" class="logo-preview" alt="Logo Preview">
                                    @else
                                        <span class="text-muted">
                                            <i class="bi bi-image" style="font-size: 2rem;"></i>
                                            <div>New Logo Preview</div>
                                        </span>
                                    @endif
                                </div>
                            </div>
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
                                      placeholder="Brief description of the party's platform and values...">{{ old('description', $party->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Party
                            </button>
                            <a href="{{ route('admin.parties.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="col-lg-4">
                <div class="card" style="position: sticky; top: 20px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-eye"></i> Live Preview</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <div class="logo-preview-container mx-auto" id="previewLogo">
                                @if($party->logo)
                                    <img src="{{ asset('storage/' . $party->logo) }}" class="logo-preview" alt="Preview">
                                @else
                                    <i class="bi bi-flag-fill" style="font-size: 3rem; color: {{ $party->color }};"></i>
                                @endif
                            </div>
                        </div>
                        <h4 id="previewName" class="mb-2">{{ $party->name }}</h4>
                        <span class="badge mb-3" id="previewAcronym" style="background: {{ $party->color }}; color: white;">
                            {{ $party->acronym }}
                        </span>
                        <p class="text-muted small" id="previewDescription">
                            {{ $party->description ?: 'Party description will appear here...' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            // Color preview
            const colorInput = document.getElementById('color');
            const colorPreview = document.getElementById('colorPreview');
            const colorValue = document.getElementById('colorValue');
            const previewAcronym = document.getElementById('previewAcronym');

            function updateColorPreview() {
                const color = colorInput.value;
                colorPreview.style.background = color;
                colorValue.textContent = color;
                previewAcronym.style.background = color;
            }

            colorInput.addEventListener('input', updateColorPreview);
            updateColorPreview();

            // Logo preview
            const logoInput = document.getElementById('logo');
            const logoPreviewContainer = document.getElementById('logoPreviewContainer');
            const previewLogoContainer = document.getElementById('previewLogo');

            logoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = `<img src="${e.target.result}" class="logo-preview" alt="Logo Preview">`;
                        logoPreviewContainer.innerHTML = img;
                        previewLogoContainer.innerHTML = img;
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Name preview
            const nameInput = document.getElementById('name');
            const previewName = document.getElementById('previewName');

            nameInput.addEventListener('input', function() {
                previewName.textContent = this.value || 'Party Name';
            });

            // Acronym preview
            const acronymInput = document.getElementById('acronym');

            acronymInput.addEventListener('input', function() {
                previewAcronym.textContent = this.value || 'ACRONYM';
            });

            // Description preview
            const descriptionInput = document.getElementById('description');
            const previewDescription = document.getElementById('previewDescription');

            descriptionInput.addEventListener('input', function() {
                previewDescription.textContent = this.value || 'Party description will appear here...';
            });
        </script>
    </x-slot>
</x-admin-layout>
