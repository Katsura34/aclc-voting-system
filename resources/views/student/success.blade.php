<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote Submitted - ACLC Voting System</title>
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-blue: #1e3a5f;
            --primary-blue-light: #2d5a87;
            --accent-green: #10b981;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --radius-md: 10px;
            --radius-lg: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, var(--gray-100) 0%, #e2e8f0 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            -webkit-font-smoothing: antialiased;
        }

        .success-container {
            max-width: 520px;
            width: 100%;
            background: white;
            border-radius: var(--radius-lg);
            padding: 3rem 2.5rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            text-align: center;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--accent-green) 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: scaleIn 0.5s ease-out 0.2s both;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            50% {
                transform: scale(1.1);
            }
            to {
                transform: scale(1);
            }
        }

        .success-icon i {
            font-size: 3rem;
            color: white;
        }

        .success-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 0.75rem;
        }

        .success-message {
            font-size: 1rem;
            color: var(--gray-500);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        @if(session('success'))
            .success-alert {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 1rem 1.25rem;
                background: #d1fae5;
                border-radius: var(--radius-md);
                margin-bottom: 1.5rem;
                color: #065f46;
                font-size: 0.9375rem;
                text-align: left;
            }

            .success-alert i {
                color: var(--accent-green);
                font-size: 1.25rem;
                flex-shrink: 0;
            }
        @endif

        .info-card {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .info-card-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 1rem;
        }

        .info-card-title i {
            color: var(--primary-blue);
        }

        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-list li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.875rem;
            color: var(--gray-600);
            padding: 0.5rem 0;
            line-height: 1.5;
        }

        .info-list li:not(:last-child) {
            border-bottom: 1px solid var(--gray-200);
        }

        .info-list li i {
            color: var(--accent-green);
            font-size: 1rem;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .btn-logout {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 2rem;
            background: var(--primary-blue);
            border: none;
            border-radius: var(--radius-md);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-logout:hover {
            background: var(--primary-blue-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(30, 58, 95, 0.3);
            color: white;
        }

        .btn-logout:active {
            transform: translateY(0);
        }

        .timestamp {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.8125rem;
            color: var(--gray-400);
        }

        .timestamp i {
            font-size: 0.875rem;
        }

        @media (max-width: 575.98px) {
            .success-container {
                padding: 2rem 1.5rem;
            }

            .success-title {
                font-size: 1.5rem;
            }

            .success-icon {
                width: 80px;
                height: 80px;
            }

            .success-icon i {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="bi bi-check-lg"></i>
        </div>

        <h1 class="success-title">Vote Submitted!</h1>
        
        <p class="success-message">
            Thank you for participating in the election. Your vote has been recorded successfully and securely.
        </p>

        @if(session('success'))
            <div class="success-alert">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="info-card">
            <div class="info-card-title">
                <i class="bi bi-shield-check"></i>
                Important Information
            </div>
            <ul class="info-list">
                <li>
                    <i class="bi bi-check2"></i>
                    <span><strong>Your vote is confidential</strong> – No one can see who you voted for</span>
                </li>
                <li>
                    <i class="bi bi-check2"></i>
                    <span><strong>One-time voting</strong> – You cannot vote again or change your choices</span>
                </li>
                <li>
                    <i class="bi bi-check2"></i>
                    <span><strong>Secure system</strong> – Your vote is encrypted and stored safely</span>
                </li>
                @if($election = \App\Models\Election::where('is_active', true)->first())
                    <li>
                        <i class="bi bi-check2"></i>
                        @if($election->show_live_results)
                            <span><strong>Results</strong> – Live results are available after voting</span>
                        @else
                            <span><strong>Results</strong> – Will be announced after the election ends</span>
                        @endif
                    </li>
                @endif
            </ul>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i>
                Sign Out
            </button>
        </form>

        <div class="timestamp">
            <i class="bi bi-clock"></i>
            <span>Voted on {{ now()->format('F d, Y \a\t h:i A') }}</span>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
