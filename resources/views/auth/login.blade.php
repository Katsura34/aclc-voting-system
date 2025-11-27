<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ACLC Voting System</title>
    
    <!-- Google Fonts - Inter for better readability -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-blue: #1e3a5f;
            --primary-blue-light: #2d5a87;
            --primary-blue-dark: #0f2744;
            --accent-red: #dc3545;
            --accent-gold: #f59e0b;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --radius-md: 10px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
            -webkit-font-smoothing: antialiased;
        }

        /* ===== LEFT BRANDING PANEL ===== */
        .brand-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at center, rgba(255,255,255,0.05) 0%, transparent 50%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .brand-content {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 400px;
        }

        .brand-logo {
            width: 140px;
            height: 140px;
            margin: 0 auto 2rem;
            background: white;
            border-radius: 50%;
            padding: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand-name {
            color: white;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: 0.025em;
        }

        .brand-tagline {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
            margin-bottom: 2.5rem;
        }

        .brand-features {
            text-align: left;
            color: white;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .feature-icon i {
            font-size: 1.25rem;
            color: white;
        }

        .feature-text h4 {
            font-size: 0.9375rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .feature-text p {
            font-size: 0.8125rem;
            color: rgba(255, 255, 255, 0.7);
            margin: 0;
        }

        /* ===== RIGHT LOGIN PANEL ===== */
        .login-panel {
            width: 100%;
            max-width: 480px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
            padding: 3rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--gray-500);
            font-size: 0.9375rem;
        }

        /* ===== FORM STYLES ===== */
        .login-form {
            width: 100%;
            max-width: 360px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .form-label .required {
            color: var(--accent-red);
            margin-left: 2px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 1.125rem;
            pointer-events: none;
            transition: color 0.2s;
        }

        .input-wrapper:focus-within i {
            color: var(--primary-blue);
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            border: 1.5px solid var(--gray-300);
            border-radius: var(--radius-md);
            font-size: 0.9375rem;
            color: var(--gray-700);
            background: white;
            transition: all 0.2s;
        }

        .form-control:hover {
            border-color: var(--gray-400);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.15);
        }

        .form-control::placeholder {
            color: var(--gray-400);
        }

        .form-control.is-invalid {
            border-color: var(--accent-red);
        }

        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.15);
        }

        .invalid-feedback {
            display: block;
            font-size: 0.8125rem;
            color: var(--accent-red);
            margin-top: 0.375rem;
        }

        /* ===== ALERT ===== */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.9375rem;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-danger i {
            color: #ef4444;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        /* ===== BUTTON ===== */
        .btn-login {
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: var(--primary-blue);
            border: none;
            border-radius: var(--radius-md);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-login:hover {
            background: var(--primary-blue-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(30, 58, 95, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.3);
        }

        .btn-login i {
            font-size: 1.125rem;
        }

        /* ===== FOOTER ===== */
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }

        .login-footer p {
            font-size: 0.8125rem;
            color: var(--gray-500);
        }

        .login-footer .version {
            color: var(--gray-400);
            margin-top: 0.5rem;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991.98px) {
            body {
                flex-direction: column;
            }

            .brand-panel {
                padding: 2rem;
            }

            .brand-features {
                display: none;
            }

            .login-panel {
                max-width: 100%;
                padding: 2rem;
            }
        }

        @media (max-width: 575.98px) {
            .brand-panel {
                padding: 1.5rem;
            }

            .brand-logo {
                width: 100px;
                height: 100px;
            }

            .brand-name {
                font-size: 1.5rem;
            }

            .login-panel {
                padding: 1.5rem;
            }

            .login-header h1 {
                font-size: 1.5rem;
            }
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-form {
            animation: fadeIn 0.4s ease-out;
        }
    </style>
</head>
<body>
    <!-- Left Branding Panel -->
    <div class="brand-panel">
        <div class="brand-content">
            <div class="brand-logo">
                <img src="/storage/logos/aclc-logo.png" alt="ACLC College" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 200 200%27%3E%3Ccircle cx=%27100%27 cy=%27100%27 r=%2780%27 fill=%27%231e3a5f%27/%3E%3Ctext x=%27100%27 y=%27115%27 text-anchor=%27middle%27 fill=%27white%27 font-size=%2736%27 font-weight=%27bold%27 font-family=%27Inter, sans-serif%27%3EACLC%3C/text%3E%3C/svg%3E'">
            </div>
            
            <h1 class="brand-name">ACLC Voting System</h1>
            <p class="brand-tagline">Secure • Simple • Transparent</p>
            
            <div class="brand-features">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="feature-text">
                        <h4>Secure Voting</h4>
                        <p>Your vote is encrypted and anonymous</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="bi bi-lightning"></i>
                    </div>
                    <div class="feature-text">
                        <h4>Easy to Use</h4>
                        <p>Simple interface, quick voting process</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="feature-text">
                        <h4>Real-time Results</h4>
                        <p>View live election results instantly</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Login Panel -->
    <div class="login-panel">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Sign in to access the voting system</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-circle"></i>
                <div>
                    <strong>Login Failed</strong><br>
                    {{ $errors->first() }}
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="login-form" autocomplete="on">
            @csrf

            <div class="form-group">
                <label class="form-label" for="usn">
                    Student ID / Username <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                    <i class="bi bi-person"></i>
                    <input 
                        type="text" 
                        id="usn" 
                        name="usn" 
                        class="form-control @error('usn') is-invalid @enderror" 
                        value="{{ old('usn') }}" 
                        placeholder="Enter your Student ID"
                        autocomplete="username"
                        required 
                        autofocus
                        aria-describedby="usnHelp"
                    >
                </div>
                @error('usn')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password">
                    Password <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                    <i class="bi bi-lock"></i>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                        aria-describedby="passwordHelp"
                    >
                </div>
                @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right"></i>
                Sign In
            </button>
        </form>

        <div class="login-footer">
            <p>Having trouble signing in? Contact your administrator.</p>
            <p class="version">Version 2.0</p>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
