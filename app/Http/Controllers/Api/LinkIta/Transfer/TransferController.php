<?php

namespace App\Http\Controllers\Api\LinkIta\Transfer;

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

class TransferController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // INQ
    // Get Transfer Inquiry
    public function Inq(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();
        $time = $Generate->time();
        $ref1 = $Generate->generateRef1();

        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');

        $method = LKMethod::InqBank;
        $kodeProduk = LKConstant::TFBank;

        // Mengambil nilai dari request pengguna
        $idPelanggan = $request->id_pelanggan;
        $nominal = $request->nominal;

        $signature = $Generate->generateSignature($clientKey, $method, $kodeProduk, $time, $idPelanggan, $idMember, $ref1);

        $data = [
            'method' => $method,
            'kode_produk' => $kodeProduk,
            'waktu' => $time,
            'id_pelanggan' => $idPelanggan,
            'nominal' => $nominal,
            'id_member' => $idMember,
            'signature' => $signature,
            'ref1' => $ref1
        ];

        // $url = env('LINKITA');
        $url = env('SANDBOX');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;
        $log = $Generate->createLog($response);

        return $response;
    }

    // Get Check Inq
    public function CInq(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();
        $time = $Generate->time();
        $ref1 = $Generate->generateRef1();

        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');

        $method = LKMethod::InqCheck;
        $kodeProduk = LKConstant::TFBank;

        // Mengambil nilai dari request pengguna
        $idPelanggan = $request->id_pelanggan;
        $nominal = $request->nominal;
        $idtransaksi = $request->idtransaksi;

        // Generate Signature
        $signature = $Generate->generateSignature($clientKey, $method, $kodeProduk, $time, $idPelanggan, $idMember, $ref1);

        $data = [
            'method' => $method,
            'id_transaksi_inq' => $idtransaksi,
            'kode_produk' => $kodeProduk,
            'waktu' => $time,
            'id_pelanggan' => $idPelanggan,
            'id_member' => env('ID_MEM'),
            'signature' => $signature,
            'ref1' => $ref1
        ];


        $url = env('LINKITA');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;
        $log =  $Generate->createLog($response);
        return $response;
    }

    // Get Check Inq + Check
    public function CheckInq(Request $request)
    {

        $user = auth()->guard('api')->user();
        $MemberId = $user->id;
        // dd($MemberId);
        $transferInqResponse = $this->Inq($request);
        $transferInqContent = $transferInqResponse;
        $Generate = new GenerateController;
        // Ambil nilai yang diperlukan dari transferInqResponse jika perlu
        $idtransaksi = $transferInqContent->id_transaksi_inq ?? null; // Menggunakan akses yang sesuai ke properti id_transaksi_inq

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
            $log->customer_id = $checkInqContent->id_pelanggan ?? 0;
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
    // Pay Transfer
    // public function transferPay(Request $request)
    // {
    //     $user = auth()->guard('api')->user();
    //     $MemberId = $user->id;

    //     $Balance = new ApiDataController;
    //     $member = new MemberController;
    //     $Generate = new GenerateController;
    //     $token = $Generate->getJwtToken();
    //     $time = $Generate->time();
    //     $ref1 = $Generate->generateRef1();
    //     // Notif Saldo GLobal
    //     $saldo = $Balance->getBalance();

    //     $clientKey = env('CLIENT_KEY');
    //     $idMember = env('ID_MEM');

    //     $method = LKMethod::PayBank;
    //     $kodeProduk = LKConstant::TFBank;

    //     $idPelanggan = $request->id_pelanggan;
    //     $nominal = $request->nominal;
    //     $idtransaksi = $request->idtransaksi;

    //     // Cek saldo user
    //     $cash = $member->checkBalance();

    //     $signature = $Generate->generateSignature($clientKey, $method, $kodeProduk, $time, $idPelanggan, $idMember, $ref1);

    //     $data = [
    //         'method' => $method,
    //         'kode_produk' => $kodeProduk,
    //         'waktu' => $time,
    //         'id_transaksi_inq' => $idtransaksi,
    //         'id_pelanggan' => $idPelanggan,
    //         'id_member' => $idMember,
    //         'nominal' => $nominal,
    //         'signature' => $signature,
    //         'ref1' => $ref1
    //     ];

    //     // $url = env('LINKITA');
    //     $url = env('SANDBOX');
    //     $response = Helper::DataLinkita($url, $data, $token);

    //     $content = json_encode($response);
    //     $log = $Generate->createLog1($content, 'tf_pay');
    //     $allowedStatuses = [1, 2, 6, 9, 13];
    //     $balanceResponse = $cash->getData(); // Get the JSON response data from the JsonResponse instance

    //     if ($balanceResponse->saldo < 0) {
    //         $response = ['message' => 'Saldo Tidak Mencukupi.'];
    //     } elseif (in_array($response->status, $allowedStatuses)) {
    //         $mutasi = $Generate->mutasi($response, $nominal);
    //     }
    //     // $mutasi = $Generate->mutasi($response, $nominal);

    //     if ($saldo->nominal >= $nominal) {
    //         return $response;
    //     } elseif ($user->nama_user === 'admin' && $cash->saldo_global >= $nominal) {
    //         return $response;
    //     } elseif ($user->nama_user === 'member') {
    //         foreach ($cash->saldo as $saldo) {
    //             if ($saldo->id_user == $MemberId && $saldo->saldo >= $nominal) {
    //                 return $response;
    //             }
    //         }
    //     }

    //     sleep(3);
    //     $response = $Generate->fail($idtransaksi, $nominal, $idPelanggan, $idMember, $ref1);
    //     $logContent = json_encode($response);
    //     $logController = new GenerateController;
    //     $logController->createLog1($logContent, 'tf_pay');
    //     return $response;
    // }
    public function Pay(Request $request)
    {
        $user = auth()->guard('api')->user();
        $MemberId = $user->id;

        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();
        $time = $Generate->time();
        $ref1 = $Generate->generateRef1();
        // Notif Saldo GLobal
        $saldo = (new ApiDataController)->getBalance();

        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');

        $method = LKMethod::PayBank;


        $idPelanggan = $request->id_pelanggan;
        $nominal = $request->nominal;
        $idtransaksi = $request->idtransaksi;
        $kodeProduk = $request->kodeProduk;

        // Cek saldo user
        $cash = (new MemberController)->checkBalance();

        $signature = $Generate->generateSignature($clientKey, $method, $kodeProduk, $time, $idPelanggan, $idMember, $ref1);

        $data = [
            'method' => $method,
            'kode_produk' => $kodeProduk,
            'waktu' => $time,
            'id_transaksi_inq' => $idtransaksi,
            'id_pelanggan' => $idPelanggan,
            'id_member' => $idMember,
            'nominal' => $nominal,
            'signature' => $signature,
            'ref1' => $ref1
        ];

        // $url = env('LINKITA');
        $url = env('SANDBOX');
        $response = Helper::DataLinkita($url, $data, $token);

        $content = json_encode($response);
        $log = $Generate->createLog1($content);
        $allowedStatuses = [1, 2, 6, 9, 13];
        $balanceResponse = $cash->getData(); // Get the JSON response data from the JsonResponse instance

        if ($balanceResponse->saldo < 0 || in_array($response->status, $allowedStatuses)) {
            $mutasi = $Generate->mutasi($response, $nominal);
        }

        if ($saldo->nominal >= $nominal || ($user->nama_user === 'admin' && $cash->saldo_global >= $nominal)) {
            return $response;
        } elseif ($user->nama_user === 'member') {
            foreach ($cash->saldo as $saldo) {
                if ($saldo->id_user == $MemberId && $saldo->saldo >= $nominal) {
                    return $response;
                }
            }
        }

        sleep(3);
        $response = $Generate->fail($idtransaksi, $nominal, $idPelanggan, $idMember, $ref1);
        $logContent = json_encode($response);
        $logController = new GenerateController;
        $logController->createLog1($logContent);
        return $response;
    }


    // Get Check pay
    public function CPay(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();
        $time = $Generate->time();
        $ref1 = $Generate->generateRef1();

        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');

        $method = LKMethod::PayCheck;
        $kodeProduk = LKConstant::TFBank;

        // Mengambil nilai dari request pengguna
        $idPelanggan = $request->id_pelanggan;
        $nominal = $request->nominal;
        $idtransaksi = $request->idtransaksi;

        // Generate Signature
        $signature = $Generate->generateSignature($clientKey, $method, $kodeProduk, $time, $idPelanggan, $idMember, $ref1);

        $data = [
            'method'    =>    $method,
            'kode_produk'    =>  $kodeProduk,
            'waktu'    =>    $time,
            'id_transaksi_pay'    =>  $idtransaksi,
            'id_pelanggan'    =>    $idPelanggan,
            'id_member'    =>    env('ID_MEM'),
            'nominal'    =>    '',
            'signature'    =>    $signature,
            'ref1'    =>    $ref1
        ];
        // dd($data);

        $url = env('LINKITA');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;
        $log =  $Generate->createLog($response);

        return $response;
    }

    // Get Pay + Check pay
    public function CheckPay(Request $request)
    {
        $user = auth()->guard('api')->user();
        $MemberId = $user->id;
        $transferPayResponse = $this->Inq($request);
        $transferPayContent = $transferPayResponse;
        $Generate = new GenerateController;
        // Ambil nilai yang diperlukan dari transferInqResponse jika perlu
        $idtransaksi = $transferPayContent->id_transaksi_inq ?? null; // Menggunakan akses yang sesuai ke properti id_transaksi_inq

        if ($idtransaksi) {
            // Tunda eksekusi selama 3 detik
            sleep(3);
            // Panggil fungsi checkInq dengan idtransaksi yang didapatkan
            $checkPayRequest = $request;
            $checkPayRequest->merge(['idtransaksi' => $idtransaksi]); // Tambahkan idtransaksi ke request checkInq
            $checkPayResponse = $this->CPay($checkPayRequest);
            $checkPayContent = $checkPayResponse;

            // Simpan log jika perlu
            $log = new lk_log;
            $log->id_user = $MemberId;
            $log->customer_id = $checkPayContent->id_pelanggan ?? 0;
            $log->nama = $checkPayContent->nama_pelanggan ?? 0;
            $log->method = $checkPayContent->method ?? 0;
            $log->id_pay = $checkPayContent->id_transaksi_pay ?? 0;
            $log->id_inq = $checkPayContent->id_transaksi_inq ?? 0;
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
