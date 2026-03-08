<?php

use App\Http\Controllers\ChecklistsController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\StepsController;
use App\Http\Controllers\SchemaController;
use Illuminate\Support\Facades\Route;

Route::get('/checklists', [ChecklistsController::class, 'index']);
Route::get('/employees', [EmployeesController::class, 'index']);
Route::get('/steps', [StepsController::class, 'index']);
Route::get('/schema/{stepId}', [SchemaController::class, 'show']);