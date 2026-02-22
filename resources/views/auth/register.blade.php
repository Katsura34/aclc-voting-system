<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Registration</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    /* Prevent horizontal page scrollbar (common with transforms) */
    html, body { overflow-x: hidden;
    }
      
    body {
      height: 90vh;
    }

    /* Top fixed scroller bar */
    .scroller-fixed-bottom{
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  z-index: 1000;
  background: transparent;
}

/* prevent the form being covered by the bottom bar */
.page-wrap{
  padding-top: 50px; /* adjust if needed */
}

    .scroller {
      width: 100%;
      overflow: hidden !important; /* removes scroll + scrollbar */
      white-space: nowrap;
      padding: 10px 0;
      scrollbar-width: none;       /* Firefox */
      -ms-overflow-style: none;    /* IE/old Edge */
      pointer-events: auto;        /* re-enable only inside if needed */
    }
    .scroller::-webkit-scrollbar { display: none; }

    .scroller-track {
      display: flex;
      gap: 2rem;
      width: max-content;
      will-change: transform;
      align-items: center;
      padding: 0 12px;
    }

    .scroller-item {
      flex: 0 0 auto;
      min-width: 260px;
      background: #f5f5f5;
      border-radius: 10px;
      padding: 0.6rem 1.1rem;
      text-align: center;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    }

   


    .github-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.95rem;
    }


    .social-item {
  display: flex;
  align-items: center;
  gap: 2.5rem;           /* space between GitHub / FB / Guild */
  white-space: nowrap;
}

.social-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;           /* icon/logo ↔ text */
  font-weight: 600;
  text-decoration: none;
  color: #111;
}

.social-link i {
  font-size: 1.1rem;
}

/* Programmers Guild logo */
.guild-logo {
  height: 22px;
  width: auto;
  object-fit: contain;
}

/* Brand hint colors */
.social-link.facebook i {
  color: #1877f2;
}
  </style>
</head>

