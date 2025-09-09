<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('contact');
});

Route::resource('contact', ContactController::class)->except(['create', 'edit']);
Route::post('contact/index-ajax', [ContactController::class, 'indexAjax'])->name('contact.list');

Route::post('/contacts/imports', [ContactImportController::class, 'store'])->name('contacts.imports.store');
Route::get('/contacts/imports/{import}', [ContactImportController::class, 'show'])->name('contacts.imports.show');
