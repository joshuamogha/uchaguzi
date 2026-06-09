<?php

use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\ChurchGroupController;
use App\Http\Controllers\Admin\CommunityController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ElectionContestController;
use App\Http\Controllers\Admin\ElectionController;
use App\Http\Controllers\Admin\ElectionResultController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoterController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PublicElectionController;
use App\Http\Controllers\VotingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicElectionController::class, 'home'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')->name('admin.')->middleware('auth')->scopeBindings()->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/elections', [ElectionController::class, 'index'])->name('elections.index');
    Route::get('/elections/{election}/candidates/export-sheet', [CandidateController::class, 'exportSheet'])->name('elections.candidates.export-sheet');
    Route::get('/elections/{election}/results/manual-entry', [ElectionResultController::class, 'editManualEntry'])->name('elections.results.manual-entry');
    Route::post('/elections/{election}/results/manual-entry/ballots', [ElectionResultController::class, 'storeManualBallot'])->name('elections.results.manual-entry.ballots.store');

    Route::middleware('can:admin-only')->group(function () {
        Route::resource('communities', CommunityController::class)->except('show');
        Route::resource('church-groups', ChurchGroupController::class)->except('show');
        Route::resource('members', MemberController::class)->except('show');
        Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update']);

        Route::get('/elections/create', [ElectionController::class, 'create'])->name('elections.create');
        Route::post('/elections', [ElectionController::class, 'store'])->name('elections.store');
        Route::get('/elections/{election}/edit', [ElectionController::class, 'edit'])->name('elections.edit');
        Route::put('/elections/{election}', [ElectionController::class, 'update'])->name('elections.update');
        Route::delete('/elections/{election}', [ElectionController::class, 'destroy'])->name('elections.destroy');

        Route::get('/elections/{election}/contests', [ElectionContestController::class, 'index'])->name('elections.contests.index');
        Route::get('/elections/{election}/contests/create', [ElectionContestController::class, 'create'])->name('elections.contests.create');
        Route::post('/elections/{election}/contests', [ElectionContestController::class, 'store'])->name('elections.contests.store');
        Route::get('/elections/{election}/contests/{contest}/edit', [ElectionContestController::class, 'edit'])->name('elections.contests.edit');
        Route::put('/elections/{election}/contests/{contest}', [ElectionContestController::class, 'update'])->name('elections.contests.update');
        Route::delete('/elections/{election}/contests/{contest}', [ElectionContestController::class, 'destroy'])->name('elections.contests.destroy');

        Route::get('/elections/{election}/candidates', [CandidateController::class, 'index'])->name('elections.candidates.index');
        Route::get('/elections/{election}/contests/{contest}/candidates/create', [CandidateController::class, 'create'])->name('elections.candidates.create');
        Route::post('/elections/{election}/contests/{contest}/candidates', [CandidateController::class, 'store'])->name('elections.candidates.store');
        Route::get('/elections/{election}/contests/{contest}/candidates/{candidate}/edit', [CandidateController::class, 'edit'])->name('elections.candidates.edit');
        Route::put('/elections/{election}/contests/{contest}/candidates/{candidate}', [CandidateController::class, 'update'])->name('elections.candidates.update');
        Route::delete('/elections/{election}/contests/{contest}/candidates/{candidate}', [CandidateController::class, 'destroy'])->name('elections.candidates.destroy');

        Route::get('/elections/{election}/voters', [VoterController::class, 'index'])->name('elections.voters.index');
        Route::post('/elections/{election}/voters/generate', [VoterController::class, 'generate'])->name('elections.voters.generate');
        Route::get('/elections/{election}/voters/cards', [VoterController::class, 'cards'])->name('elections.voters.cards');
        Route::patch('/elections/{election}/voters/{voter}/eligibility', [VoterController::class, 'toggleEligibility'])->name('elections.voters.toggle-eligibility');

        Route::get('/elections/{election}/results', [ElectionResultController::class, 'index'])->name('elections.results.index');
        Route::put('/elections/{election}/results/manual-entry', [ElectionResultController::class, 'updateManualEntry'])->name('elections.results.manual-entry.update');
        Route::get('/elections/{election}/results/export', [ElectionResultController::class, 'export'])->name('elections.results.export');
        Route::post('/elections/{election}/results/{contest}/runoff', [ElectionResultController::class, 'createRunoff'])->name('elections.results.runoff');
    });
});

Route::get('/vote/verify', [VotingController::class, 'showVerifyForm'])->name('vote.verify.form');
Route::post('/vote/verify', [VotingController::class, 'verifyToken'])->middleware('throttle:vote-verification')->name('vote.verify');
Route::post('/vote/confirm-pin', [VotingController::class, 'confirmPin'])->middleware('throttle:vote-pin')->name('vote.confirm-pin');
Route::middleware('verified.voter')->group(function () {
    Route::get('/vote/ballot/{election}', [VotingController::class, 'ballot'])->name('vote.ballot');
    Route::post('/vote/review/{election}', [VotingController::class, 'review'])->name('vote.review');
    Route::post('/vote/submit/{election}', [VotingController::class, 'submit'])->name('vote.submit');
});
Route::get('/vote/success', [VotingController::class, 'success'])->name('vote.success');

Route::get('/elections/{election}/candidates', [PublicElectionController::class, 'candidates'])->name('public.elections.candidates');
Route::get('/elections/{election}/results', [PublicElectionController::class, 'results'])->name('public.elections.results');
