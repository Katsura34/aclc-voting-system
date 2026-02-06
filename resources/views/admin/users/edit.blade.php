<x-admin-layout title="Edit Student">
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-pencil-square"></i> Edit Student</h2>
                <p class="text-muted mb-0">Update student information</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Students
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-card-text"></i> Student Information</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.users.update', $user) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="usn" class="form-label">
                                    USN <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('usn') is-invalid @enderror" 
                                       id="usn" 
                                       name="usn" 
                                       value="{{ old('usn', $user->usn) }}"
                                       required>
                                @error('usn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">
                                        Last Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" 
                                           name="last_name" 
                                           value="{{ old('last_name', $user->last_name) }}"
                                           required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">
                                        First Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" 
                                           name="first_name" 
                                           value="{{ old('first_name', $user->first_name) }}"
                                           required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="strand" class="form-label">Strand</label>
                                    <input type="text" 
                                           class="form-control @error('strand') is-invalid @enderror" 
                                           id="strand" 
                                           name="strand" 
                                           value="{{ old('strand', $user->strand) }}"
                                           placeholder="e.g., STEM, ABM, HUMSS">
                                    @error('strand')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="year" class="form-label">Year</label>
                                    <input type="text" 
                                           class="form-control @error('year') is-invalid @enderror" 
                                           id="year" 
                                           name="year" 
                                           value="{{ old('year', $user->year) }}"
                                           placeholder="e.g., 1st Year, 2nd Year">
                                    @error('year')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select @error('gender') is-invalid @enderror" 
                                            id="gender" 
                                            name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="Male" {{ old('gender', $user->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender', $user->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="has_voted" class="form-label">Voting Status</label>
                                <select class="form-select @error('has_voted') is-invalid @enderror" 
                                        id="has_voted" 
                                        name="has_voted">
                                    <option value="0" {{ old('has_voted', $user->has_voted) == 0 ? 'selected' : '' }}>Not Voted</option>
                                    <option value="1" {{ old('has_voted', $user->has_voted) == 1 ? 'selected' : '' }}>Has Voted</option>
                                </select>
                                @error('has_voted')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Leave password fields empty to keep current password
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password">
                                    <small class="text-muted">Minimum 8 characters</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation">
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Update Student
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
