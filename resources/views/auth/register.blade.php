<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body.dark-mode {
            background-color: #181a1b !important;
            color: #e0e0e0;
        }
        .card.dark-mode {
            background-color: #23272b;
            color: #e0e0e0;
        }
        .form-control.dark-mode {
            background-color: #2c2f33;
            color: #e0e0e0;
            border-color: #444;
        }
        .btn-dark-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body class="bg-light" id="mainBody">
 <dev class="scroller">
     <dev class="scroller-inner">
         <dev class="scroller-inner">    name    </dev>  
         <dev class="scroller-inner">    name    </dev>  
         <dev class="scroller-inner">    name    </dev>  
    </dev>
 </dev>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mt-5 shadow" id="cardBox">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">Voters Registration</h2>
                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="school_name" class="form-label">School Name</label>
                                <input type="text" id="school_name" name="school_name" class="form-control" required autofocus placeholder="Enter your school name">
                            </div>

                            <div class="row mb-3">
                                <div class="col">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" id="first_name" name="first_name" class="form-control" required placeholder="Enter your first name">
                                </div>
                                <div class="col">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" class="form-control" required placeholder="Enter your last name">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" required placeholder="Enter your email address">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required placeholder="Create a password">
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required placeholder="Re-enter your password">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        const toggleBtn = document.getElementById('themeToggleBtn');
        const mainBody = document.getElementById('mainBody');
        const cardBox = document.getElementById('cardBox');
        const inputs = document.querySelectorAll('.form-control');

        function setDarkMode(isDark) {
            if (isDark) {
                mainBody.classList.add('dark-mode');
                cardBox.classList.add('dark-mode');
                inputs.forEach(input => input.classList.add('dark-mode'));
                toggleBtn.textContent = 'Toggle Light Mode';
            } else {
                mainBody.classList.remove('dark-mode');
                cardBox.classList.remove('dark-mode');
                inputs.forEach(input => input.classList.remove('dark-mode'));
                toggleBtn.textContent = 'Toggle Dark Mode';
            }
        }

        // Load theme from localStorage
        let darkMode = localStorage.getItem('darkMode') === 'true';
        setDarkMode(darkMode);

        toggleBtn.addEventListener('click', () => {
            darkMode = !darkMode;
            localStorage.setItem('darkMode', darkMode);
            setDarkMode(darkMode);
        });
    </script>
</body>
</html>
