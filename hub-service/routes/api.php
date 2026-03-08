<?php

use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/checklists', [ChecklistController::class, 'index']);
Route::get('/employees', [EmployeeController::class, 'index']);