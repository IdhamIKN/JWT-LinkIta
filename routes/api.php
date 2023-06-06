<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Exceptions\JwtHandlerException;
use App\Http\Controllers\Api\Member\MemberController;
use App\Http\Controllers\Api\User\RegisterController;
use App\Http\Controllers\Api\User\LoginController;
use App\Http\Controllers\Api\User\LogoutController;
use App\Http\Controllers\Api\LinkIta\ApiDataController;
use App\Http\Controllers\Api\LinkIta\TransferController;
use App\Http\Controllers\Api\LinkIta\EmoneyController;

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
Route::post('/register', RegisterController::class)->name('register');

/**
 * route "/login"
 * @method "POST"
 */
Route::post('/login', LoginController::class)->name('login');

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
    Route::post('/balance', [ApiDataController::class, 'getBalance']);
    // Get Data Bank
    Route::post('/bank', [ApiDataController::class, 'getBank']);
    //Cetak Struk
    Route::post('/struk', [ApiDataController::class, 'getStruk']);
    //Get Url Widget
    Route::post('/widget', [ApiDataController::class, 'getUrlWidget']);
    //Get Transaksi
    Route::post('/trans', [ApiDataController::class, 'transaksi']);
    //Get Transaksi
    Route::post('/mutasi', [ApiDataController::class, 'mutasi']);


    //Get Transfer Inquiry
    Route::post('/tfinq', [TransferController::class, 'transferInq']);
    //Get Transfer Pay
    Route::post('/tfpay', [TransferController::class, 'transferPay']);
    //Get Check Inquiry
    Route::post('/checkinq', [TransferController::class, 'checkInq']);
    //Get Check Pay
    Route::post('/checkpay', [TransferController::class, 'checkPay']);
    //Get Transfer Inquiry and Check Inquiry
    Route::post('/inqtransfercheck', [TransferController::class, 'transferInqAndCheckInq']);
    //Get Transfer Pay and Check Pay
    Route::post('/paytransfercheck', [TransferController::class, 'transferPayAndCheckPay']);
    // Emoney
    //Get Transfer Inquiry
    Route::post('/moneyinq', [EmoneyController::class, 'inqEmoney']);
    //Get Transfer Pay
    Route::post('/moneypay', [EmoneyController::class, 'payEmoney']);
    //Get Check Inquiry
    Route::post('/inqmoney', [EmoneyController::class, 'emoneyInq']);
    //Get Check Pay
    Route::post('/payemoney', [EmoneyController::class, 'emoneyPay']);
    //Get Emoney Inquiry and Check Inquiry
    Route::post('/inqemoneycheck', [EmoneyController::class, 'emoneyInqAndCheckInq']);
    //Get Emoney Pay and Check Pay
    Route::post('/payemoneycheck', [EmoneyController::class, 'emoneyPayAndCheckPay']);
    //Get VA Inquiry
    Route::post('/vainq', [TransferController::class, 'vaInq']);
    //Get VA Check
    Route::post('/vacheck', [TransferController::class, 'vaCheck']);
    //Get VA Inquiry and Check Inquiry
    Route::post('/inqvacheck', [TransferController::class, 'vaInqAndCheckInq']);
    //Get VA Pay
    Route::post('/vapay', [TransferController::class, 'vaPay']);
    //Get VA Pay Check
    Route::post('/checkva', [TransferController::class, 'vacheckPay']);
    //Get VA Pay and Check Pay
    Route::post('/payvacheck', [TransferController::class, 'vaPayAndCheckPay']);
    // MEMBER
    // TopUp
    Route::post('/topup', [MemberController::class, 'topUp'])->name('topUp');
    // Verifikasi TopUp
    Route::post('/verify', [MemberController::class, 'verifytopUp'])->name('verifytopUp');
    // Verifikasi TopUp
    Route::post('/cekbalance', [MemberController::class, 'checkBalance'])->name('checkBalance');
});

/**
 * route "/logout"
 * @method "POST"
 */
Route::post('/logout', LogoutController::class)->name('logout');
