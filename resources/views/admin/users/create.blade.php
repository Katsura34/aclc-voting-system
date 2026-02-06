<x-admin-layout title="Add New User">
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-person-plus-fill"></i> Add New Student</h2>
                <p class="text-muted mb-0">Create a new student or admin account</p>
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
                        <form action="{{ route('admin.users.store') }}" method="POST">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="student_id" class="form-label">
                                        Student ID <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('student_id') is-invalid @enderror" 
                                           id="student_id" 
                                           name="student_id" 
                                           value="{{ old('student_id') }}"
                                           required>
                                    @error('student_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- <div class="col-md-6">
                                    <label for="user_type" class="form-label">
                                        User Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('user_type') is-invalid @enderror" 
                                            id="user_type" 
                                            name="user_type"
                                            required>
                                        <option value="">Select Type</option>
                                        <option value="student" {{ old('user_type') == 'student' ? 'selected' : '' }}>Student</option>
                                        <option value="admin" {{ old('user_type') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    @error('user_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div> --}}
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">
                                        First Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" 
                                           name="first_name" 
                                           value="{{ old('first_name') }}"
                                           required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">
                                        Last Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" 
                                           name="last_name" 
                                           value="{{ old('last_name') }}"
                                           required>
                                    @error('last_name')
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
                                       value="{{ old('email') }}"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="course" class="form-label">Course</label>
                                    <input type="text" 
                                           class="form-control @error('course') is-invalid @enderror" 
                                           id="course" 
                                           name="course" 
                                           value="{{ old('course') }}"
                                           placeholder="e.g., BSIT, BSCS">
                                    @error('course')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="year_level" class="form-label">Year Level</label>
                                    <select class="form-select @error('year_level') is-invalid @enderror" 
                                            id="year_level" 
                                            name="year_level">
                                        <option value="">Select Year Level</option>
                                        <option value="1st Year" {{ old('year_level') == '1st Year' ? 'selected' : '' }}>1st Year</option>
                                        <option value="2nd Year" {{ old('year_level') == '2nd Year' ? 'selected' : '' }}>2nd Year</option>
                                        <option value="3rd Year" {{ old('year_level') == '3rd Year' ? 'selected' : '' }}>3rd Year</option>
                                        <option value="4th Year" {{ old('year_level') == '4th Year' ? 'selected' : '' }}>4th Year</option>
                                    </select>
                                    @error('year_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">
                                        Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password"
                                           required>
                                    <small class="text-muted">Minimum 8 characters</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label">
                                        Confirm Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation"
                                           required>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Create User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
