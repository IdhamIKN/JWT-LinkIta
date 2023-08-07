<?php

namespace App\Http\Controllers\Api\LinkIta;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Helpers\Helper;
use Illuminate\Support\Carbon;
use App\Constants\LKMethod;
use App\Constants\LKConstant;
use App\Models\lk_log;
use App\Http\Controllers\Api\LinkIta\GenerateController;
use App\Http\Controllers\Api\LinkIta\ApiDataController;
use App\Http\Controllers\Api\Member\MemberController;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\Utils;
use App\Models\mutasi;

class ProdukController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // INQ
    // INQ PLN Pascabayar
    public function GetPln(Request $request)
    {
        $response = [
            [
                'Kode_Produk' => 'PLNPB',
                'Ket' => 'PLN Pasca Bayar'
            ],
            [
                'Kode_Produk' => 'PLNNONTAG',
                'Ket' => 'PLN Non Tagihan Listrik'
            ],
            [
                'Kode_Produk' => 'PLNPRA',
                'Ket' => 'PLN Prabayar'
            ]
        ];

        return $response;
    }

}
