<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'KasirKel Backend API',
        'status' => 'online',
        'message' => 'Backend is working! Gunakan frontend di http://localhost:5173',
    ]);
});

