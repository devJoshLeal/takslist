<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use Inertia\Inertia;
require __DIR__.'/auth.php';
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    Route::get('/alltasks', [TaskController::class, 'getTasks'])->name('tasks.gettasks');
    Route::post('/newtask', [TaskController::class, 'store'])->name('tasks.newtask');
    Route::delete('/deletetask/{task_id}', [TaskController::class, 'destroy'])->name('tasks.delete');
    Route::patch('/updatetask/{task_id}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('/completetask/{task_id}', [TaskController::class, 'complete'])->name('tasks.complete');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


