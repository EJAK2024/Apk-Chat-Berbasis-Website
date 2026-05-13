<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Chat routes (harus login)
Route::middleware('auth')->group(function () {
    Route::get('/', [RoomController::class, 'index'])->name('chat.index');
    Route::post('/rooms/group', [RoomController::class, 'createGroup'])->name('rooms.group');
    Route::post('/rooms/private', [RoomController::class, 'createPrivate'])->name('rooms.private');
    Route::post('/rooms/{room}/members', [RoomController::class, 'addMember']);
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::post('/rooms/{room}/messages', [MessageController::class, 'send'])->name('messages.send');
});