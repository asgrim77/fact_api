<?php

use App\Models\Company;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('prueba', function () {
    $company = Company::first();
    return Storage::get($company->logo_path);
});