<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;

Route::get('/', [ContactController::class, 'index']);
Route::resource('contacts', ContactController::class);
Route::post('contacts/merge', [ContactController::class, 'merge']);
Route::get('contacts-active', [ContactController::class, 'getActiveContacts']);
Route::get('contacts-custom-fields', [ContactController::class, 'getCustomFields']);
Route::get('contacts/{masterId}/merged-data', [ContactController::class, 'getMergedData']);
