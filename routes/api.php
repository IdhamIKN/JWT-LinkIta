<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Exceptions\JwtHandlerException;
use App\Http\Controllers\Api\Member\MemberController;
use App\Http\Controllers\Api\User\RegisterController;
use App\Http\Controllers\Api\User\LoginController;
use App\Http\Controllers\Api\User\LogoutController;
use App\Http\Controllers\Api\LinkIta\ApiDataController;
// use App\Http\Controllers\Api\LinkIta\TransferController;
use App\Http\Controllers\Api\LinkIta\Transfer\EmoneyController;
use App\Http\Controllers\Api\LinkIta\Transfer\TransferController;
// use App\Http\Controllers\Api\LinkIta\EmoneyController;
use App\Http\Controllers\Api\LinkIta\Transfer\VaController;

use App\Http\Controllers\Api\LinkIta\ProdukController;
use App\Http\Controllers\Api\LinkIta\PLN\PLNPBController;
use App\Http\Controllers\Api\LinkIta\PLN\PLNPRAController;
use App\Http\Controllers\Api\LinkIta\PLN\PLNNONController;
use App\Http\Controllers\Api\LinkIta\Asuransi\BPJSController;
use App\Http\Controllers\Api\LinkIta\HP\PrabayarController;
use App\Http\Controllers\Api\LinkIta\HP\PascabayarController;
use App\Http\Controllers\Api\LinkIta\PDAM\PDAMController;
use App\Http\Controllers\Api\LinkIta\TV\TVController;

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


    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::prefix('apidata')->group(function () {
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
    });

    Route::prefix('produk')->group(function () {
        // PLN Pascabayar
        Route::post('/pln', [ProdukController::class, 'GetPln']);
    });

    Route::prefix('transfer')->group(function () {
        Route::prefix('bank')->group(function () {
            Route::prefix('inq')->group(function () {
                //Get Transfer Inquiry
                Route::post('/', [TransferController::class, 'Inq']);
                //Get Check Inquiry
                Route::post('/cek', [TransferController::class, 'CInq']);
                //Get Transfer Inquiry and Check Inquiry
                Route::post('/inqc', [TransferController::class, 'CheckInq']);
            });
            Route::prefix('pay')->group(function () {
                //Get Transfer Pay
                Route::post('/', [TransferController::class, 'Pay']);
                //Get Check Pay
                Route::post('/cek', [TransferController::class, 'CPay']);
                //Get Transfer Pay and Check Pay
                Route::post('/payc', [TransferController::class, 'CheckPay']);
            });
        });
        Route::prefix('emoney')->group(function () {
            // Emoney\
            Route::prefix('inq')->group(function () {
                //Get Transfer Inquiry
                Route::post('/', [EmoneyController::class, 'Inq']);
                //Get Check Inquiry
                Route::post('/cek', [EmoneyController::class, 'CInq']);
                //Get Emoney Inquiry and Check Inquiry
                Route::post('/inqc', [EmoneyController::class, 'CheckInq']);
            });
            Route::prefix('pay')->group(function () {
                //Get Transfer Pay
                Route::post('/', [EmoneyController::class, 'Pay']);
                //Get Check Pay
                Route::post('/cek', [EmoneyController::class, 'CPay']);
                //Get Emoney Pay and Check Pay
                Route::post('/payc', [EmoneyController::class, 'CheckPay']);
            });
        });
        Route::prefix('va')->group(function () {
            // Transfer Virtual Account
            Route::prefix('inq')->group(function () {
                //Get VA Inquiry
                Route::post('/', [VaController::class, 'Inq']);
                //Get VA Check
                Route::post('/cek', [VaController::class, 'CInq']);
                //Get VA Inquiry and Check Inquiry
                Route::post('/inqc', [VaController::class, 'CheckInq']);
            });
            Route::prefix('pay')->group(function () {
                //Get VA Pay
                Route::post('/pay', [VaController::class, 'Pay']);
                //Get VA Pay Check
                Route::post('/cek', [VaController::class, 'CPay']);
                //Get VA Pay and Check Pay
                Route::post('/payc', [VaController::class, 'CheckPay']);
            });
        });
    });

    Route::prefix('pln')->group(function () {

        Route::prefix('pb')->group(function () {
            Route::prefix('inq')->group(function () {
                // PLN Pascabayar
                Route::post('/', [PLNPBController::class, 'Inq']);
                Route::post('/cek', [PLNPBController::class, 'CInq']);
                Route::post('/inqc', [PLNPBController::class, 'InqCheck']);
            });
            Route::prefix('pay')->group(function () {
                Route::post('/', [PLNPBController::class, 'Pay']);
                Route::post('/cek', [PLNPBController::class, 'CPay']);
                Route::post('/payc', [PLNPBController::class, 'CheckPay']);
            });
        });

        Route::prefix('pra')->group(function () {
            Route::prefix('inq')->group(function () {
                // PLN Prabayar
                Route::post('/', [PLNPRAController::class, 'Inq']);
                Route::post('/cek', [PLNPRAController::class, 'CInq']);
                Route::post('/inqc', [PLNPRAController::class, 'InqCheck']);
            });
            Route::prefix('pay')->group(function () {
                Route::post('/', [PLNPRAController::class, 'Pay']);
                Route::post('/cek', [PLNPRAController::class, 'CPay']);
                Route::post('/payc', [PLNPRAController::class, 'CheckPay']);
            });
        });

        Route::prefix('non')->group(function () {
            Route::prefix('inq')->group(function () {
                // PLN Prabayar
                Route::post('/', [PLNNONController::class, 'Inq']);
                Route::post('/cek', [PLNNONController::class, 'CInq']);
                Route::post('/inqc', [PLNNONController::class, 'InqCheck']);
            });
            Route::prefix('pay')->group(function () {
                Route::post('/', [PLNNONController::class, 'Pay']);
                Route::post('/cek', [PLNNONController::class, 'CPay']);
                Route::post('/payc', [PLNNONController::class, 'CheckPay']);
            });
        });
    });

    Route::prefix('asuransi')->group(function () {
        Route::prefix('bpjs')->group(function () {
            Route::prefix('inq')->group(function () {
                // PLN Prabayar
                Route::post('/', [BPJSController::class, 'Inq']);
                Route::post('/cek', [BPJSController::class, 'CInq']);
                Route::post('/inqc', [BPJSController::class, 'InqCheck']);
            });
            Route::prefix('pay')->group(function () {
                Route::post('/', [BPJSController::class, 'Pay']);
                Route::post('/cek', [BPJSController::class, 'CPay']);
                Route::post('/payc', [BPJSController::class, 'CheckPay']);
            });
        });
    });

    Route::prefix('hp')->group(function () {
        Route::prefix('pra')->group(function () {
            Route::post('/', [PrabayarController::class, 'Pay']);
            Route::post('/cek', [PrabayarController::class, 'CPay']);
            Route::post('/payc', [PrabayarController::class, 'CheckPay']);
        });
        Route::prefix('pas')->group(function () {
            Route::prefix('inq')->group(function () {
                Route::post('/', [PascabayarController::class, 'Inq']);
                Route::post('/cek', [PascabayarController::class, 'CInq']);
                Route::post('/inqc', [PascabayarController::class, 'InqCheck']);
            });

            Route::prefix('pay')->group(function () {
                Route::post('/', [PascabayarController::class, 'Pay']);
                Route::post('/cek', [PascabayarController::class, 'CPay']);
                Route::post('/payc', [PascabayarController::class, 'CheckPay']);
            });
        });
    });

    Route::prefix('member')->group(function () {
        // MEMBER
        // TopUp
        Route::post('/topup', [MemberController::class, 'topUp'])->name('topUp');
        // Verifikasi TopUp
        Route::post('/verify', [MemberController::class, 'verifytopUp'])->name('verifytopUp');
        // Verifikasi TopUp
        Route::post('/cekbalance', [MemberController::class, 'checkBalance'])->name('checkBalance');
    });

    Route::prefix('pdam')->group(function () {
        Route::prefix('inq')->group(function () {
            Route::post('/', [PDAMController::class, 'Inq']);
            Route::post('/cek', [PDAMController::class, 'CInq']);
            Route::post('/inqc', [PDAMController::class, 'InqCheck']);
        });
        Route::prefix('pay')->group(function () {
            Route::post('/', [PDAMController::class, 'Pay']);
            Route::post('/cek', [PDAMController::class, 'CPay']);
            Route::post('/payc', [PDAMController::class, 'CheckPay']);
        });
    });

    Route::prefix('tv')->group(function () {
        Route::prefix('inq')->group(function () {
            Route::post('/', [TVController::class, 'Inq']);
            Route::post('/cek', [TVController::class, 'CInq']);
            Route::post('/inqc', [TVController::class, 'InqCheck']);
        });
        Route::prefix('pay')->group(function () {
            Route::post('/', [TVController::class, 'Pay']);
            Route::post('/cek', [TVController::class, 'CPay']);
            Route::post('/payc', [TVController::class, 'CheckPay']);
        });
    });

    Route::prefix('telkom')->group(function () {
        Route::prefix('inq')->group(function () {
            Route::post('/', [TVController::class, 'Inq']);
            Route::post('/cek', [TVController::class, 'CInq']);
            Route::post('/inqc', [TVController::class, 'InqCheck']);
        });
        Route::prefix('pay')->group(function () {
            Route::post('/', [TVController::class, 'Pay']);
            Route::post('/cek', [TVController::class, 'CPay']);
            Route::post('/payc', [TVController::class, 'CheckPay']);
        });
    });
    
});


Route::post('/logout', LogoutController::class)->name('logout');
