<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


use Illuminate\Http\Request;

use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\YearController;
use App\Http\Controllers\AccountGroupController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\FileMangementController;
use App\Http\Controllers\DefaultFoldersCreation;

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
Route::get('trial', function () {
        return Inertia::render('TrialExcel/Index');
    })->name('trial.index')->middleware('auth');
Route::post('trial/read', Excel::class)->name('trial.read')->middleware('auth');

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


//ACCOUNTS GROUPS ----------------------- END --------------------
// Route::get('accountgroups/generate', [GroupSeeder::class, 'run'])
//     ->name('accountgroups.generate')
//     ->middleware('auth');

Route::get('accountgroups', [AccountGroupController::class, 'index'])
    ->name('accountgroups')
    ->middleware('auth');

Route::get('accountgroups/create', [AccountGroupController::class, 'create'])
    ->name('accountgroups.create')
    ->middleware('auth');

Route::post('accountgroups/create', [AccountGroupController::class, 'create'])
    ->name('accountgroups.create')
    ->middleware('auth');

Route::post('accountgroups', [AccountGroupController::class, 'store'])
    ->name('accountgroups.store')
    ->middleware('auth');

Route::get('accountgroups/{accountgroup}/edit', [AccountGroupController::class, 'edit'])
    ->name('accountgroups.edit')
    ->middleware('auth');

Route::put('accountgroups/{accountgroup}', [AccountGroupController::class, 'update'])
    ->name('accountgroups.update')
    ->middleware('auth');

Route::delete('accountgroups/{accountgroup}', [AccountGroupController::class, 'destroy'])
    ->name('accountgroups.destroy')
    ->middleware('auth');
//ACCOUNTS GROUPS ----------------------- END --------------------


//ACCOUNTS ----------------------- STARTS --------------------
Route::get('accounts', [AccountController::class, 'index'])
    ->name('accounts')
    ->middleware('auth');

Route::get('accounts/create', [AccountController::class, 'create'])
    ->name('accounts.create')
    ->middleware('auth');

Route::post('accounts', [AccountController::class, 'store'])
    ->name('accounts.store')
    ->middleware('auth');

Route::get('accounts/{account}/edit', [AccountController::class, 'edit'])
    ->name('accounts.edit')
    ->middleware('auth');

Route::put('accounts/{account}', [AccountController::class, 'update'])
    ->name('accounts.update')
    ->middleware('auth');

Route::delete('accounts/{account}', [AccountController::class, 'destroy'])
    ->name('accounts.destroy')
    ->middleware('auth');
//ACCOUNTS ----------------------- END --------------------



//File Management ------------------------- STARTS --------------------------------------

Route::get('filing/createFolder', [FileMangementController::class, 'createFolder'])
    ->name('filing.createFolder')
    ->middleware('auth');

Route::post('filing/folder', [FileMangementController::class, 'storeFolder'])
    ->name('filing.store.folder')
    ->middleware('auth');

Route::get('filing/uploadFile/{folder_id}', [FileMangementController::class, 'uploadFile'])
    ->name('filing.uploadFile')
    ->middleware('auth');

Route::post('filing/file/{parent_id}', [FileMangementController::class, 'storeFile'])
    ->name('filing.store.file')
    ->middleware('auth');

Route::get('filing/downloadFile/{file_id}', [FileMangementController::class, 'downloadFile'])
    ->name('filing.downloadFile')
    ->middleware('auth');

Route::get('filing/deleteFileFolder/{file_folder_id}', [FileMangementController::class, 'deleteFileFolder'])
    ->name('filing.deleteFileFolder')
    ->middleware('auth');

Route::controller(FileMangementController::class)->group(function () {
    Route::get('/filing/{parent_name_id}', 'filing')->name('filing');
    Route::get('filing/createFolder', 'createFolder')->name('filing.createFolder');
    Route::post('filing', 'storeFolder')->name('filing.storeFolder');
});



//File Management ------------------------- END --------------------------------------


Route::get('/routes', function() {
    //Clear Route cache:
    $exitCode = Artisan::call('route:clear');
    //Route cache:
    $exitCode2 = Artisan::call('route:cache');
    return back()->with('success', 'Cache clear');
    return '<h1>Route cache cleared</h1>';
});

