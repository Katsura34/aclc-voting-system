<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote Submitted Successfully</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --aclc-blue: #003366;
            --aclc-light-blue: #00509E;
            --aclc-red: #CC0000;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease;
        }

        .success-icon i {
            font-size: 4rem;
            color: white;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-title {
            color: var(--aclc-blue);
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 15px;
        }

        .success-message {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .info-box {
            background: linear-gradient(135deg, rgba(0, 51, 102, 0.05) 0%, rgba(0, 80, 158, 0.1) 100%);
            border-left: 4px solid var(--aclc-red);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: left;
        }

        .info-box h5 {
            color: var(--aclc-blue);
            font-weight: 700;
            margin-bottom: 15px;
        }

        .info-box ul {
            margin: 0;
            padding-left: 20px;
        }

        .info-box li {
            color: #555;
            margin-bottom: 8px;
        }

        .btn-logout {
            padding: 12px 40px;
            background: linear-gradient(135deg, var(--aclc-red) 0%, #990000 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(204, 0, 0, 0.4);
            background: linear-gradient(135deg, var(--aclc-blue) 0%, var(--aclc-light-blue) 100%);
            color: white;
        }

        .timestamp {
            color: #999;
            font-size: 0.9rem;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>

        <h1 class="success-title">Vote Submitted!</h1>
        
        <p class="success-message">
            Thank you for participating in the election. Your vote has been recorded successfully and securely.
        </p>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="info-box">
            <h5><i class="bi bi-info-circle"></i> Important Information</h5>
            <ul>
                <li><strong>Your vote is confidential</strong> - No one can see who you voted for</li>
                <li><strong>One-time voting</strong> - You cannot vote again or change your choices</li>
                <li><strong>Secure system</strong> - Your vote is encrypted and stored safely</li>
                @if($election = \App\Models\Election::where('is_active', true)->first())
                    @if($election->show_live_results)
                        <li><strong>Results</strong> - Live results are available after voting</li>
                    @else
                        <li><strong>Results</strong> - Will be announced after the election period ends</li>
                    @endif
                @endif
            </ul>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>

        <div class="timestamp">
            <i class="bi bi-clock"></i>
            Voted on {{ now()->format('F d, Y \a\t h:i A') }}
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
