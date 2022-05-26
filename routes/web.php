<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


use Illuminate\Http\Request;

use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\YearController;

// To read Excel file
use App\Http\Controllers\Excel;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});


// To read the Excel File
Route::get('/excel', Excel::class)->name('excel');

//COMPANIES -------------------- STARTS ---------------------------
Route::get('companies', [CompanyController::class, 'index'])
    ->name('companies')
    ->middleware('auth');

Route::get('companies/create', [CompanyController::class, 'create'])
    ->name('companies.create')
    ->middleware('auth');

Route::post('companies', [CompanyController::class, 'store'])
    ->name('companies.store')
    ->middleware('auth');

Route::get('companies/{company}/edit', [CompanyController::class, 'edit'])
    ->name('companies.edit')
    ->middleware('auth');

Route::put('companies/{company}', [CompanyController::class, 'update'])
    ->name('companies.update')
    ->middleware('auth');

Route::delete('companies/{company}', [CompanyController::class, 'destroy'])
    ->name('companies.destroy')
    ->middleware('auth');

//TO CHANGE COMPANY THE FROM DROPDOWN
Route::get('companies/coch/{id}', [CompanyController::class, 'coch'])
    ->name('companies.coch');

//COMPANIES -------------------- END ---------------------------


//YEARS ------------------------------------ STARTS ------------------
Route::get('years', [YearController::class, 'index'])
    ->name('years')
    ->middleware('auth');

Route::get('years/create', [YearController::class, 'create'])
    ->name('years.create')
    ->middleware('auth');

Route::post('years', [YearController::class, 'store'])
    ->name('years.store')
    ->middleware('auth');

Route::get('years/{year}/edit', [YearController::class, 'edit'])
    ->name('years.edit')
    ->middleware('auth');

Route::put('years/{year}', [YearController::class, 'update'])
    ->name('years.update')
    ->middleware('auth');

Route::delete('years/{year}', [YearController::class, 'destroy'])
    ->name('years.destroy')
    ->middleware('auth');

//TO CHANGE YEAR THE FROM DROPDOWN
Route::get('years/yrch/{id}', [YearController::class, 'yrch'])
    ->name('years.yrch');

Route::get('years/{year}/close', [YearController::class, 'close'])
    ->name('years.close')
    ->middleware('auth');
//YEARS ------------------------------------ END ------------------





