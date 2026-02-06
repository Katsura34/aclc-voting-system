<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vote - {{ $election->title }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --aclc-blue: #003366;
            --aclc-light-blue: #00509E;
            --aclc-red: #CC0000;
            --aclc-dark-red: #990000;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--aclc-blue) 0%, var(--aclc-light-blue) 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }

        .main-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 15px;
        }

        .election-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-left: 5px solid var(--aclc-red);
        }

        .election-title {
            color: var(--aclc-blue);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .election-description {
            color: #666;
            margin-bottom: 0;
        }

        .position-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .position-title {
            color: var(--aclc-blue);
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .position-info {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .candidates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .candidate-card {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .candidate-card:hover {
            border-color: var(--aclc-light-blue);
            box-shadow: 0 5px 15px rgba(0, 80, 158, 0.2);
            transform: translateY(-3px);
        }

        .candidate-card.selected {
            border-color: var(--aclc-red);
            background: linear-gradient(135deg, rgba(204, 0, 0, 0.05) 0%, rgba(204, 0, 0, 0.1) 100%);
            box-shadow: 0 5px 20px rgba(204, 0, 0, 0.3);
        }

        .candidate-card input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .candidate-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--aclc-blue) 0%, var(--aclc-light-blue) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2.5rem;
            color: white;
            font-weight: 700;
        }

        .candidate-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--aclc-blue);
            text-align: center;
            margin-bottom: 8px;
        }

        .candidate-details {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .candidate-party {
            text-align: center;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            width: 100%;
        }

        .candidate-bio {
            margin-top: 10px;
            font-size: 0.85rem;
            color: #555;
            text-align: center;
            font-style: italic;
        }

        .check-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--aclc-red);
            color: white;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .candidate-card.selected .check-indicator {
            display: flex;
        }

        .submit-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .btn-submit {
            padding: 15px 50px;
            background: linear-gradient(135deg, var(--aclc-red) 0%, var(--aclc-dark-red) 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(204, 0, 0, 0.4);
            background: linear-gradient(135deg, var(--aclc-blue) 0%, var(--aclc-light-blue) 100%);
        }

        .btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <span class="navbar-brand">
                <i class="bi bi-check2-square"></i>
                ACLC Voting System
            </span>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="bi bi-person-circle"></i>
                    {{ Auth::user()->usn }}
                </span>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <!-- Welcome Message -->
        <div class="alert alert-info border-0 shadow-sm mb-4">
            <h4 class="mb-0">
                <i class="bi bi-hand-wave"></i>
                Welcome, {{ Auth::user()->full_name }}!
            </h4>
            <p class="mb-0 mt-2">Please select your preferred candidates for each position below.</p>
        </div>

        <!-- Election Header -->
        <div class="election-header">
            <h1 class="election-title">{{ $election->title }}</h1>
            <p class="election-description">{{ $election->description }}</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                {{ session('error') }}
            </div>
        @endif

        <!-- Voting Form -->
        <form method="POST" action="{{ route('voting.submit') }}" id="votingForm" autocomplete="off">
            @csrf

            @foreach($election->positions as $position)
                <div class="position-card">
                    <div class="position-title">
                        <i class="bi bi-award"></i>
                        {{ $position->name }}
                    </div>
                    <div class="position-info">
                        @if($position->max_winners > 1)
                            Choose up to {{ $position->max_winners }} candidates
                        @else
                            Choose one candidate
                        @endif
                        <span class="text-danger">*</span>
                    </div>

                    @error("position_{$position->id}")
                        <div class="alert alert-danger">{{ $message }}</div>
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
                                             alt="{{ $candidate->full_name }}"
                                             style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
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
                                    @endif
                                </div>
                                
                                @if($candidate->party)
                                    <div class="candidate-party" style="background-color: {{ $candidate->party->color }}20; color: {{ $candidate->party->color }}; border: 1px solid {{ $candidate->party->color }};">
                                        {{ $candidate->party->name }}
                                    </div>
                                @endif

                                @if($candidate->bio)
                                    <div class="candidate-bio">
                                        "{{ Str::limit($candidate->bio, 100) }}"
                                    </div>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <!-- Submit Section -->
            <div class="submit-section">
                <h3 class="mb-3">Review Your Choices</h3>
                <p class="text-muted mb-4">
                    Please review your selections carefully. Once submitted, you cannot change your vote.
                </p>
                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="bi bi-check-circle"></i> Submit My Vote
                </button>
            </div>
        </form>
    </div>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--aclc-blue) 0%, var(--aclc-light-blue) 100%); color: white;">
                    <h5 class="modal-title" id="reviewModalLabel">
                        <i class="bi bi-clipboard-check"></i> Review Your Votes
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Please review your selections carefully. Once submitted, you cannot change your vote.
                    </div>
                    <div id="reviewContent">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-pencil"></i> Edit Selections
                    </button>
                    <button type="button" class="btn btn-submit" id="confirmSubmitBtn">
                        <i class="bi bi-check-circle"></i> Confirm & Submit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Constants
        const DEFAULT_PARTY_BG = 'rgba(108, 117, 125, 0.125)';
        const DEFAULT_PARTY_TEXT = '#6c757d';
        const ALERT_DISMISS_TIMEOUT = 5000;
        
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
            radio.closest('.candidate-card').classList.add('selected');
        });

        // Show review modal before submit
        const reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
        
        // Helper function to escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Helper function to validate and sanitize color values
        function sanitizeColor(color) {
            // Only allow valid hex colors and rgb/rgba colors
            if (!color) return DEFAULT_PARTY_TEXT;
            
            // Test for valid hex color (#xxx or #xxxxxx)
            if (/^#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/.test(color)) {
                return color;
            }
            
            // Test for valid rgb/rgba color
            if (/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(,\s*[\d.]+\s*)?\)$/.test(color)) {
                return color;
            }
            
            // Default fallback
            return DEFAULT_PARTY_TEXT;
        }
        
        document.getElementById('votingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Check if all positions have selections
            let allSelected = true;
            const positions = document.querySelectorAll('.position-card');
            const reviewContent = document.getElementById('reviewContent');
            let reviewHTML = '<div class="list-group">';
            
            positions.forEach(position => {
                const positionTitle = position.querySelector('.position-title').textContent.trim();
                const selectedRadio = position.querySelector('input[type="radio"]:checked');
                
                if (selectedRadio) {
                    const candidateCard = selectedRadio.closest('.candidate-card');
                    const candidateName = candidateCard.querySelector('.candidate-name').textContent.trim();
                    const candidateParty = candidateCard.querySelector('.candidate-party');
                    const partyName = candidateParty ? candidateParty.textContent.trim() : 'Independent';
                    
                    // Escape all user-controlled values to prevent XSS
                    const safePositionTitle = escapeHtml(positionTitle);
                    const safeCandidateName = escapeHtml(candidateName);
                    const safePartyName = escapeHtml(partyName);
                    
                    // Get and sanitize party colors
                    const rawBgColor = candidateParty ? candidateParty.style.backgroundColor : '';
                    const rawTextColor = candidateParty ? candidateParty.style.color : '';
                    const partyBgColor = sanitizeColor(rawBgColor) || DEFAULT_PARTY_BG;
                    const partyTextColor = sanitizeColor(rawTextColor) || DEFAULT_PARTY_TEXT;
                    
                    reviewHTML += `
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1" style="color: var(--aclc-blue);">
                                        <i class="bi bi-award"></i> ${safePositionTitle}
                                    </h6>
                                    <p class="mb-0">
                                        <strong>${safeCandidateName}</strong>
                                        <span class="badge" style="background-color: ${partyBgColor}; color: ${partyTextColor};">
                                            ${safePartyName}
                                        </span>
                                    </p>
                                </div>
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    `;
                } else {
                    allSelected = false;
                }
            });
            
            reviewHTML += '</div>';
            
            if (!allSelected) {
                // Show error in the page instead of using browser alert
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger alert-dismissible fade show';
                errorDiv.innerHTML = `
                    <i class="bi bi-exclamation-triangle"></i>
                    Please select a candidate for each position before submitting.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.querySelector('.submit-section').insertBefore(errorDiv, document.getElementById('submitBtn'));
                
                // Scroll to the error
                errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Auto-dismiss after timeout
                setTimeout(() => {
                    if (errorDiv && errorDiv.parentNode) {
                        errorDiv.remove();
                    }
                }, ALERT_DISMISS_TIMEOUT);
                
                return;
            }
            
            // Populate and show the review modal
            reviewContent.innerHTML = reviewHTML;
            reviewModal.show();
        });

        // Confirm submit button
        document.getElementById('confirmSubmitBtn').addEventListener('click', function() {
            reviewModal.hide();
            // Submit the form
            document.getElementById('votingForm').submit();
        });
    </script>
</body>
</html>
