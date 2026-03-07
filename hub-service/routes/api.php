<?php

use App\Http\Controllers\ChecklistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/checklist', [ChecklistController::class, 'index']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
