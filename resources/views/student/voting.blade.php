<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vote - {{ $election->title }}</title>
    
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
            --primary-blue-dark: #0f2744;
            --accent-green: #10b981;
            --accent-red: #ef4444;
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
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--gray-100);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            color: var(--gray-700);
        }

        /* ===== HEADER ===== */
        .app-header {
            background: white;
            border-bottom: 1px solid var(--gray-200);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-sm);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-light) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }

        .brand-text h1 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-800);
            margin: 0;
        }

        .brand-text span {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--gray-100);
            border-radius: 9999px;
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .user-info i {
            color: var(--primary-blue);
        }

        .btn-logout {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: transparent;
            border: 1.5px solid var(--gray-300);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-600);
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background: var(--gray-100);
            border-color: var(--gray-400);
            color: var(--gray-700);
        }

        /* ===== MAIN CONTAINER ===== */
        .main-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* ===== ELECTION HEADER ===== */
        .election-header {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
        }

        .election-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-800);
            margin: 0 0 0.5rem 0;
        }

        .election-description {
            color: var(--gray-500);
            font-size: 0.9375rem;
            margin: 0;
        }

        .election-notice {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-top: 1rem;
            padding: 1rem;
            background: #dbeafe;
            border-radius: var(--radius-md);
            color: #1e40af;
            font-size: 0.875rem;
        }

        .election-notice i {
            color: #3b82f6;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        /* ===== ALERT ===== */
        .alert-error {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            color: #991b1b;
            font-size: 0.9375rem;
        }

        .alert-error i {
            color: var(--accent-red);
            font-size: 1.25rem;
        }

        /* ===== POSITION CARD ===== */
        .position-card {
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-200);
            margin-bottom: 1.5rem;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .position-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-50);
        }

        .position-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--gray-800);
            margin: 0 0 0.25rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .position-title i {
            color: var(--primary-blue);
        }

        .position-info {
            font-size: 0.8125rem;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .position-info .required-mark {
            color: var(--accent-red);
            font-weight: 600;
        }

        .position-body {
            padding: 1.5rem;
        }

        .position-error {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: #fee2e2;
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
            color: #991b1b;
            font-size: 0.875rem;
        }

        /* ===== CANDIDATES GRID ===== */
        .candidates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
        }

        /* ===== CANDIDATE CARD ===== */
        .candidate-card {
            position: relative;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            padding: 1.25rem;
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
        }

        .candidate-card:hover {
            border-color: var(--primary-blue-light);
            box-shadow: var(--shadow-md);
        }

        .candidate-card.selected {
            border-color: var(--accent-green);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.03) 0%, rgba(16, 185, 129, 0.08) 100%);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }

        .candidate-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .check-indicator {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--accent-green);
            color: white;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            animation: scaleIn 0.2s ease;
        }

        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        .candidate-card.selected .check-indicator {
            display: flex;
        }

        .candidate-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.75rem;
            color: white;
            font-weight: 700;
            overflow: hidden;
        }

        .candidate-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .candidate-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-800);
            text-align: center;
            margin-bottom: 0.25rem;
        }

        .candidate-details {
            text-align: center;
            font-size: 0.8125rem;
            color: var(--gray-500);
            margin-bottom: 0.5rem;
        }

        .candidate-party {
            text-align: center;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            margin: 0 auto;
            width: auto;
        }

        .candidate-bio {
            margin-top: 0.75rem;
            font-size: 0.8125rem;
            color: var(--gray-600);
            text-align: center;
            font-style: italic;
            line-height: 1.5;
        }

        /* ===== ABSTAIN OPTION ===== */
        .abstain-card {
            border-style: dashed;
            border-color: var(--gray-300);
            background: var(--gray-50);
        }

        .abstain-card:hover {
            border-color: var(--gray-400);
            background: var(--gray-100);
        }

        .abstain-card.selected {
            border-color: var(--gray-500);
            background: var(--gray-200);
            box-shadow: none;
        }

        .abstain-card .candidate-photo {
            background: var(--gray-400);
        }

        .abstain-card .candidate-name {
            color: var(--gray-600);
        }

        /* ===== SUBMIT SECTION ===== */
        .submit-section {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-top: 2rem;
            border: 1px solid var(--gray-200);
            text-align: center;
            box-shadow: var(--shadow-sm);
        }

        .submit-section h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .submit-section p {
            color: var(--gray-500);
            font-size: 0.9375rem;
            margin-bottom: 1.5rem;
        }

        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem 2.5rem;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
            border: none;
            border-radius: var(--radius-md);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(30, 58, 95, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            background: var(--gray-300);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-submit i {
            font-size: 1.125rem;
        }

        /* ===== CONFIRMATION MODAL ===== */
        .modal-content {
            border: none;
            border-radius: var(--radius-lg);
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
        }

        .modal-header {
            border-bottom: 1px solid var(--gray-200);
            padding: 1.25rem 1.5rem;
        }

        .modal-title {
            font-weight: 700;
            color: var(--gray-800);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid var(--gray-200);
            padding: 1rem 1.5rem;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 767.98px) {
            .main-container {
                padding: 1rem;
            }

            .header-content {
                padding: 0.875rem 1rem;
            }

            .brand-text h1 {
                font-size: 0.9375rem;
            }

            .brand-text span {
                display: none;
            }

            .user-info span {
                display: none;
            }

            .election-header {
                padding: 1.25rem;
            }

            .election-title {
                font-size: 1.25rem;
            }

            .candidates-grid {
                grid-template-columns: 1fr;
            }

            .submit-section {
                padding: 1.5rem;
            }
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .position-card {
            animation: fadeIn 0.3s ease-out;
        }

        .position-card:nth-child(2) { animation-delay: 0.1s; }
        .position-card:nth-child(3) { animation-delay: 0.2s; }
        .position-card:nth-child(4) { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="app-header">
        <div class="header-content">
            <div class="header-brand">
                <div class="brand-icon">
                    <i class="bi bi-check2-square"></i>
                </div>
                <div class="brand-text">
                    <h1>ACLC Voting System</h1>
                    <span>Cast your vote securely</span>
                </div>
            </div>
            <div class="header-user">
                <div class="user-info">
                    <i class="bi bi-person-circle"></i>
                    <span>{{ Auth::user()->usn }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="main-container">
        <!-- Election Header -->
        <div class="election-header">
            <h2 class="election-title">{{ $election->title }}</h2>
            <p class="election-description">{{ $election->description }}</p>
            @if($election->allow_abstain)
                <div class="election-notice">
                    <i class="bi bi-info-circle-fill"></i>
                    <div>
                        <strong>Note:</strong> You may choose to abstain from voting for any position. 
                        This allows you to skip positions where you have no preference.
                    </div>
                </div>
            @endif
        </div>

        @if(session('error'))
            <div class="alert-error">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Voting Form -->
        <form method="POST" action="{{ route('voting.submit') }}" id="votingForm" autocomplete="off">
            @csrf

            @foreach($election->positions as $index => $position)
                <div class="position-card" style="animation-delay: {{ $index * 0.1 }}s;">
                    <div class="position-header">
                        <h3 class="position-title">
                            <i class="bi bi-award-fill"></i>
                            {{ $position->name }}
                        </h3>
                        <div class="position-info">
                            @if($position->max_winners > 1)
                                <span>Select up to {{ $position->max_winners }} candidates</span>
                            @else
                                <span>Select one candidate</span>
                            @endif
                            @if(!$election->allow_abstain)
                                <span class="required-mark">• Required</span>
                            @endif
                        </div>
                    </div>

                    <div class="position-body">
                        @error("position_{$position->id}")
                            <div class="position-error">
                                <i class="bi bi-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="candidates-grid">
                            @foreach($position->candidates as $candidate)
                                <label class="candidate-card" data-position="{{ $position->id }}">
                                    <input 
                                        type="radio" 
                                        name="position_{{ $position->id }}" 
                                        value="{{ $candidate->id }}"
                                        {{ old("position_{$position->id}") == $candidate->id ? 'checked' : '' }}
                                    >
                                    <div class="check-indicator">
                                        <i class="bi bi-check-lg"></i>
                                    </div>
                                    
                                    <div class="candidate-photo">
                                        @if($candidate->photo_path)
                                            <img src="{{ asset('storage/' . $candidate->photo_path) }}" 
                                                 alt="{{ $candidate->full_name }}">
                                        @else
                                            {{ strtoupper(substr($candidate->first_name, 0, 1) . substr($candidate->last_name, 0, 1)) }}
                                        @endif
                                    </div>
                                    
                                    <div class="candidate-name">
                                        {{ $candidate->full_name }}
                                    </div>
                                    
                                    <div class="candidate-details">
                                        @if($candidate->course && $candidate->year_level)
                                            {{ $candidate->course }} - {{ $candidate->year_level }}
                                        @else
                                            &nbsp;
                                        @endif
                                    </div>
                                    
                                    @if($candidate->party)
                                        <div style="text-align: center;">
                                            <span class="candidate-party" style="background-color: {{ $candidate->party->color }}20; color: {{ $candidate->party->color }}; border: 1px solid {{ $candidate->party->color }}40;">
                                                {{ $candidate->party->name }}
                                            </span>
                                        </div>
                                    @endif

                                    @if($candidate->bio)
                                        <div class="candidate-bio">
                                            "{{ Str::limit($candidate->bio, 80) }}"
                                        </div>
                                    @endif
                                </label>
                            @endforeach

                            @if($election->allow_abstain)
                                <label class="candidate-card abstain-card" data-position="{{ $position->id }}">
                                    <input 
                                        type="radio" 
                                        name="position_{{ $position->id }}" 
                                        value=""
                                        {{ old("position_{$position->id}") === '' ? 'checked' : '' }}
                                    >
                                    <div class="check-indicator" style="background: var(--gray-500);">
                                        <i class="bi bi-check-lg"></i>
                                    </div>
                                    
                                    <div class="candidate-photo">
                                        <i class="bi bi-dash-lg"></i>
                                    </div>
                                    
                                    <div class="candidate-name">
                                        Abstain
                                    </div>
                                    
                                    <div class="candidate-details">
                                        Skip this position
                                    </div>
                                </label>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Submit Section -->
            <div class="submit-section">
                <h3>Ready to Submit?</h3>
                <p>
                    Please review your selections carefully. Once submitted, <strong>you cannot change your vote.</strong>
                </p>
                <button type="button" class="btn-submit" data-bs-toggle="modal" data-bs-target="#confirmModal">
                    <i class="bi bi-check-circle"></i>
                    Submit My Vote
                </button>
            </div>

            <!-- Confirmation Modal -->
            <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmModalLabel">
                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                Confirm Your Vote
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">Are you sure you want to submit your vote?</p>
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>This action cannot be undone.</strong> Your vote will be final.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x me-1"></i>
                                Review Again
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check me-1"></i>
                                Yes, Submit Vote
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Handle candidate card selection
        document.querySelectorAll('.candidate-card').forEach(card => {
            card.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                const position = this.dataset.position;
                
                // Unselect all cards in this position
                document.querySelectorAll(`.candidate-card[data-position="${position}"]`).forEach(c => {
                    c.classList.remove('selected');
                });
                
                // Select this card
                if (radio) {
                    radio.checked = true;
                    this.classList.add('selected');
                }
            });
        });

        // Initialize selected cards on page load
        document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
            const card = radio.closest('.candidate-card');
            if (card) {
                card.classList.add('selected');
            }
        });
    </script>
</body>
</html>
