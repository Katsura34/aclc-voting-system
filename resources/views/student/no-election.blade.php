<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No Active Election</title>
    
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

        .message-container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .message-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--aclc-blue) 0%, var(--aclc-light-blue) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }

        .message-icon i {
            font-size: 4rem;
            color: white;
        }

        .message-title {
            color: var(--aclc-blue);
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 15px;
        }

        .message-text {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
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
    </style>
</head>
<body>
    <div class="message-container">
        <div class="message-icon">
            <i class="bi bi-calendar-x"></i>
        </div>

        <h1 class="message-title">No Active Election</h1>
        
        <p class="message-text">
            There is currently no active election. Please check back later or contact your administrator for more information.
        </p>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
