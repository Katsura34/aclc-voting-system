<x-admin-layout title="Parties Management">
    <x-slot name="styles">
        <style>
            .party-card {
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .party-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            }
            .party-logo {
                width: 80px;
                height: 80px;
                object-fit: contain;
                border-radius: 8px;
                background: white;
                padding: 10px;
            }
            .party-color-badge {
                width: 30px;
                height: 30px;
                border-radius: 50%;
                display: inline-block;
                border: 2px solid white;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            }
            .stats-badge {
                background: var(--aclc-light-blue);
                color: white;
                padding: 5px 15px;
                border-radius: 20px;
                font-size: 0.9rem;
                font-weight: 500;
            }
        </style>
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-flag"></i> Parties Management</h2>
                <p class="text-muted mb-0">Manage political parties in the system</p>
            </div>
            <a href="{{ route('admin.parties.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Party
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Parties Grid -->
        @if($parties->isEmpty())
            <div class="card text-center py-5">
                <div class="card-body">
                    <i class="bi bi-flag" style="font-size: 4rem; color: var(--aclc-light-blue);"></i>
                    <h4 class="mt-3">No Parties Found</h4>
                    <p class="text-muted">Get started by creating your first party.</p>
                    <a href="{{ route('admin.parties.create') }}" class="btn btn-primary mt-2">
                        <i class="bi bi-plus-circle"></i> Create First Party
                    </a>
                </div>
            </div>
        @else
            <div class="row">
                @foreach($parties as $party)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card party-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        @if($party->logo)
                                            <img src="{{ asset('storage/' . $party->logo) }}" 
                                                 alt="{{ $party->name }}" 
                                                 class="party-logo">
                                        @else
                                            <div class="party-logo d-flex align-items-center justify-content-center" 
                                                 style="background: {{ $party->color }}20;">
                                                <i class="bi bi-flag-fill" style="font-size: 2rem; color: {{ $party->color }};"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h5 class="mb-1">{{ $party->name }}</h5>
                                            <span class="badge" style="background: {{ $party->color }}; color: white;">
                                                {{ $party->acronym }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="party-color-badge" style="background: {{ $party->color }};"></span>
                                </div>

                                @if($party->description)
                                    <p class="text-muted small mb-3">
                                        {{ Str::limit($party->description, 100) }}
                                    </p>
                                @endif

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="stats-badge">
                                        <i class="bi bi-people"></i> 
                                        {{ $party->candidates_count }} {{ Str::plural('Candidate', $party->candidates_count) }}
                                    </span>
                                </div>

                                <div class="btn-group w-100" role="group">
                                    <a href="{{ route('admin.parties.show', $party) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.parties.edit', $party) }}" 
                                       class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteParty({{ $party->id }}, '{{ $party->name }}')">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </div>

                                <!-- Hidden delete form -->
                                <form id="delete-form-{{ $party->id }}" 
                                      action="{{ route('admin.parties.destroy', $party) }}" 
                                      method="POST" 
                                      style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <x-slot name="scripts">
        <script>
            function deleteParty(id, name) {
                if (confirm(`Are you sure you want to delete "${name}"?\n\nThis action cannot be undone and will fail if the party has candidates.`)) {
                    document.getElementById('delete-form-' + id).submit();
                }
            }
        </script>
    </x-slot>
</x-admin-layout>
