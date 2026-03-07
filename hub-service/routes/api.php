<?php

use App\Http\Controllers\ChecklistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/checklists', [ChecklistController::class, 'index']);
