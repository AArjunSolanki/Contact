<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomFieldController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [ContactController::class, 'index'])->name('contacts.index');
Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
Route::get('/contacts/filter', [ContactController::class, 'filter'])->name('contacts.filter');
Route::delete('/contacts/{id}', [ContactController::class, 'destroy'])->name('contacts.destroy');
Route::put('/contacts/{id}', [ContactController::class, 'update'])->name('contacts.update');
Route::post('/contacts/merge-init', [ContactController::class, 'mergeInit'])->name('contacts.mergeInit');
Route::post('/contacts/do-merge', [ContactController::class, 'doMerge'])->name('contacts.doMerge');
Route::get('/contacts/ajax-compare/{a}/{b}', [ContactController::class, 'ajaxCompare']);
Route::get('/contacts/ajax-merge-preview/{master}/{secondary}', [ContactController::class, 'ajaxMergePreview']);

Route::get('/custom-fields', [CustomFieldController::class, 'index'])->name('custom-fields.index');
Route::delete('/custom-fields/{id}', [CustomFieldController::class, 'destroy'])->name('custom-fields.destroy');
Route::post('/custom-fields/ajax-store', [CustomFieldController::class, 'storeAjax'])->name('custom-fields.ajax-store');
Route::put('/custom-fields/{id}', [CustomFieldController::class, 'update'])->name('custom-fields.update');
Route::get('/contacts/{id}/custom-fields', [ContactController::class, 'getCustomFields']);
