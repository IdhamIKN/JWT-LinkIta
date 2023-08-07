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

class VaController extends Controller
{
    // Virtual Account Inq
    // public function Inq(Request $request)
    // {
    //     $Generate = new GenerateController;
    //     $token = $Generate->getJwtToken();
    //     $time = $Generate->time();
    //     $ref1 = $Generate->generateRef1();

    //     $clientKey = env('CLIENT_KEY');
    //     $idMember = env('ID_MEM');

    //     $method = LKMethod::InqBank;
    //     $kodeProduk = LKConstant::VABank;

    //     // Mengambil nilai dari request pengguna
    //     $idPelanggan = $request->id_pelanggan;
    //     $nominal = $request->nominal;

    //     $kodeProduk .= substr($idPelanggan, 0, 3);
    //     $signature = $Generate->generateSignature($clientKey, $method, $kodeProduk, $time, $idPelanggan, $idMember, $ref1);

    //     $data = [
    //         'method' => $method,
    //         'kode_produk' => $kodeProduk,
    //         'waktu' => $time,
    //         'id_pelanggan' => $idPelanggan,
    //         'nominal' => $nominal,
    //         'id_member' => $idMember,
    //         'signature' => $signature,
    //         'ref1' => $ref1
    //     ];

    //     $url = env('LINKITA');
    //     $response = Helper::DataLinkita($url, $data, $token);

    //     // Simpan Log
    //     $content = $response;
    //     $log =  $Generate->createLog($response);

    //     return $response;
    // }
    public function Inq(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();
        $time = $Generate->time();
        $ref1 = $Generate->generateRef1();

        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');

        $method = LKMethod::InqBank;
        $kodeProduk = LKConstant::VABank;

        // Mengambil nilai dari request pengguna
        $idPelanggan = $request->id_pelanggan;
        $nominal = $request->nominal;

        $kodeProduk .= substr($idPelanggan, 0, 3);
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
// dd($data);
        // $url = env('LINKITA');
        $url = 'https://api1.linkita.id';
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;
        $log =  $Generate->createLog($response);

        return $response;
    }

    // Virtual Account Check
    // Virtual Account Check
    public function CInq(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();
        $time = $Generate->time();
        $ref1 = $Generate->generateRef1();

        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');

        $method = LKMethod::InqCheck;
        $kodeProduk = LKConstant::VABank;

        // Mengambil nilai dari request pengguna
        $idPelanggan = $request->id_pelanggan;
        $nominal = $request->nominal;
        $idtransaksi = $request->idtransaksi;

        $kodeProduk .= substr($idPelanggan, 0, 3);
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

        // $url = env('LINKITA');
        $url = 'https://api1.linkita.id';
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;
        $log =  $Generate->createLog($response);

        return $response;
    }

    // Virtual Account Inq + Check
    public function CheckInq(Request $request)
    {
        $user = auth()->guard('api')->user();
        $MemberId = $user->id;
        $vaInqResponse = $this->Inq($request);
        $vaInqContent = $vaInqResponse;
        $Generate = new GenerateController;
        // Ambil nilai
        $idtransaksi = $vaInqContent->id_transaksi_inq ?? null;

        if ($idtransaksi) {
            // Tunda eksekusi selama 3 detik
            sleep(3);
            // Panggil fungsi checkInq dengan idtransaksi yang didapatkan
            $checkvaInqRequest = $request;
            $checkvaInqRequest->merge(['idtransaksi' => $idtransaksi]);
            $checkvaInqResponse = $this->CInq($checkvaInqRequest);
            $checkvaInqContent = $checkvaInqResponse;

            // Simpan log jika perlu
            $log = new lk_log;
            $log->id_user = $MemberId;
            $log->customer_id = $checkvaInqContent->id_pelanggan ?? 0;
            $log->nama = $checkvaInqContent->nama_pelanggan ?? 0;
            $log->method = $checkvaInqContent->nama_pelanggan ?? 0;
            $log->id_pay = $checkvaInqContent->id_transaksi_pay ?? 0;
            $log->id_inq = $checkvaInqContent->id_transaksi_inq ?? 0;
            $log->nominal = $checkvaInqContent->nominal ?? 0;
            $log->status = $checkvaInqContent->status ?? 0;
            $log->ket =  $checkvaInqContent->keterangan ?? 0;
            $log->content = json_encode($checkvaInqResponse);
            $log->save();

            return $checkvaInqResponse; // Mengembalikan hasil dari checkInq sebagai respons
        } else {
            return response()->json([
                'error' => 'Gagal mendapatkan idtransaksi dari transferInqResponse'
            ], 400);
        }
    }


