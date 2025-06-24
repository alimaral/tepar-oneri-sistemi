<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuggestionController; // Kullanıcı tarafı için
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SuggestionController as AdminSuggestionController; // Admin tarafı için
use App\Http\Controllers\Admin\DepartmentController as AdminDepartmentController;
use App\Http\Controllers\CommentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect()->route('suggestions.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Kullanıcı Tarafı Öneriler
    Route::resource('suggestions', SuggestionController::class)->except(['edit', 'update', 'destroy']);
    Route::post('/suggestions/{suggestion}/comments', [CommentController::class, 'store'])->name('suggestions.comments.store');
});

// YÖNETİCİ ROTLARI
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Yönetici Ana Paneli
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // ÖNEMLİ: Daha spesifik rotalar (export gibi) resource rotalarından ÖNCE tanımlanmalı
        // Yönetici Öneri Excel Export
        Route::get('suggestions/export', [AdminSuggestionController::class, 'export'])->name('suggestions.export');

        // Yönetici Öneri Yönetimi (CRUD işlemleri - export hariç)
        // 'destroy' metodunu eklemedik, senaryona göre ekleyebilirsin.
        Route::resource('suggestions', AdminSuggestionController::class)->only(['index', 'show', 'edit', 'update']);

        // Departman Yönetimi (CRUD işlemleri)
        Route::resource('departments', AdminDepartmentController::class);

    });

require __DIR__.'/auth.php';
