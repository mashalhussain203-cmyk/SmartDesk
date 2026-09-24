<?php

use App\Http\Controllers\PasskeyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest', 'throttle:20,1'])->prefix('passkeys/login')->group(function (): void {
    Route::post('/options', [PasskeyController::class, 'loginOptions'])->name('passkeys.login.options');
    Route::post('/verify', [PasskeyController::class, 'loginVerify'])->name('passkeys.login.verify');
});

Route::middleware(['auth', 'throttle:20,1'])->prefix('account/passkeys')->group(function (): void {
    Route::get('/', [PasskeyController::class, 'index'])->name('passkeys.index');
    Route::post('/options', [PasskeyController::class, 'registerOptions'])->name('passkeys.register.options');
    Route::post('/verify', [PasskeyController::class, 'registerVerify'])->name('passkeys.register.verify');
    Route::delete('/{passkey}', [PasskeyController::class, 'destroy'])->whereNumber('passkey')->name('passkeys.destroy');
});