    public function Pay(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();
        $time = $Generate->time();
        $ref1 = $Generate->generateRef1();
        $user = auth()->guard('api')->user();

        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');

        $method = LKMethod::PayBank;
        $kodeProduk = LKConstant::VABank;

        // Mengambil nilai dari request pengguna
        $idPelanggan = $request->id_pelanggan;
        $nominal = $request->nominal;
        $idtransaksi = $request->idtransaksi;

        // Validasi saldo user
        $Balance = new ApiDataController;
        $saldo = $Balance->getBalance();
        $member = new MemberController;
        $cash = $Balance->checkBalance();
        $kodeProduk .= substr($idPelanggan, 0, 3);
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

        $url = env('LINKITA');
        $response = Helper::DataLinkita($url, $data, $token);
        $content = json_encode($response);
        $log = $Generate->createLog1($content);
        $mutasi = $Generate->mutasi($response, $nominal);

        if ($saldo->nominal >= $nominal) {
            return $response;
        } elseif ($user->nama_user === 'admin' && $cash->saldo_global >= $nominal) {
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
        $logController->createLog1($logContent, 'tf_pay');
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
        $kodeProduk = LKConstant::VABank;

        // Mengambil nilai dari request pengguna
        $idPelanggan = $request->id_pelanggan;
        $nominal = $request->nominal;
        $idtransaksi = $request->idtransaksi;

        // Generate Signature
        $kodeProduk .= substr($idPelanggan, 0, 3);
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
        $log = $this->createLog($response);

        return $response;
    }

    // Get Inq + Check pay
    public function CheckPay(Request $request)
    {
        $transfervaPayResponse = $this->Inq($request);
        $transfervaPayContent = $transfervaPayResponse;
        $Generate = new GenerateController;
        // Ambil nilai yang diperlukan dari transferInqResponse jika perlu
        $idtransaksi = $transfervaPayContent->id_transaksi_inq ?? null; // Menggunakan akses yang sesuai ke properti id_transaksi_inq

        if ($idtransaksi) {
            // Tunda eksekusi selama 3 detik
            sleep(3);
            // Panggil fungsi checkInq dengan idtransaksi yang didapatkan
            $checkvaPayRequest = $request;
            $checkvaPayRequest->merge(['idtransaksi' => $idtransaksi]); // Tambahkan idtransaksi ke request checkInq
            $checkvaPayResponse = $this->CPay($checkvaPayRequest);
            $checkvaPayContent = $checkvaPayResponse;

            // Simpan log jika perlu
            $log = new lk_log;
            $log->id_user = $MemberId;
            $log->customer_id = $checkvaPayContent->id_pelanggan ?? 0;
            $log->nama = $checkvaPayContent->nama_pelanggan ?? 0;
            $log->method = $checkvaPayContent->method ?? 0;
            $log->id_pay = $checkvaPayContent->id_transaksi_pay ?? 0;
            $log->id_inq = $checkvaPayContent->id_transaksi_inq ?? 0;
            $log->nominal = $checkvaPayContent->nominal ?? 0;
            $log->status = $checkvaPayContent->status ?? 0;
            $log->ket =  $checkvaPayContent->keterangan ?? 0;
            $log->content = json_encode($checkvaPayResponse);
            $log->save();

            return $checkvaPayResponse; // Mengembalikan hasil dari checkInq sebagai respons
        } else {
            return response()->json([
                'error' => 'Gagal mendapatkan idtransaksi dari transferInqResponse'
            ], 400);
        }
    }
}
