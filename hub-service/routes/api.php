<?php

use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\StepsController;
use Illuminate\Support\Facades\Route;

Route::get('/checklists', [ChecklistController::class, 'index']);
Route::get('/employees', [EmployeeController::class, 'index']);
Route::get('/steps', [StepsController::class, 'index']);