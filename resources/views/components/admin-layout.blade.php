<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Panel' }} - ACLC Voting System</title>
    
    <!-- Google Fonts - Inter for better readability -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        /* ===== CSS VARIABLES - Consistent theming ===== */
        :root {
            /* Primary Colors */
            --aclc-blue: #1e3a5f;
            --aclc-light-blue: #2d5a87;
            --aclc-dark-blue: #0f2744;
            --aclc-red: #dc3545;
            --aclc-dark-red: #b02a37;
            
            /* Neutral Colors */
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            
            /* Status Colors */
            --success: #10b981;
            --success-light: #d1fae5;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --info: #3b82f6;
            --info-light: #dbeafe;
            
            /* Layout */
            --sidebar-width: 260px;
            --sidebar-collapsed: 80px;
            --header-height: 70px;
            
            /* Spacing */
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            
            /* Border Radius */
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            
            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            
            /* Transitions */
            --transition-fast: 150ms ease;
            --transition-normal: 250ms ease;
            --transition-slow: 350ms ease;
        }

        /* ===== BASE STYLES ===== */
        * {
            box-sizing: border-box;
        }

        body {
            background: var(--gray-100);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 0.9375rem;
            line-height: 1.6;
            color: var(--gray-700);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding: var(--spacing-xl);
            transition: margin-left var(--transition-normal);
        }

        /* ===== PAGE HEADER ===== */
        .page-header {
            background: white;
            padding: var(--spacing-lg) var(--spacing-xl);
            border-radius: var(--radius-lg);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .page-title {
            color: var(--aclc-blue);
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .page-title i {
            font-size: 1.25rem;
            opacity: 0.8;
        }

        .page-subtitle {
            color: var(--gray-500);
            font-size: 0.875rem;
            margin-top: var(--spacing-xs);
        }

        /* ===== BUTTONS - Clear affordance ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
            font-weight: 600;
            border-radius: var(--radius-sm);
            transition: all var(--transition-fast);
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.2);
        }

        .btn-primary {
            background: var(--aclc-blue);
            color: white;
            padding: 0.625rem 1.25rem;
        }

        .btn-primary:hover {
            background: var(--aclc-dark-blue);
            color: white;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
            padding: 0.625rem 1.25rem;
        }

        .btn-secondary:hover {
            background: var(--gray-300);
            color: var(--gray-800);
        }

        .btn-success {
            background: var(--success);
            color: white;
            padding: 0.625rem 1.25rem;
        }

        .btn-success:hover {
            background: #059669;
            color: white;
            transform: translateY(-1px);
        }

        .btn-warning {
            background: var(--warning);
            color: var(--gray-900);
            padding: 0.625rem 1.25rem;
        }

        .btn-warning:hover {
            background: #d97706;
            color: var(--gray-900);
            transform: translateY(-1px);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
            padding: 0.625rem 1.25rem;
        }

        .btn-danger:hover {
            background: #dc2626;
            color: white;
            transform: translateY(-1px);
        }

        .btn-info {
            background: var(--info);
            color: white;
            padding: 0.625rem 1.25rem;
        }

        .btn-info:hover {
            background: #2563eb;
            color: white;
            transform: translateY(-1px);
        }

        .btn-outline-primary {
            background: transparent;
            border: 2px solid var(--aclc-blue);
            color: var(--aclc-blue);
            padding: 0.5rem 1.125rem;
        }

        .btn-outline-primary:hover {
            background: var(--aclc-blue);
            color: white;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
        }

        .btn-lg {
            padding: 0.875rem 1.75rem;
            font-size: 1rem;
        }

        /* Icon-only buttons */
        .btn-icon {
            width: 36px;
            height: 36px;
            padding: 0;
            border-radius: var(--radius-sm);
        }

        .btn-icon.btn-sm {
            width: 32px;
            height: 32px;
        }

        /* ===== CARDS ===== */
        .card {
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--spacing-lg);
            overflow: hidden;
        }

        .card-header {
            padding: var(--spacing-lg);
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-50);
            font-weight: 600;
            color: var(--gray-800);
        }

        .card-body {
            padding: var(--spacing-lg);
        }

        .card-footer {
            padding: var(--spacing-md) var(--spacing-lg);
            border-top: 1px solid var(--gray-200);
            background: var(--gray-50);
        }

        /* ===== FORMS - Clear labels and inputs ===== */
        .form-label {
            color: var(--gray-700);
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: var(--spacing-sm);
            display: block;
        }

        .form-label .required {
            color: var(--danger);
            margin-left: 2px;
        }

        .form-control, .form-select {
            border: 1.5px solid var(--gray-300);
            border-radius: var(--radius-sm);
            padding: 0.625rem 0.875rem;
            font-size: 0.9375rem;
            transition: all var(--transition-fast);
            background-color: white;
            color: var(--gray-700);
        }

        .form-control:hover, .form-select:hover {
            border-color: var(--gray-400);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--aclc-light-blue);
            box-shadow: 0 0 0 3px rgba(45, 90, 135, 0.15);
            outline: none;
        }

        .form-control::placeholder {
            color: var(--gray-400);
        }

        .form-control.is-invalid {
            border-color: var(--danger);
        }

        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
        }

        .form-text {
            font-size: 0.8125rem;
            color: var(--gray-500);
            margin-top: var(--spacing-xs);
        }

        .form-check-input {
            width: 1.125rem;
            height: 1.125rem;
            border: 1.5px solid var(--gray-400);
            transition: all var(--transition-fast);
        }

        .form-check-input:checked {
            background-color: var(--aclc-blue);
            border-color: var(--aclc-blue);
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.15);
        }

        /* ===== ALERTS - Clear feedback ===== */
        .alert {
            border-radius: var(--radius-md);
            border: none;
            padding: var(--spacing-md) var(--spacing-lg);
            display: flex;
            align-items: flex-start;
            gap: var(--spacing-md);
            font-size: 0.9375rem;
        }

        .alert i {
            font-size: 1.125rem;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .alert-success {
            background: var(--success-light);
            color: #065f46;
        }

        .alert-success i { color: var(--success); }

        .alert-danger {
            background: var(--danger-light);
            color: #991b1b;
        }

        .alert-danger i { color: var(--danger); }

        .alert-warning {
            background: var(--warning-light);
            color: #92400e;
        }

        .alert-warning i { color: var(--warning); }

        .alert-info {
            background: var(--info-light);
            color: #1e40af;
        }

        .alert-info i { color: var(--info); }

        .alert-dismissible .btn-close {
            padding: var(--spacing-md);
        }

        /* ===== BADGES ===== */
        .badge {
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.25rem 0.625rem;
            border-radius: var(--radius-sm);
        }

        /* ===== TABLES ===== */
        .table {
            margin-bottom: 0;
        }

        .table > thead {
            background: var(--gray-50);
        }

        .table > thead > tr > th {
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            color: var(--gray-600);
            padding: var(--spacing-md) var(--spacing-lg);
            border-bottom: 2px solid var(--gray-200);
        }

        .table > tbody > tr > td {
            padding: var(--spacing-md) var(--spacing-lg);
            vertical-align: middle;
            border-bottom: 1px solid var(--gray-100);
            color: var(--gray-700);
        }

        .table > tbody > tr:hover {
            background: var(--gray-50);
        }

        .table > tbody > tr:last-child > td {
            border-bottom: none;
        }

        /* ===== SECTION TITLE ===== */
        .section-title {
            color: var(--gray-800);
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-md);
            border-bottom: 2px solid var(--gray-200);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        /* ===== HELP TEXT ===== */
        .help-text {
            font-size: 0.8125rem;
            color: var(--gray-500);
            margin-top: var(--spacing-xs);
        }

        /* ===== EMPTY STATES ===== */
        .empty-state {
            text-align: center;
            padding: var(--spacing-xl) * 2;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--gray-300);
            margin-bottom: var(--spacing-lg);
        }

        .empty-state h3 {
            color: var(--gray-600);
            font-weight: 600;
            margin-bottom: var(--spacing-sm);
        }

        .empty-state p {
            color: var(--gray-500);
            margin-bottom: var(--spacing-lg);
        }

        /* ===== MODALS ===== */
        .modal-content {
            border: none;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
        }

        .modal-header {
            border-bottom: 1px solid var(--gray-200);
            padding: var(--spacing-lg);
        }

        .modal-body {
            padding: var(--spacing-lg);
        }

        .modal-footer {
            border-top: 1px solid var(--gray-200);
            padding: var(--spacing-lg);
        }

        /* ===== PROGRESS BAR ===== */
        .progress {
            height: 8px;
            border-radius: 4px;
            background: var(--gray-200);
            overflow: hidden;
        }

        .progress-bar {
            border-radius: 4px;
            transition: width var(--transition-slow);
        }

        /* ===== TOOLTIPS ===== */
        [data-bs-toggle="tooltip"] {
            cursor: help;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0;
                padding: var(--spacing-lg);
            }
        }

        @media (max-width: 575.98px) {
            .main-content {
                padding: var(--spacing-md);
            }

            .page-header {
                padding: var(--spacing-md);
            }

            .page-title {
                font-size: 1.25rem;
            }
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn var(--transition-normal) ease-out;
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--gray-400);
        }

        {{ $styles ?? '' }}
    </style>

    {{ $headScripts ?? '' }}
</head>
<body>
    <!-- Sidebar Component -->
    <x-admin-sidebar />

    <!-- Main Content -->
    <div class="main-content fade-in">
        {{ $slot }}
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Initialize tooltips -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Auto-hide alerts after 5 seconds
            document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
                setTimeout(function() {
                    var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
    
    {{ $scripts ?? '' }}
    @stack('scripts')
</body>
</html>