<body class="bg-light" id="mainBody">

  @if(session('show_credentials'))
    <div class="modal fade show" id="credentialsModal" tabindex="-1" style="display:block; background:rgba(0,0,0,0.4);" aria-modal="true" role="dialog">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Registration Successful</h5>
            <button type="button" class="btn-close" id="closeModalBtn" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="mb-2">Please remember your credentials:</p>
            <ul class="list-group mb-3">
              <li class="list-group-item"><strong>School Name:</strong> {{ session('show_credentials.school_name') }}</li>
              <li class="list-group-item"><strong>First Name:</strong> {{ session('show_credentials.firstname') }}</li>
              <li class="list-group-item"><strong>Last Name:</strong> {{ session('show_credentials.lastname') }}</li>
              <li class="list-group-item"><strong>Email:</strong> {{ session('show_credentials.email') }}</li>
              <li class="list-group-item"><strong>Password:</strong> {{ session('show_credentials.password') }}</li>
            </ul>
            <div class="alert alert-warning">This will close automatically in 10 seconds.</div>
          </div>
        </div>
      </div>
    </div>
    <script>
      setTimeout(function() {
        window.location.href = '{{ route('register') }}';
      }, 10000);
      document.getElementById('closeModalBtn').onclick = function() {
        window.location.href = '{{ route('register') }}';
      };
      // Prevent interaction with background
      document.body.style.overflow = 'hidden';
    </script>
  @endif


  <div class="page-wrap">
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> Please fix the errors below.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card mt-4 shadow" id="cardBox">
            <div class="card-body">
              <h2 class="card-title text-center mb-4">Voters Registration</h2>

              <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-3">
                  <label for="school_name" class="form-label">School Name</label>
                  <input type="text" id="school_name" name="school_name" class="form-control @error('school_name') is-invalid @enderror" required autofocus placeholder="Enter your school name" value="{{ old('school_name') }}">
                  @error('school_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="row mb-3">
                  <div class="col">
                    <label for="firstname" class="form-label">First Name</label>
                    <input type="text" id="firstname" name="firstname" class="form-control @error('firstname') is-invalid @enderror" required placeholder="Enter your first name" value="{{ old('firstname') }}">
                    @error('firstname')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col">
                    <label for="lastname" class="form-label">Last Name</label>
                    <input type="text" id="lastname" name="lastname" class="form-control @error('lastname') is-invalid @enderror" required placeholder="Enter your last name" value="{{ old('lastname') }}">
                    @error('lastname')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                <div class="mb-3">
                  <label for="email" class="form-label">Email Address</label>
                  <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" required placeholder="Enter your email address" value="{{ old('email') }}">
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required placeholder="Create a password">
                  @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-4">
                  <label for="password_confirmation" class="form-label">Confirm Password</label>
                  <input type="password" id="password_confirmation" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" required placeholder="Re-enter your password">
                  @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100">Register</button>
              </form>

            </div>
          </div>
        </div>
      </div>
    </div>

    
  <!-- Fixed top marquee -->
  <div class="scroller-fixed-bottom">
    <div class="scroller" data-speed="20">
      <div class="scroller-track" id="scrollerTrack">
        <div class="scroller-item github-item">
        <i class="bi bi-github"></i>
        <span>github.com/Katsura6</span>
        </div>
         <!-- Facebook -->
        <div class="scroller-item github-item">
        <a class="social-link facebook"
            href="https://facebook.com/"
            target="_blank" rel="noopener">
            <i class="bi bi-facebook"></i>
            <span>Jeson Beltran</span>
        </a>
        </div>
        <!-- Programmers Guild -->
        <div class="scroller-item github-item">
        <div class="social-link guild">
            <img
            src="{{ asset('storage/logos/programmers-guild-logo.png') }}"
            alt="Programmers Guild Logo"
            class="guild-logo"
            >
            <span>Programmers Guild</span>
        </div>
        </div>
      </div>
    </div>
  </div>
  </div>

<script>
(function () {
  const scroller = document.querySelector(".scroller");
  const track = document.getElementById("scrollerTrack");
  if (!scroller || !track) return;

  const SPEED_PX_PER_SEC = 50;

  // Save original items (the "one full set")
  const originalNodes = Array.from(track.children).map(n => n.cloneNode(true));

  // Measure width of ONE set
  const measureOneSetWidth = () => {
    track.innerHTML = "";
    originalNodes.forEach(n => track.appendChild(n.cloneNode(true)));
    return track.scrollWidth;
  };

  let oneSetWidth = measureOneSetWidth();

  // Now fill enough copies so there is NEVER a gap
  // Need at least: viewport + oneSetWidth (buffer)
  while (track.scrollWidth < scroller.clientWidth + oneSetWidth) {
    originalNodes.forEach(n => track.appendChild(n.cloneNode(true)));
  }

  // And add one more full set for seamless looping
  originalNodes.forEach(n => track.appendChild(n.cloneNode(true)));

  // Re-measure on resize (important)
  window.addEventListener("resize", () => {
    oneSetWidth = measureOneSetWidth();
    while (track.scrollWidth < scroller.clientWidth + oneSetWidth) {
      originalNodes.forEach(n => track.appendChild(n.cloneNode(true)));
    }
    originalNodes.forEach(n => track.appendChild(n.cloneNode(true)));
  });

  // Smooth rAF animation
  let x = 0;
  let last = performance.now();

  track.style.willChange = "transform";
  track.style.transform = "translate3d(0,0,0)";

  function tick(now) {
    const dt = (now - last) / 1000;
    last = now;

    x -= SPEED_PX_PER_SEC * dt;

    // Loop exactly by ONE set width
    if (-x >= oneSetWidth) x += oneSetWidth;

    track.style.transform = `translate3d(${x}px, 0, 0)`;
    requestAnimationFrame(tick);
  }

  requestAnimationFrame(tick);
})();
</script>

</body>
</html>