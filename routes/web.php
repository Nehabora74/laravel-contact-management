<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('contacts.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Contacts
    Route::resource('contacts', ContactController::class);
    Route::post('/contacts/{contact}/notes', [ContactController::class, 'addNote'])->name('contacts.notes.store');
    Route::post('/contacts/{contact}/activities', [ContactController::class, 'addActivity'])->name('contacts.activities.store');
    Route::get('/contacts-export', [ContactController::class, 'export'])->name('contacts.export');
    Route::post('/contacts-import', [ContactController::class, 'import'])->name('contacts.import');
    Route::post('/contacts-check-duplicates', [ContactController::class, 'checkDuplicates'])->name('contacts.check-duplicates');

    // Companies
    Route::resource('companies', CompanyController::class);

    // Groups
    Route::resource('groups', GroupController::class);
});

require __DIR__.'/auth.php';
