<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vote - {{ $election->title }}</title>
    
    {{-- <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

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
                <div class="position-card" data-max-winners="{{ $position->max_winners }}">
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
                        @php
                            $user = Auth::user();
                            $isRepresentative = strtolower(trim($position->name)) === 'senators';
                            $posName = strtolower(trim($position->name));
                            $isHousePosition = in_array($posName, ['house lord', 'house lady']);

                            // Normalize for case-insensitive comparisons
                            $studentHouse = strtolower(trim($user->house ?? ''));
                            $candidateHouse = null; // will be computed per candidate
                            $studentStrand = strtolower(trim($user->strand ?? ''));
                        @endphp
                        @foreach($position->candidates as $candidate)
                            @php $candidateHouse = strtolower(trim($candidate->house ?? '')); $candidateCourse = strtolower(trim($candidate->course ?? '')); @endphp
                            @if(
                                // Senators: filter by student's strand/course (case-insensitive)
                                ($isRepresentative && ($candidateCourse === $studentStrand))
                                // House Lord/Lady: filter by student's house (case-insensitive, require non-empty)
                                || ($isHousePosition && $candidateHouse !== '' && $candidateHouse === $studentHouse)
                                // Other positions: show all candidates
                                || (!$isRepresentative && !$isHousePosition)
                            )
                                @php $isMultiple = $position->max_winners > 1; @endphp
                                <label class="candidate-card" data-position="{{ $position->id }}" data-course="{{ $candidate->course }}" data-is-stem="{{ strtolower($candidate->course) === 'stem' ? 1 : 0 }}">
                                    <input 
                                        type="{{ $isMultiple ? 'checkbox' : 'radio' }}" 
                                        name="position_{{ $position->id }}{{ $isMultiple ? '[]' : '' }}" 
                                        value="{{ $candidate->id }}"
                                        {{ $isMultiple ? (is_array(old("position_{$position->id}")) && in_array($candidate->id, old("position_{$position->id}")) ? 'checked' : '') : (old("position_{$position->id}") == $candidate->id ? 'checked' : '') }}
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
                            @endif
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
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> --}}
    
    <script>
        // Constants
        const DEFAULT_PARTY_BG = 'rgba(108, 117, 125, 0.125)';
        const DEFAULT_PARTY_TEXT = '#6c757d';
        const ALERT_DISMISS_TIMEOUT = 5000;
        
        // Student strand for STEM-specific rules
        const STUDENT_STRAND = {!! json_encode(strtolower(trim(Auth::user()->strand ?? ''))) !!};

        // Handle candidate card selection (supports radio and checkbox)
        document.querySelectorAll('.candidate-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Ignore clicks directly on inputs (they'll be handled naturally)
                if (e.target && e.target.tagName === 'INPUT') return;

                const input = this.querySelector('input[type="radio"], input[type="checkbox"]');
                const position = this.dataset.position;

                if (!input) return;

                // If checkbox (multiple selection)
                if (input.type === 'checkbox') {
                    const isStem = this.dataset.isStem == '1';
                    const cards = Array.from(document.querySelectorAll(`.candidate-card[data-position="${position}"]`));
                    const checkboxes = cards.map(c => c.querySelector('input[type="checkbox"]')).filter(Boolean);
                    const checkedBoxes = checkboxes.filter(cb => cb.checked);

                    // Determine general max winners from position card container
                    const posContainer = this.closest('.position-card');
                    const maxWinners = {{ '0' }}; // placeholder will be overridden per-position below

                    // Count current STEM selections
                    const stemSelected = checkboxes.filter(cb => cb.checked && cb.closest('.candidate-card').dataset.isStem == '1').length;

                    // We'll compute limits using attributes on the position-card element.
                    const posMax = parseInt(posContainer.dataset.maxWinners || '1');

                    // STEM-specific cap (2) applies only when the logged-in student's strand is STEM
                    const STEM_CAP = (STUDENT_STRAND === 'stem') ? 2 : Infinity;

                    // If attempting to check this box (it was unchecked), enforce limits
                    if (!input.checked) {
                        // If selecting would exceed general max winners
                        if (checkedBoxes.length >= posMax) {
                            showInlineError(this, `You may only choose up to ${posMax} candidate(s) for this position.`);
                            return;
                        }

                        // If candidate is STEM and would exceed STEM cap
                        if (isStem && stemSelected >= STEM_CAP) {
                            showInlineError(this, `You may only choose up to ${STEM_CAP} STEM candidate(s) for this position.`);
                            return;
                        }

                        input.checked = true;
                        this.classList.add('selected');
                    } else {
                        // Uncheck
                        input.checked = false;
                        this.classList.remove('selected');
                    }
                } else {
                    // Radio: unselect siblings then select this one
                    const radios = document.querySelectorAll(`.candidate-card[data-position="${position}"] input[type="radio"]`);
                    radios.forEach(r => r.closest('.candidate-card').classList.remove('selected'));
                    input.checked = true;
                    this.classList.add('selected');
                }
            });
        });

        // Initialize selected cards on page load (radios and checkboxes)
        document.querySelectorAll('input[type="radio"], input[type="checkbox"]').forEach(input => {
            if (input.checked) {
                const card = input.closest('.candidate-card');
                if (card) card.classList.add('selected');
            }
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

        // Show inline error near a candidate card
        function showInlineError(cardEl, message) {
            // remove existing small alert in this card
            const existing = cardEl.querySelector('.inline-error');
            if (existing) existing.remove();

            const div = document.createElement('div');
            div.className = 'alert alert-danger mt-2 inline-error';
            div.style.fontSize = '0.85rem';
            div.textContent = message;
            cardEl.appendChild(div);

            setTimeout(() => {
                if (div && div.parentNode) div.remove();
            }, 4000);
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
                const checkedInputs = position.querySelectorAll('input[type="radio"]:checked, input[type="checkbox"]:checked');

                if (checkedInputs.length > 0) {
                    checkedInputs.forEach(input => {
                        const candidateCard = input.closest('.candidate-card');
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
                    });
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
