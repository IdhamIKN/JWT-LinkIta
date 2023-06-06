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

class TransferController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // INQ
    // Get Transfer Inquiry
    public function transferInq(Request $request)
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

        $url = env('LINKITA');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;
        $log = $Generate->createLog($response, 'tf_inq');

        return $response;
    }

    // Get Check Inq
    public function checkInq(Request $request)
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
        $log =  $Generate->createLog($response, 'cek_inq');
        return $response;
    }

    // Get Check Inq + Check
    public function transferInqAndCheckInq(Request $request)
    {
        $transferInqResponse = $this->transferInq($request);
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
            $checkInqResponse = $this->checkInq($checkInqRequest);
            $checkInqContent = $checkInqResponse;

            // Simpan log jika perlu
            $log = new lk_log;
            $log->customer_id = $checkInqContent->id_pelanggan ?? 0;
            $log->nama = $checkInqContent->nama_pelanggan ?? 0;
            $log->method = 'tfcek_inq';
            $log->id_pay = $checkInqContent->id_transaksi_pay ?? 0;
            $log->id_inq = $checkInqContent->id_transaksi_inq ?? 0;
            $log->nominal = $checkInqContent->nominal ?? 0;
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
    public function transferPay(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();
        $time = $Generate->time();
        $ref1 = $Generate->generateRef1();
        $user = auth()->guard('api')->user();
        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');
        $MemberId = $user->id;
        $method = LKMethod::PayBank;
        $kodeProduk = LKConstant::TFBank;
        $idPelanggan = $request->id_pelanggan;
        $nominal = $request->nominal;
        $idtransaksi = $request->idtransaksi;
        $Balance = new ApiDataController;
        $saldo = $Balance->getBalance();
        $member = new MemberController;
        $cash = $Balance->checkBalance();
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
        $log = $Generate->createLog1($content, 'tf_pay');
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
    public function checkPay(Request $request)
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
        $log =  $Generate->createLog($response, 'check_pay');

        return $response;
    }

    // Get Pay + Check pay
    public function transferPayAndCheckPay(Request $request)
    {
        $transferPayResponse = $this->transferInq($request);
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
            $checkPayResponse = $this->checkPay($checkPayRequest);
            $checkPayContent = $checkPayResponse;

            // Simpan log jika perlu
            $log = new lk_log;
            $log->customer_id = $checkPayContent->id_pelanggan ?? 0;
            $log->nama = $checkPayContent->nama_pelanggan ?? 0;
            $log->method = 'tfcek_pay';
            $log->id_pay = $checkPayContent->id_transaksi_pay ?? 0;
            $log->id_inq = $checkPayContent->id_transaksi_inq ?? 0;
            $log->nominal = $checkPayContent->nominal ?? 0;
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


    // Virtual Account Inq
    public function vaInq(Request $request)
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

        $signature = $Generate->generateSignature($clientKey, $method, $kodeProduk, $time, $idPelanggan, $idMember, $ref1);
        $kodeProduk .= substr($idPelanggan, 0, 3);
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

        $url = env('LINKITA');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;
        $log =  $Generate->createLog($response, 'va_inq');

        return $response;
    }

    // Virtual Account Check
    public function vaCheck(Request $request)
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

        // Generate Signature
        $signature = $Generate->generateSignature($clientKey, $method, $kodeProduk, $time, $idPelanggan, $idMember, $ref1);
        $kodeProduk .= substr($idPelanggan, 0, 3);
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
        $log =  $Generate->createLog($response, 'va_check');

        return $response;
    }

    // Virtual Account Inq + Check
    public function vaInqAndCheckInq(Request $request)
    {
        $vaInqResponse = $this->vaInq($request);
        $vaInqContent = $vaInqResponse;
        $Generate = new GenerateController;
        // Ambil nilai yang diperlukan dari transferInqResponse jika perlu
        $idtransaksi = $vaInqContent->id_transaksi_inq ?? null; // Menggunakan akses yang sesuai ke properti id_transaksi_inq

        if ($idtransaksi) {
            // Tunda eksekusi selama 3 detik
            sleep(3);
            // Panggil fungsi checkInq dengan idtransaksi yang didapatkan
            $checkvaInqRequest = $request;
            $checkvaInqRequest->merge(['idtransaksi' => $idtransaksi]); // Tambahkan idtransaksi ke request checkInq
            $checkvaInqResponse = $this->vaCheck($checkvaInqRequest);
            $checkvaInqContent = $checkvaInqResponse;

            // Simpan log jika perlu
            $log = new lk_log;
            $log->customer_id = $checkvaInqContent->id_pelanggan ?? 0;
            $log->nama = $checkvaInqContent->nama_pelanggan ?? 0;
            $log->method = 'vacek_inq';
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


    // Get Transfer pay
    public function vaPay(Request $request)
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

        // Generate Signature
        $signature = $Generate->generateSignature($clientKey, $method, $kodeProduk, $time, $idPelanggan, $idMember, $ref1);
        $kodeProduk .= substr($idPelanggan, 0, 3);
        $data = [
            'method'    =>    $method,
            'kode_produk'    =>    $kodeProduk,
            'waktu'    =>    $time,
            'id_transaksi_inq'    =>    $idtransaksi,
            'id_pelanggan'    =>    $idPelanggan,
            'id_member'    => env('ID_MEM'),
            'nominal'    =>    $nominal,
            'signature'    =>    $signature,
            'ref1'    =>    $ref1
        ];

        $url = env('LINKITA');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = json_encode($response);
        $log = $Generate->createLog($content, 'tf_pay');
        // Jika transferPay berhasil
        $mutasi = Mutasi::create([
            'id_user' => $user->id,
            'id_transaksi' => $response->id_transaksi_pay,
            'jenis_transaksi' => 'Transaksi',
            'status' => 'Sukses',
            'tanggal' => now(),
            'debit' => $nominal,
            'kredit' => 0
        ]);

        return $response;
    }

    // Get Check pay
    public function vacheckPay(Request $request)
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
        $signature = $Generate->generateSignature($clientKey, $method, $kodeProduk, $time, $idPelanggan, $idMember, $ref1);
        $kodeProduk .= substr($idPelanggan, 0, 3);
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
        $log = $this->createLog($response, 'va_check_pay');

        return $response;
    }

    // Get Inq + Check pay
    public function vaPayAndCheckPay(Request $request)
    {
        $transfervaPayResponse = $this->transferInq($request);
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
            $checkvaPayResponse = $this->checkPay($checkvaPayRequest);
            $checkvaPayContent = $checkvaPayResponse;

            // Simpan log jika perlu
            $log = new lk_log;
            $log->customer_id = $checkvaPayContent->id_pelanggan ?? 0;
            $log->nama = $checkvaPayContent->nama_pelanggan ?? 0;
            $log->method = 'tfcek_pay';
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


 