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
                @php
                    // Determine if this position should allow multiple selections for this student
                    $user = Auth::user();
                    $studentStrand = strtolower(trim($user->strand ?? ''));
                    $isRepresentative = strtolower(trim($position->name)) === 'senators';
                    // If senators and student is STEM, allow up to 2 selections for this position
                    $posMaxForStudent = ($isRepresentative && $studentStrand === 'stem') ? 2 : $position->max_winners;
                @endphp
                <div class="position-card" data-max-winners="{{ $posMaxForStudent }}">
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
                                @php
                                    // Allow multiple if position max for student > 1
                                    $isMultiple = (int)($posMaxForStudent ?? $position->max_winners) > 1;
                                @endphp
                                <label class="candidate-card" data-position="{{ $position->id }}" data-candidate-id="{{ $candidate->id }}" data-course="{{ $candidate->course }}" data-is-stem="{{ strtolower($candidate->course) === 'stem' ? 1 : 0 }}">
                                    @if($isMultiple)
                                        {{-- Hidden inputs container for selected values (populated by JS) --}}
                                        <div class="hidden-inputs" data-field="position_{{ $position->id }}">
                                            @if(is_array(old("position_{$position->id}")))
                                                @foreach(old("position_{$position->id}") as $sel)
                                                    <input type="hidden" name="position_{{ $position->id }}[]" value="{{ $sel }}">
                                                @endforeach
                                            @endif
                                        </div>
                                    @else
                                        <input 
                                            type="radio" 
                                            name="position_{{ $position->id }}" 
                                            value="{{ $candidate->id }}"
                                            {{ old("position_{$position->id}") == $candidate->id ? 'checked' : '' }}
                                        >
                                    @endif
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
        
        // Handle candidate card selection (supports radio and checkbox)
        document.querySelectorAll('.candidate-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    const position = this.dataset.position;
                    const posContainer = this.closest('.position-card');
                    if (!posContainer) return;

                    const posMax = parseInt(posContainer.dataset.maxWinners || '1', 10);
                    const isMultiple = posMax > 1;

                    const candidateId = this.dataset.candidateId;
                    if (!candidateId) return;

                    if (isMultiple) {
                        // Manage hidden inputs under the position container
                        const hiddenContainer = posContainer.querySelector('.hidden-inputs');
                        if (!hiddenContainer) return;

                        const existing = Array.from(hiddenContainer.querySelectorAll('input[type="hidden"]'));
                        const values = existing.map(i => i.value);

                        const isSelected = values.includes(candidateId);

                        // Count STEM selected
                        const stemSelected = Array.from(posContainer.querySelectorAll('.candidate-card.selected')).filter(c => c.dataset.isStem == '1').length;
                        const isStem = this.dataset.isStem == '1';

                        if (!isSelected) {
                            // Enforce max
                            if (values.length >= posMax) {
                                showInlineError(this, `You may only choose up to ${posMax} candidate(s) for this position.`);
                                return;
                            }
                            // Enforce STEM cap when applicable (only affects senators; server also enforces)
                            if (isStem && stemSelected >= 2) {
                                showInlineError(this, `You may only choose up to 2 STEM candidate(s) for this position.`);
                                return;
                            }

                            // Add hidden input
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = `position_${position}[]`;
                            input.value = candidateId;
                            hiddenContainer.appendChild(input);

                            this.classList.add('selected');
                        } else {
                            // Remove hidden input
                            const match = existing.find(i => i.value === candidateId);
                            if (match && match.parentNode) match.parentNode.removeChild(match);
                            this.classList.remove('selected');
                        }
                    } else {
                        // Single selection radio behavior
                        // Unselect siblings
                        posContainer.querySelectorAll('.candidate-card.selected').forEach(c => c.classList.remove('selected'));
                        // Remove any existing radio input selection (radios are native inputs)
                        const radios = posContainer.querySelectorAll('input[type="radio"]');
                        radios.forEach(r => r.checked = false);

                        // Find this label's radio and check it (if present)
                        const radio = this.querySelector('input[type="radio"]');
                        if (radio) radio.checked = true;
                        this.classList.add('selected');
                    }
                });
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
                const posMax = parseInt(position.dataset.maxWinners || '1', 10);

                if (posMax > 1) {
                    const checkedBoxes = position.querySelectorAll('input[type="checkbox"]:checked');
                    if (checkedBoxes.length === 0) {
                        allSelected = false;
                    } else {
                        checkedBoxes.forEach(cb => {
                            const candidateCard = cb.closest('.candidate-card');
                            const candidateName = candidateCard.querySelector('.candidate-name').textContent.trim();
                            const candidateParty = candidateCard.querySelector('.candidate-party');
                            const partyName = candidateParty ? candidateParty.textContent.trim() : 'Independent';

                            const safePositionTitle = escapeHtml(positionTitle);
                            const safeCandidateName = escapeHtml(candidateName);
                            const safePartyName = escapeHtml(partyName);

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
                    }
                } else {
                    const selectedRadio = position.querySelector('input[type="radio"]:checked');
                    if (selectedRadio) {
                        const candidateCard = selectedRadio.closest('.candidate-card');
                        const candidateName = candidateCard.querySelector('.candidate-name').textContent.trim();
                        const candidateParty = candidateCard.querySelector('.candidate-party');
                        const partyName = candidateParty ? candidateParty.textContent.trim() : 'Independent';

                        // Escape and sanitize
                        const safePositionTitle = escapeHtml(positionTitle);
                        const safeCandidateName = escapeHtml(candidateName);
                        const safePartyName = escapeHtml(partyName);

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
