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
    <?php 
    include resource_path('css/login.css');
    ?>

    
    {{-- <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #003366 0%, #002147 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .login-container {
            display: flex;
            max-width: 900px;
            width: 100%;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.3);
        }
        

        /* Left Panel - Blue and Red Side */
        .left-panel {
            background: linear-gradient(135deg, #003366 0%, #00509E 50%, #CC0000 100%);
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60%;
            background: linear-gradient(180deg, transparent 0%, rgba(204, 0, 0, 0.2) 100%);
            border-radius: 50% 50% 0 0 / 30% 30% 0 0;
        }

        .logo-container {
            position: relative;
            z-index: 1;
            text-align: center;
            width: 100%;
        }

        .main-logo {
            width: 180px;
            height: 180px;
            margin-bottom: 30px;
            background: white;
            border-radius: 50%;
            padding: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
            margin-right: auto;
        }

        .main-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .company-name {
            color: white;
            font-size: 24px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .company-tagline {
            color: rgba(255, 255, 255, 0.95);
            font-size: 14px;
            margin-bottom: 30px;
        }

        .sparkles {
            position: absolute;
            color: white;
            font-size: 24px;
            animation: sparkle 2s infinite;
        }

        .sparkle-1 { top: 15%; left: 15%; animation-delay: 0s; }
        .sparkle-2 { top: 25%; right: 20%; animation-delay: 0.5s; }
        .sparkle-3 { bottom: 30%; left: 10%; animation-delay: 1s; }
        .sparkle-4 { bottom: 20%; right: 15%; animation-delay: 1.5s; }

        @keyframes sparkle {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        .org-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            padding: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px;
        }

        .org-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .org-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-top: 30px;
            position: relative;
            z-index: 1;
        }

        /* Right Panel - Login Form */
        .right-panel {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h2 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #888;
            font-size: 14px;
        }

        .form-group {
            position: relative;   /* 👈 THIS FIXES IT */
            margin-bottom: 25px;
        }

        .form-group .bi-lock-fill,
        .form-group .bi-person-circle {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            font-size: 18px;
        }


       .password-toggle {
            position: absolute;
            right: 15px;
            left: auto;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            font-size: 18px;
            cursor: pointer;
            z-index: 10;
            user-select: none;
        }


        .password-toggle:hover {
            color: #003366;
        }


        .form-control {
            width: 100%;
            padding: 14px 50px 14px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }



        .form-control:focus {
            outline: none;
            border-color: #003366;
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
        }

        .form-control::placeholder {
            color: #aaa;
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            cursor: pointer;
            accent-color: #CC0000;
        }

        .remember-me label {
            font-size: 14px;
            color: #666;
            cursor: pointer;
            user-select: none;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #CC0000 0%, #990000 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(204, 0, 0, 0.4);
            background: linear-gradient(135deg, #003366 0%, #00509E 100%);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: #888;
        }

        .login-footer a {
            color: #CC0000;
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
            color: #003366;
        }
        

        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 12px 15px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .left-panel {
                padding: 40px 30px;
            }

            .right-panel {
                flex: 1;
                padding: 60px 50px;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .main-logo {
                width: 120px;
                height: 120px;
            }

            .company-name {
                font-size: 20px;
            }

            .org-logos {
                flex-wrap: wrap;
            }

            .org-logo {
                width: 60px;
                height: 60px;
            }
        }
    </style> --}}
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
                    <img src="/storage/logos/aclc-logo.png" alt="ACLC College of Ormoc City" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 200 200%27%3E%3Ccircle cx=%27100%27 cy=%27100%27 r=%2780%27 fill=%27%23003366%27/%3E%3Ctext x=%27100%27 y=%27110%27 text-anchor=%27middle%27 fill=%27white%27 font-size=%2724%27 font-weight=%27bold%27%3EACLC%3C/text%3E%3C/svg%3E'">
                </div>
                
                <div class="company-name">ACLC COLLEGE</div>
                <div class="company-tagline">Student Voting System</div>
                
                <div class="org-logos">
                    <!-- Programmers Guild Logo -->
                    <div class="org-logo">
                        <img src="/storage/logos/programmers-guild-logo.png" alt="Programmers Guild" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 200 200%27%3E%3Ccircle cx=%27100%27 cy=%27100%27 r=%2790%27 fill=%27black%27/%3E%3Ctext x=%27100%27 y=%27110%27 text-anchor=%27middle%27 fill=%27white%27 font-size=%2748%27 font-weight=%27bold%27%3EPG%3C/text%3E%3C/svg%3E'">
                    </div>
                    
                    <!-- ACLC Ormoc City Logo -->
                    <div class="org-logo">
                        <img src="/storage/logos/aclc-ormoc-logo.png" alt="ACLC Ormoc City" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 200 200%27%3E%3Ccircle cx=%27100%27 cy=%27100%27 r=%2790%27 fill=%27%23003366%27/%3E%3Ctext x=%27100%27 y=%27110%27 text-anchor=%27middle%27 fill=%27white%27 font-size=%2748%27 font-weight=%27bold%27%3E🗳️%3C/text%3E%3C/svg%3E'">
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