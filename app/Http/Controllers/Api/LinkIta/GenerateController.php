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



class GenerateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // Ambil Jwt Token User
    public function getJwtToken()
    {
        $user = auth()->guard('api')->user();

        if ($user) {
            return $user->jwt;
        } else {
            // throw an exception or return an error response
            throw new Exception('Gagal');
        }
    }

    // Ambil Waktu
    public function time()
    {
        $time = Carbon::now()->toDateTimeString();
        return $time;
    }

    // Generate Signature
    public function generateSignature($clientKey, $method, $kodeProduk, $waktu, $idPelanggan, $idMember, $ref1)
    {
        $stringToHash = $clientKey . "|" . $method . "|" . $kodeProduk . "|" . $waktu . "|" . $idPelanggan . "|" . $idMember . "|" . $ref1;
        $signature = md5($stringToHash);

        return $signature;
    }

    // Generate Reff
    public function generateRef1()
    {
        $dateTime = new \DateTime();
        $currentDateTime = $dateTime->format('mdHis');
        $randomNumber = mt_rand(100, 999);

        $ref1 = $currentDateTime . $randomNumber;

        return $ref1;
    }


    ////////////////////////////////

}
