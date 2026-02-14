<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Panel' }} - ACLC Voting System</title>
    
    {{-- <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/admin_layout..css'])
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <style>
        :root {
            --aclc-blue: #003366;
            --aclc-light-blue: #00509E;
            --aclc-red: #CC0000;
            --sidebar-width: 250px;
        }

        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
        }

        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .page-title {
            color: var(--aclc-blue);
            font-weight: 700;
            font-size: 1.8rem;
            margin: 0;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--aclc-red) 0%, #990000 100%);
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(204, 0, 0, 0.3);
            background: linear-gradient(135deg, var(--aclc-blue) 0%, var(--aclc-light-blue) 100%);
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .btn-info {
            background: var(--aclc-light-blue);
            border: none;
            color: white;
        }

        .btn-info:hover {
            background: var(--aclc-blue);
            transform: translateY(-1px);
        }

        .btn-warning {
            background: #ffc107;
            border: none;
            color: #000;
        }

        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-1px);
        }

        .btn-success {
            background: #28a745;
            border: none;
        }

        .btn-success:hover {
            background: #218838;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: var(--aclc-red);
            border: none;
        }

        .btn-danger:hover {
            background: #990000;
            transform: translateY(-1px);
        }

        .btn-sm {
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .form-label {
            color: var(--aclc-blue);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--aclc-light-blue);
            box-shadow: 0 0 0 3px rgba(0, 80, 158, 0.1);
        }

        .form-check-input:checked {
            background-color: var(--aclc-red);
            border-color: var(--aclc-red);
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            color: var(--aclc-blue);
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }

        .help-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
            z-index: 10;
        }

        .password-toggle:hover {
            color: var(--aclc-blue);
        }

        {{ $styles ?? '' }}
    </style>

    {{ $headScripts ?? '' }}
</head>
<body>
    <!-- Sidebar Component -->
    <x-admin-sidebar />

    <!-- Main Content -->
    <div class="main-content">
        {{ $slot }}
    </div>

    {{-- <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> --}}
    
    <!-- Password Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Find all password input fields
            const passwordFields = document.querySelectorAll('input[type="password"]');
            
            passwordFields.forEach(function(passwordInput) {
                // Only add toggle if not already added
                if (passwordInput.parentElement.querySelector('.password-toggle')) {
                    return;
                }
                
                // Only add toggle to fields that are in a form (avoid interfering with other structures)
                if (!passwordInput.closest('form')) {
                    return;
                }
                
                // Wrap the input in a position relative div if not already wrapped
                if (!passwordInput.parentElement.classList.contains('position-relative')) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'position-relative';
                    passwordInput.parentNode.insertBefore(wrapper, passwordInput);
                    wrapper.appendChild(passwordInput);
                }
                
                // Create toggle icon
                const toggleIcon = document.createElement('i');
                toggleIcon.className = 'bi bi-eye-slash password-toggle';
                
                // Insert after the input
                passwordInput.parentElement.appendChild(toggleIcon);
                
                // Add click handler
                toggleIcon.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    if (type === 'password') {
                        this.classList.remove('bi-eye');
                        this.classList.add('bi-eye-slash');
                    } else {
                        this.classList.remove('bi-eye-slash');
                        this.classList.add('bi-eye');
                    }
                });
            });
        });
    </script>
    
    {{ $scripts ?? '' }}
</body>
</html>