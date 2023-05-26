<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Exceptions\JwtHandlerException;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * route "/register"
 * @method "POST"
 */
Route::post('/register', App\Http\Controllers\Api\User\RegisterController::class)->name('register');

/**
 * route "/login"
 * @method "POST"
 */
Route::post('/login', App\Http\Controllers\Api\User\LoginController::class)->name('login');

// Middleware group for routes that require authentication
Route::middleware(['auth:api', 'auth.api'])->group(function () {
    /**
     * route "/user"
     * @method "GET"
     */
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    //Get Saldo Transaksi
    Route::post('/balance', [App\Http\Controllers\Api\LinkIta\ApiDataController::class, 'getBalance']);
    // Get Data Bank
    Route::post('/bank', [App\Http\Controllers\Api\LinkIta\ApiDataController::class, 'getBank']);
    //Cetak Struk
    Route::post('/struk', [App\Http\Controllers\Api\LinkIta\ApiDataController::class, 'getStruk']);
    //Get Url Widget
    Route::post('/widget', [App\Http\Controllers\Api\LinkIta\ApiDataController::class, 'getUrlWidget']);
    //Get Transfer Inquiry
    Route::post('/tfinq', [App\Http\Controllers\Api\LinkIta\TransferController::class, 'transferInq']);
    //Get Transfer Pay
    Route::post('/tfpay', [App\Http\Controllers\Api\LinkIta\TransferController::class, 'transferPay']);
    //Get Check Inquiry
    Route::post('/checkinq', [App\Http\Controllers\Api\LinkIta\TransferController::class, 'checkInq']);
    //Get Check Pay
    Route::post('/checkpay', [App\Http\Controllers\Api\LinkIta\TransferController::class, 'checkPay']);

    Route::post('/inqtransfercheck', [App\Http\Controllers\Api\LinkIta\TransferController::class, 'transferInqAndCheckInq']);
    Route::post('/paytransfercheck', [App\Http\Controllers\Api\LinkIta\TransferController::class, 'transferPayAndCheckPay']);

    // Emoney
    //Get Transfer Inquiry
    Route::post('/moneyinq', [App\Http\Controllers\Api\LinkIta\EmoneyController::class, 'inqEmoney']);
    //Get Transfer Pay
    Route::post('/moneypay', [App\Http\Controllers\Api\LinkIta\EmoneyController::class, 'payEmoney']);
    //Get Check Inquiry
    Route::post('/inqmoney', [App\Http\Controllers\Api\LinkIta\EmoneyController::class, 'emoneyInq']);
    //Get Check Pay
    Route::post('/payemoney', [App\Http\Controllers\Api\LinkIta\EmoneyController::class, 'emoneyPay']);

    Route::post('/inqemoneycheck', [App\Http\Controllers\Api\LinkIta\EmoneyController::class, 'emoneyInqAndCheckInq']);
    Route::post('/payemoneycheck', [App\Http\Controllers\Api\LinkIta\EmoneyController::class, 'emoneyPayAndCheckPay']);
});



/**
 * route "/logout"
 * @method "POST"
 */
Route::post('/logout', App\Http\Controllers\Api\User\LogoutController::class)->name('logout');
