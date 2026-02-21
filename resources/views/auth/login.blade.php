<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Voting System</title>
    {{-- <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet"> --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/login.css'])

</head>
<body>
    <div class="login-container">
        <!-- Left Panel with Logos -->
        <div class="left-panel">
            <div class="sparkles sparkle-1">✦</div>
            <div class="sparkles sparkle-2">✦</div>
            <div class="sparkles sparkle-3">✦</div>
            <div class="sparkles sparkle-4">✦</div>

            <div class="logo-container">
                <div class="main-logo">
                    <!-- ACLC Logo -->
                    <img src="{{ asset('storage/logos/aclc-logo.png') }}" alt="ACLC College of Ormoc City" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 200 200%27%3E%3Ccircle cx=%27100%27 cy=%27100%27 r=%2780%27 fill=%27%23003366%27/%3E%3Ctext x=%27100%27 y=%27110%27 text-anchor=%27middle%27 fill=%27white%27 font-size=%2724%27 font-weight=%27bold%27%3EACLC%3C/text%3E%3C/svg%3E'">
                </div>
                
                <div class="company-name">ACLC COLLEGE</div>
                <div class="company-tagline">BSP Voting System</div>
                
                <div class="org-logos">
                    <!-- Programmers Guild Logo -->
                    <div class="org-logo">
                        <img src="{{ asset('storage/logos/programmers-guild-logo.png') }}" alt="Programmers Guild" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 200 200%27%3E%3Ccircle cx=%27100%27 cy=%27100%27 r=%2790%27 fill=%27black%27/%3E%3Ctext x=%27100%27 y=%27110%27 text-anchor=%27middle%27 fill=%27white%27 font-size=%2748%27 font-weight=%27bold%27%3EPG%3C/text%3E%3C/svg%3E'">
                    </div>
                    
                    <!-- ACLC Ormoc City Logo -->
                    <div class="org-logo">
                        <img src="{{ asset('storage/logos/aclc-logo.png') }}" alt="ACLC Ormoc City" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 200 200%27%3E%3Ccircle cx=%27100%27 cy=%27100%27 r=%2790%27 fill=%27%23003366%27/%3E%3Ctext x=%27100%27 y=%27110%27 text-anchor=%27middle%27 fill=%27white%27 font-size=%2748%27 font-weight=%27bold%27%3E🗳️%3C/text%3E%3C/svg%3E'">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel with Login Form -->
        <div class="right-panel">
            <div class="login-header">
                <h2>Log in</h2>
                <p>Enter your credentials to access your account</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong>Error!</strong> {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" autocomplete="on">
                @csrf

                <div class="form-group">
                    <i class="bi bi-person-circle"></i>
                    <input 
                        type="text" 
                        id="usn" 
                        name="usn" 
                        class="form-control @error('usn') is-invalid @enderror" 
                        value="{{ old('usn') }}" 
                        placeholder="Username (USN)"
                        autocomplete="username"
                        required 
                        autofocus
                    >
                    @error('usn')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <i class="bi bi-lock-fill"></i>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        placeholder="Password"
                        autocomplete="current-password"
                        required
                    >
                   
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
                </div>


                <button type="submit" class="btn-login">Log In</button>
            </form>


        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> --}}

    
    <!-- Password Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    // Toggle the password field type
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Toggle the icon
                    if (type === 'password') {
                        this.classList.remove('bi-eye');
                        this.classList.add('bi-eye-slash');
                    } else {
                        this.classList.remove('bi-eye-slash');
                        this.classList.add('bi-eye');
                    }
                });
            }
        });
    </script>
</body>
</html>