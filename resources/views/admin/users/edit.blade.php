<x-admin-layout title="Edit User">
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-pencil-square"></i> Edit Student</h2>
                <p class="text-muted mb-0">Update Student information</p>
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

                            <div class="row mb-3">
                                <div class="col-md-6">
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

                                <div class="col-md-6">
                                    <label for="user_type" class="form-label">
                                        User Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('user_type') is-invalid @enderror" 
                                            id="user_type" 
                                            name="user_type"
                                            required>
                                        <option value="">Select Type</option>
                                        <option value="student" {{ old('user_type', $user->user_type) == 'student' ? 'selected' : '' }}>Student</option>
                                        <option value="admin" {{ old('user_type', $user->user_type) == 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    @error('user_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="firstname" class="form-label">
                                        First Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('firstname') is-invalid @enderror" 
                                           id="firstname" 
                                           name="firstname" 
                                           value="{{ old('firstname', $user->firstname) }}"
                                           required>
                                    @error('firstname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="lastname" class="form-label">
                                        Last Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('lastname') is-invalid @enderror" 
                                           id="lastname" 
                                           name="lastname" 
                                           value="{{ old('lastname', $user->lastname) }}"
                                           required>
                                    @error('lastname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    Email Address <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $user->email) }}"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                    <select class="form-select @error('year') is-invalid @enderror" 
                                            id="year" 
                                            name="year">
                                        <option value="">Select Year</option>
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}" {{ old('year', $user->year) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
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
                                        <option value="Other" {{ old('gender', $user->gender) == 'Other' ? 'selected' : '' }}>Other</option>
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
                                    <i class="bi bi-save"></i> Update User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
