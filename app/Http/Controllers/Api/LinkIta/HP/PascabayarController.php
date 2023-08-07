<?php

namespace App\Http\Controllers\Api\LinkIta\HP;

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

class PascabayarController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // INQ
    // INQ PLN Pascabayar
    public function Inq(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();
        $time = $Generate->time();
        $ref1 = $Generate->generateRef1();

        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');

        $method = LKMethod::PasInq;

        // Mengambil nilai dari request pengguna
        $nohp = $request->no_hp;
        $kodeProduk = $request->kodeProduk;

        $signature = $Generate->generateSignature($clientKey, $method, $kodeProduk, $time, $nohp, $idMember, $ref1);

        $data = [
            'method' => $method,
            'kode_produk' => $kodeProduk,
            'waktu' => $time,
            'id_member' => $idMember,
            'signature' => $signature,
            'ref1' => $ref1,
            'no_hp' => $nohp
        ];

        $url = env('SANDBOX');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;
        $log = $Generate->createLog($response);

        return $response;
    }

    // INQ Cek PLN Pascabayar
    public function CInq(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();
        $time = $Generate->time();
        $ref1 = $Generate->generateRef1();

        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');

        $method = LKMethod::CPasInq;

        $nohp = $request->no_hp;
        $idtransaksi = $request->idtransaksi;
        $kodeProduk = $request->kodeProduk;

        // Generate Signature
        $signature = $Generate->generateSignature($clientKey, $method, $kodeProduk, $time, $nohp, $idMember, $ref1);

        $data = [
            'method' => $method,
            'id_transaksi_inq' => $idtransaksi,
            'kode_produk' => $kodeProduk,
            'waktu' => $time,
            'id_member' => env('ID_MEM'),
            'signature' => $signature,
            'ref1' => $ref1,
            'no_hp' => $nohp
        ];


        $url = env('SANDBOX');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;
        $log =  $Generate->createLogP($response);
        return $response;
    }

    // Get Check Inq + Check
    public function InqCheck(Request $request)
    {
        $user = auth()->guard('api')->user();
        $MemberId = $user->id;
        $InqResponse = $this->Inq($request);
        $InqContent = $InqResponse;
        $Generate = new GenerateController;
        // Ambil nilai yang diperlukan dari transferInqResponse jika perlu
        $idtransaksi = $InqContent->id_transaksi_inq ?? null; // Menggunakan akses yang sesuai ke properti id_transaksi_inq

        if ($idtransaksi) {
            // Tunda eksekusi selama 3 detik
            sleep(3);
            // Panggil fungsi checkInq dengan idtransaksi yang didapatkan
            $checkInqRequest = $request;
            $checkInqRequest->merge(['idtransaksi' => $idtransaksi]); // Tambahkan idtransaksi ke request checkInq
            $checkInqResponse = $this->CInq($checkInqRequest);
            $checkInqContent = $checkInqResponse;

            // Simpan log jika perlu
            $log = new lk_log;
            $log->id_user = $MemberId;
            $log->customer_id = $checkInqContent->no_hp ?? 0;
            $log->nama = $checkInqContent->nama_pelanggan ?? 0;
            $log->method = $checkInqContent->method ?? 0;
            $log->id_pay = $checkInqContent->id_transaksi_pay ?? 0;
            $log->id_inq = $checkInqContent->id_transaksi_inq ?? 0;
            $log->nominal = isset($checkInqContent->nominal) && is_numeric($checkInqContent->nominal) ? $checkInqContent->nominal : 0;
            $log->status = $checkInqContent->status ?? 0;
            $log->ket =  $checkInqContent->keterangan ?? 0;
            $log->content = json_encode($checkInqResponse);
            $log->save();

            return $checkInqResponse; // Mengembalikan hasil dari checkInq sebagai respons
        } else {
            return response()->json([
                'error' => 'Gagal mendapatkan idtransaksi dari transferInqResponse'
            ], 400);
        }
    }

    // PAY
    //
    public function Pay(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();
        $time = $Generate->time();
        $ref1 = $Generate->generateRef1();
        $user = auth()->guard('api')->user();
        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');
        $MemberId = $user->id;

        $no_hp = $request->no_hp;
        $kodeProduk = $request->kodeProduk;

        $Balance = new ApiDataController;
        $saldo = $Balance->getBalance();
        $member = new MemberController;
        $cash = $Balance->checkBalance();

        $method = LKMethod::PasPay;


        $signature = $Generate->generateSignature($clientKey, $method, $kodeProduk, $time, $no_hp, $idMember, $ref1);

        $data = [
            'method' => $method,
            'kode_produk' => $kodeProduk,
            'waktu' => $time,
            'id_member' => $idMember,
            'signature' => $signature,
            'ref1' => $ref1,
            'no_hp' => $no_hp
        ];

        $url = env('SANDBOX');
        $response = Helper::DataLinkita($url, $data, $token);
// dd($response);
        $content = json_encode($response);
        $log = $Generate->createLog1($content);
        // $allowedStatuses = [1, 2, 6, 9, 13];
        // $balanceResponse = $cash->getData(); // Get the JSON response data from the JsonResponse instance

        // if ($balanceResponse->saldo < 0 || in_array($response->status, $allowedStatuses)) {
        //     $mutasi = $Generate->mutasi($response, $nominal);
        // }

        // if ($saldo->nominal >= $nominal || ($user->nama_user === 'admin' && $cash->saldo_global >= $nominal)) {
        //     return $response;
        // } elseif ($user->nama_user === 'member') {
        //     foreach ($cash->saldo as $saldo) {
        //         if ($saldo->id_user == $MemberId && $saldo->saldo >= $nominal) {
        //             return $response;
        //         }
        //     }
        // }

        sleep(3);

        return $response;
    }

    // Get Check pay PLN Pascabayar
    public function CPay(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();
        $time = $Generate->time();
        $ref1 = $Generate->generateRef1();

        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');

        $method = LKMethod::CPasPay;

        // Mengambil nilai dari request pengguna
        $idtransaksi = $request->idtransaksi;
        $no_hp = $request->no_hp;
        $kodeProduk = $request->kodeProduk;

        // Generate Signature
        $signature = $Generate->generateSignature($clientKey, $method, $kodeProduk, $time, $no_hp, $idMember, $ref1);

        $data = [
            'method'    =>    $method,
            'kode_produk'    =>  $kodeProduk,
            'waktu'    =>    $time,
            'id_transaksi_pay'    =>  $idtransaksi,
            'id_member'    =>    env('ID_MEM'),
            'signature'    =>    $signature,
            'ref1'    =>    $ref1,
            'no_hp' => $no_hp
        ];

        $url = env('SANDBOX');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;
        $log =  $Generate->createLog($response);

        return $response;
    }

    public function CheckPay(Request $request)
    {
        $user = auth()->guard('api')->user();
        $MemberId = $user->id;
        $InqRequest = $this->Inq($request);
        $InqContent = $InqRequest;
        $Generate = new GenerateController;
        // Ambil nilai yang diperlukan dari transferInqResponse jika perlu
        $idtransaksi = $InqContent->id_transaksi_inq ?? null; // Menggunakan akses yang sesuai ke properti id_transaksi_inq

        if ($idtransaksi) {
            // Tunda eksekusi selama 3 detik
            sleep(3);
            // Panggil fungsi checkInq dengan idtransaksi yang didapatkan
            $checkPayRequest = $request;
            $checkPayRequest->merge(['idtransaksi' => $idtransaksi]); // Tambahkan idtransaksi ke request checkInq
            $checkPayResponse = $this->CPay($checkPayRequest);
            $PayResponse = $this->Pay($checkPayRequest);
            $checkPayContent = $checkPayResponse;
            $PayContent = $PayResponse;

            // Simpan log jika perlu
            $log = new lk_log;
            $log->id_user = $MemberId;
            $log->customer_id = $checkPayContent->no_hp ?? 0;
            $log->nama = $checkPayContent->nama_pelanggan ?? 0;
            $log->method = $checkPayContent->method ?? 0;
            $log->id_pay = $PayContent->id_transaksi_pay ?? 0;
            $log->id_inq = $InqContent->id_transaksi_inq ?? 0;
            $log->nominal = isset($checkPayContent->nominal) && is_numeric($checkPayContent->nominal) ? $checkPayContent->nominal : 0;
            $log->status = $checkPayContent->status ?? 0;
            $log->ket =  $checkPayContent->keterangan ?? 0;
            $log->content = json_encode($checkPayResponse);
            $log->save();

            return $checkPayResponse; // Mengembalikan hasil dari checkInq sebagai respons
        } else {
            return response()->json([
                'error' => 'Gagal mendapatkan idtransaksi dari transferInqResponse'
            ], 400);
        }
    }
}
