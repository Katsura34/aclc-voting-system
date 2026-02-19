<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ElectionController;
use App\Http\Controllers\Admin\PartyController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Student\VotingController;

Route::get('/', function () {
    return redirect('/login');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Student Voting Routes
    Route::get('/voting', [VotingController::class, 'index'])->name('voting.index');
    Route::post('/voting/submit', [VotingController::class, 'submit'])->name('voting.submit');
    Route::post('/voting/set-house', [VotingController::class, 'setHouse'])->name('voting.set-house');
    Route::get('/voting/success', [VotingController::class, 'success'])->name('voting.success');
    
    // Legacy dashboard route (redirect to voting)
    Route::get('/dashboard', function () {
        return redirect()->route('voting.index');
    })->name('dashboard');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // Election Management
    Route::resource('elections', ElectionController::class)->names([
        'index' => 'admin.elections.index',
        'create' => 'admin.elections.create',
        'store' => 'admin.elections.store',
        'show' => 'admin.elections.show',
        'edit' => 'admin.elections.edit',
        'update' => 'admin.elections.update',
        'destroy' => 'admin.elections.destroy',
    ]);
    Route::post('/elections/{election}/toggle-active', [ElectionController::class, 'toggleActive'])
        ->name('admin.elections.toggle-active');
    
    // Party Management
    Route::resource('parties', PartyController::class)->names([
        'index' => 'admin.parties.index',
        'create' => 'admin.parties.create',
        'store' => 'admin.parties.store',
        'show' => 'admin.parties.show',
        'edit' => 'admin.parties.edit',
        'update' => 'admin.parties.update',
        'destroy' => 'admin.parties.destroy',
    ]);
    
    // Position Management
    Route::get('positions/download-template', [PositionController::class, 'downloadTemplate'])
        ->name('admin.positions.download-template');
    Route::post('positions/import', [PositionController::class, 'import'])
        ->name('admin.positions.import');
    Route::resource('positions', PositionController::class)->names([
        'index' => 'admin.positions.index',
        'create' => 'admin.positions.create',
        'store' => 'admin.positions.store',
        'show' => 'admin.positions.show',
        'edit' => 'admin.positions.edit',
        'update' => 'admin.positions.update',
        'destroy' => 'admin.positions.destroy',
    ]);
    
    // Candidate Management
    Route::get('candidates/download-template', [CandidateController::class, 'downloadTemplate'])
        ->name('admin.candidates.download-template');
    Route::post('candidates/import', [CandidateController::class, 'import'])
        ->name('admin.candidates.import');
    Route::resource('candidates', CandidateController::class)->names([
        'index' => 'admin.candidates.index',
        'create' => 'admin.candidates.create',
        'store' => 'admin.candidates.store',
        'show' => 'admin.candidates.show',
        'edit' => 'admin.candidates.edit',
        'update' => 'admin.candidates.update',
        'destroy' => 'admin.candidates.destroy',
    ]);
    
    // Results
    Route::get('results', [ResultController::class, 'index'])->name('admin.results.index');
    Route::get('results/export', [ResultController::class, 'export'])->name('admin.results.export');
    Route::get('results/print', [ResultController::class, 'print'])->name('admin.results.print');
    
    // Audit Logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs.index');
    Route::get('audit-logs/export', [AuditLogController::class, 'export'])->name('admin.audit-logs.export');
    Route::get('audit-logs/print', [AuditLogController::class, 'print'])->name('admin.audit-logs.print');
    

    
    // User Management
    Route::get('users/download-template', [UserController::class, 'downloadTemplate'])
        ->name('admin.users.download-template');
    Route::post('users/import', [UserController::class, 'import'])
        ->name('admin.users.import');
    Route::patch('users/{user}/reset-vote', [UserController::class, 'resetVote'])
        ->name('admin.users.reset-vote');
    Route::post('users/reset-all-votes', [UserController::class, 'resetAllVotes'])
        ->name('admin.users.reset-all-votes');
    Route::resource('users', UserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);
});
