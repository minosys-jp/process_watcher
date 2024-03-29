<?php

use Illuminate\Support\Facades\Route; use App\Http\Controllers\UserController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\ReceiverController;
use App\Http\Controllers\HostnameController;
use App\Http\Controllers\ProgramModuleController;
use App\Http\Controllers\ConfigureController;
use App\Http\Controllers\ApiController;

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
    return redirect()->route('login');
});

Auth::routes();

Route::get('/register', function() {
    abort(404);
});

Route::post('/register', function() {
    abort(404);
});


Route::group(['middleware' => ['auth']], function() {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::resource('/user', UserController::class);
    Route::resource('/tenant', TenantController::class);
    Route::resource('/domain', DomainController::class);
    Route::get('/hostname/{hostname}', [HostnameController::class, 'index'])->name('hostname.index');
    Route::get('/hostname/{hostname}/show', [HostnameController::class, 'show'])->name('hostname.show');
    Route::post('/hostname/{hostname}/show', [HostnameController::class, 'change'])->name('hostname.change');
    Route::get('/hostname/{hostname}/edit', [HostnameController::class, 'edit'])->name('hostname.edit');
    Route::put('/hostname/{hostname}', [HostnameController::class, 'update'])->name('hostname.update');
    Route::get('/hostname/{hostname}/module', [ProgramModuleController::class, 'index'])->name('module.index');
    Route::post('/hostname/{domain}/csv', [HostnameController::class, 'csv'])->name('hostname.csv');
    Route::get('/module/{module}/sha_history', [ProgramModuleController::class, 'sha_history'])->name('module.sha_history');
    Route::put('/module/{module}/sha_history', [ProgramModuleController::class, 'change_status'])->name('module.sha_history');
    Route::get('/module/{module}/graph_history', [ProgramModuleController::class, 'graph_history'])->name('module.graph_history');
    Route::get('/module/{mlog}/child_history', [ProgramModuleController::class, 'child_history'])->name('module.child_history');
    Route::get('/config', [ConfigureController::class, 'index'])->name('config.index');
    Route::get('/config/{tenant}/create', [ConfigureController::class, 'create'])->name('config.create');
    Route::post('/config/{tenant}', [ConfigureController::class, 'store'])->name('config.store');
    Route::get('/config/{tenant}/edit/{cid}', [ConfigureController::class, 'edit'])->name('config.edit');
    Route::put('/config/{tenant}/update/{cid}', [ConfigureController::class, 'update'])->name('config.update');
    Route::delete('/config/{tenant}/delete/{cid}', [ConfigureController::class, 'destroy'])->name('config.destroy');
});
