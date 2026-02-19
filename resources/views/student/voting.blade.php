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
    
    <style>
        /* Center candidate cards inside each position */
        .position-card {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .position-title {
            width: 100%;
            text-align: center;
            font-weight: 700;
        }

        /* Make the title look clickable when hovered */
        .position-title {
            cursor: pointer;
        }

        .position-info {
            width: 100%;
            text-align: center;
            margin-bottom: 0.5rem;
            }
        }
            /* Make the entire header clickable */
            .position-header { cursor: pointer; }

        .candidates-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            align-items: flex-start;
            margin-top: 0.5rem;
        }

        .candidate-card {
            width: 498px; /* requested wider card */
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 1rem;
            justify-content: center; /* center content vertically */
            box-sizing: border-box;
            min-height: 435px;
            background: #fff;
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 1px 4px rgba(16,24,40,0.03);
        }

        .candidate-photo {
            width: 360px;
            height: 360px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
        }

        .candidate-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .candidate-name {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }

        /* Make cards responsive on narrow screens */
        @media (max-width: 576px) {
            .candidate-card { width: 100%; }
        }
        /* Highlight effect when scrolling to a position */
        .position-card.highlighted {
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.08), 0 6px 14px rgba(16,24,40,0.06);
            transition: box-shadow 250ms ease-in-out;
        }
    </style>

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
                    @php $posNameLower = strtolower(trim($position->name)); @endphp
                    <div class="position-header" role="button" tabindex="0">
                        <div class="position-title">
                            <i class="bi bi-award"></i>
                            {{ (strpos($posNameLower, 'house lord') !== false) ? 'House Lord/Lady' : $position->name }}
                        </div>
                        <div class="position-info">
                            @if($posMaxForStudent > 1)
                                Choose up to {{ $posMaxForStudent }} candidate(s)
                            @else
                                Choose one candidate
                            @endif
                            <span class="text-danger">*</span>
                        </div>
                    </div>

                    @error("position_{$position->id}")
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    <div class="candidates-grid">
                        {{-- Hidden inputs container for multi-select positions (one per position) --}}
                        @php $isMultipleGlobal = (int)($posMaxForStudent ?? $position->max_winners) > 1; @endphp
                        @if($isMultipleGlobal)
                            <div class="hidden-inputs" data-field="position_{{ $position->id }}">
                                @if(is_array(old("position_{$position->id}")))
                                    @foreach(old("position_{$position->id}") as $sel)
                                        <input type="hidden" name="position_{{ $position->id }}[]" value="{{ $sel }}">
                                    @endforeach
                                @endif
                            </div>
                        @endif
                        @php
                            $user = Auth::user();
                            $isRepresentative = strtolower(trim($position->name)) === 'senators';
                            $posName = strtolower(trim($position->name));
                            $isHousePosition = in_array($posName, ['house lord/lady', 'house lord']);

                            // Prefer previously selected house (old input) then user's assigned house
                            $studentHouse = strtolower(trim(old('house', $user->house ?? '')));
                            $candidateHouse = null; // will be computed per candidate
                            $studentStrand = strtolower(trim($user->strand ?? ''));
                        @endphp
                        @if($isHousePosition && empty($user->house))
                           <div class="alert alert-warning w-100">
                                <i class="bi bi-exclamation-circle"></i>
                                You have no assigned house. Please contact the admin for assistance.
                            </div>
                            {{-- <div class="house-select-wrapper w-100 mb-3">
                                <label for="userHouseSelect" class="form-label"><i class="bi bi-house-door"></i> Select your house</label>
                                <select name="house" id="userHouseSelect" class="form-select">
                                    <option value="">-- Select House --</option>
                                    <option value="ROXXO" {{ old('house') == 'ROXXO' ? 'selected' : '' }}>ROXXO</option>
                                    <option value="AZUL" {{ old('house') == 'AZUL' ? 'selected' : '' }}>AZUL</option>
                                    <option value="CAHEL" {{ old('house') == 'CAHEL' ? 'selected' : '' }}>CAHEL</option>
                                    <option value="VIERRDY" {{ old('house') == 'VIERRDY' ? 'selected' : '' }}>VIERRDY</option>
                                    <option value="GIALLIO" {{ old('house') == 'GIALLIO' ? 'selected' : '' }}>GIALLIO</option>
                                </select>
                                <div class="d-flex align-items-center mt-2">
                                    <button type="button" id="confirmHouseBtn" class="btn btn-primary btn-sm me-2">OK</button>
                                    <small class="form-text text-muted mb-0">Choose your house to see House Lord/Lady candidates.</small>
                                </div>
                                <div id="houseSelectFeedback" class="mt-2"></div>
                            </div> --}}
                        @endif
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
                                        @if(!$isMultiple)
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
                                        @if($isRepresentative && ($candidate->course ?? ''))
                                            <div class="candidate-course-badge">{{ strtoupper($candidate->course) }} Senator</div>
                                        @endif
                                        @if($isHousePosition && ($candidate->house ?? ''))
                                            <div class="candidate-course-badge">House {{ strtoupper($candidate->house) }}</div>
                                        @endif
                                        
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

        // Initialize selected cards on page load (radios and hidden-input multi-selects)
        document.querySelectorAll('.position-card').forEach(pos => {
            const posMax = parseInt(pos.dataset.maxWinners || '1', 10);
            if (posMax > 1) {
                const hiddenInputs = pos.querySelectorAll('.hidden-inputs input[type="hidden"]');
                const vals = Array.from(hiddenInputs).map(i => i.value);
                vals.forEach(v => {
                    const card = pos.querySelector(`.candidate-card[data-candidate-id="${v}"]`);
                    if (card) card.classList.add('selected');
                });
            } else {
                const radio = pos.querySelector('input[type="radio"]:checked');
                if (radio) {
                    const card = radio.closest('.candidate-card');
                    if (card) card.classList.add('selected');
                }
            }
        });

        // Run after DOM is ready so bootstrap (Vite module) is available
        document.addEventListener('DOMContentLoaded', function() {
            // Lazily create the Bootstrap modal instance (handles delayed module load)
            let reviewModal = null;
            function getReviewModal() {
                if (reviewModal) return reviewModal;
                if (!window.bootstrap || !document.getElementById('reviewModal')) return null;
                reviewModal = new window.bootstrap.Modal(document.getElementById('reviewModal'));
                return reviewModal;
            }

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

            // Handle house selection save (AJAX)
            (function() {
                const houseSelect = document.getElementById('userHouseSelect');
                const confirmBtn = document.getElementById('confirmHouseBtn');
                const feedbackEl = document.getElementById('houseSelectFeedback');
                const setHouseUrl = "{{ route('voting.set-house') }}";

                function showHouseFeedback(html, isError = false) {
                    if (!feedbackEl) return;
                    feedbackEl.innerHTML = html;
                    feedbackEl.className = isError ? 'text-danger' : 'text-success';
                    if (isError) setTimeout(() => { if (feedbackEl) feedbackEl.innerHTML = ''; }, 4000);
                }

                if (confirmBtn && houseSelect) {
                    confirmBtn.addEventListener('click', async function() {
                        const house = houseSelect.value || '';
                        if (!house) {
                            showHouseFeedback('<i class="bi bi-exclamation-circle"></i> Please select a house.', true);
                            return;
                        }

                        confirmBtn.disabled = true;
                        const originalText = confirmBtn.innerHTML;
                        confirmBtn.innerHTML = 'Saving...';

                        try {
                            const res = await fetch(setHouseUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({ house })
                            });

                            const json = await res.json().catch(() => ({}));
                            if (res.ok && json.success) {
                                // reload so server-side user.house is used when rendering candidates
                                location.reload();
                                return;
                            }

                            showHouseFeedback('<i class="bi bi-x-circle"></i> ' + (json.message || 'Unable to save house.'), true);
                        } catch (err) {
                            showHouseFeedback('<i class="bi bi-x-circle"></i> Network error. Please try again.', true);
                        } finally {
                            confirmBtn.disabled = false;
                            confirmBtn.innerHTML = originalText;
                        }
                    });
                }
            })();

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
        
        // Scroll-to-position on title click
        document.querySelectorAll('.position-title').forEach(title => {
            title.addEventListener('click', function() {
                const card = this.closest('.position-card');
                if (!card) return;
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                card.classList.add('highlighted');
                setTimeout(() => { card.classList.remove('highlighted'); }, 1800);
            });
        });
        
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
                    const hiddenInputs = position.querySelectorAll('.hidden-inputs input[type="hidden"]');
                    if (hiddenInputs.length === 0) {
                        allSelected = false;
                    } else {
                        hiddenInputs.forEach(h => {
                            const candidateId = h.value;
                            const candidateCard = position.querySelector(`.candidate-card[data-candidate-id="${candidateId}"]`);
                            if (!candidateCard) return;
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
            const m = getReviewModal();
            if (m) {
                m.show();
            } else {
                // Fallback: focus review content so user sees it
                reviewContent.scrollIntoView({ behavior: 'smooth', block: 'center' });
                console.warn('Bootstrap modal not available; ensure app.js is loaded.');
            }
        });
            // Confirm submit button
            const confirmBtn = document.getElementById('confirmSubmitBtn');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function() {
                    const m2 = getReviewModal();
                    if (m2) m2.hide();
                    // Submit the form
                    document.getElementById('votingForm').submit();
                });
            }
        });
    </script>
</body>
</html>
