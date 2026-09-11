<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalancaController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\ConferenciaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LojaController;
use App\Http\Controllers\GlpiConfigController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware(['role:admin,gerente'])->group(function () {
        Route::get('/coleta', [ConferenciaController::class, 'create'])->name('coleta.create');
        Route::get('/coleta/balancas', [ConferenciaController::class, 'getBalancas'])->name('coleta.balancas');
        Route::post('/coleta/preview', [ConferenciaController::class, 'preview'])->name('coleta.preview');
        Route::post('/coleta', [ConferenciaController::class, 'store'])->name('coleta.store');
        Route::get('/coleta/historico', [ConferenciaController::class, 'historico'])->name('coleta.historico');
        Route::get('/coleta/{conferencia}', [ConferenciaController::class, 'show'])->name('coleta.show');

        Route::get('/calendario', [CalendarioController::class, 'index'])->name('calendario');

        Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
        Route::get('/relatorios/exportar', [RelatorioController::class, 'exportCsv'])->name('relatorios.export');
    });

    Route::middleware(['role:coletor'])->group(function () {
        Route::get('/coletor/coleta', [ConferenciaController::class, 'create'])->name('coletor.coleta');
        Route::get('/coletor/balancas', [ConferenciaController::class, 'getBalancas'])->name('coletor.balancas');
        Route::post('/coletor/coleta/preview', [ConferenciaController::class, 'preview'])->name('coletor.preview');
        Route::post('/coletor/coleta', [ConferenciaController::class, 'store'])->name('coletor.store');
        Route::get('/coletor/coleta/{conferencia}', [ConferenciaController::class, 'show'])->name('coletor.show');
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::resource('lojas', LojaController::class);
        Route::resource('balancas', BalancaController::class)->except(['show']);
        Route::resource('users', UserController::class);

        Route::get('/configuracoes/glpi', [GlpiConfigController::class, 'index'])->name('glpi.config');
        Route::post('/configuracoes/glpi', [GlpiConfigController::class, 'store'])->name('glpi.config.save');
        Route::post('/configuracoes/glpi/testar', [GlpiConfigController::class, 'testar'])->name('glpi.config.test');
        Route::get('/configuracoes/glpi/entidades', [GlpiConfigController::class, 'entidades'])->name('glpi.config.entities');
    });
});