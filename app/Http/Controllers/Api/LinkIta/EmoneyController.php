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

class EmoneyController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // Get Transfer Inquiry
    public function inqEmoney(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();
        $time = $Generate->time();
        $ref1 = $Generate->generateRef1();

        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');

        $method = LKMethod::InqEmoney;

        // Mengambil nilai dari request pengguna
        $idPelanggan = $request->id_pelanggan;
        $nominal = $request->nominal;
        $kodeProduk = $request->kode_produk;

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

        $log = new lk_log;
        $log->customer_id = $content->id_pelanggan ?? 0;
        $log->status = $content->status ?? 0;
        $log->ket =  $content->keterangan ?? 0;
        $log->signature = $signature;
        $log->content = json_encode($response);
        $log->save();

        return $response;
    }

    // Get Check Inq
    public function emoneyInq(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();
        $time = $Generate->time();
        $ref1 = $Generate->generateRef1();

        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');

        $method = LKMethod::EmoneyInq;


        // Mengambil nilai dari request pengguna
        $idPelanggan = $request->id_pelanggan;
        $nominal = $request->nominal;
        $idtransaksi = $request->idtransaksi;
        $kodeProduk = $request->kode_produk;

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

        $log = new lk_log;
        $log->customer_id = $content->id_pelanggan ?? 0;
        $log->status = $content->status ?? 0;
        $log->ket =  $content->keterangan ?? 0;
        $log->signature = $signature;
        $log->content = json_encode($response);
        $log->save();
        return $response;
    }

    public function emoneyInqAndCheckInq(Request $request)
    {
        $emoneyInqResponse = $this->inqEmoney($request);
        $emoneyInqContent = $emoneyInqResponse;
        $Generate = new GenerateController;
        // Ambil nilai yang diperlukan dari transferInqResponse jika perlu
        $idtransaksi = $emoneyInqContent->id_transaksi_inq ?? null; // Menggunakan akses yang sesuai ke properti id_transaksi_inq

        if ($idtransaksi) {
        // Tunda eksekusi selama 3 detik
        sleep(3);
            // Panggil fungsi checkInq dengan idtransaksi yang didapatkan
            $checkInqRequest = $request;
            $checkInqRequest->merge(['idtransaksi' => $idtransaksi]); // Tambahkan idtransaksi ke request checkInq
            $checkInqResponse = $this->emoneyInq($checkInqRequest);
            $checkInqContent = $checkInqResponse;

            // Ambil nilai yang diperlukan dari checkInqResponse jika perlu

            // Simpan log jika perlu
            $log = new lk_log;
            $log->customer_id = $checkInqContent->id_pelanggan ?? 0;
            $log->status = $checkInqContent->status ?? 0;
            $log->ket = $checkInqContent->keterangan ?? 0;
            $log->signature = $Generate->generateSignature(env('CLIENT_KEY'), LKMethod::EmoneyInq, $checkInqRequest->kodeProduk, date('Y-m-d H:i:s'), $checkInqRequest->id_pelanggan, env('ID_MEM'), $Generate->generateRef1());
            $log->content = json_encode($checkInqResponse);
            $log->save();

            return $checkInqResponse; // Mengembalikan hasil dari checkInq sebagai respons
        } else {
            return response()->json([
                'error' => 'Gagal mendapatkan idtransaksi dari transferInqResponse'
            ], 400);
        }
    }

    // Get Transfer pay
    public function payEmoney(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();
        $time = $Generate->time();
        $ref1 = $Generate->generateRef1();

        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');

        $method = LKMethod::PayEmoney;

        // Mengambil nilai dari request pengguna
        $idPelanggan = $request->id_pelanggan;
        $nominal = $request->nominal;
        $idtransaksi = $request->idtransaksi;
        $kodeProduk = $request->kode_produk;

        // Generate Signature
        $signature = $Generate->generateSignature($clientKey, $method, $kodeProduk, $time, $idPelanggan, $idMember, $ref1);

        $data = [
            'method'    =>    $method,
            'kode_produk'    =>    $kodeProduk,
            'waktu'    =>    $time,
            'id_transaksi_inq'    =>    $idtransaksi,
            'id_pelanggan'    =>    $idPelanggan,
            'id_member'    => env('ID_MEM'),
            'nominal'    =>    $nominal,
            'signature'    =>    $signature,
            'ref1'    =>    '123'
        ];

        $url = env('LINKITA');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;

        $log = new lk_log;
        $log->customer_id = $content->id_pelanggan ?? 0;
        $log->status = $content->status ?? 0;
        $log->ket =  $content->keterangan ?? 0;
        $log->signature = $signature;
        $log->content = json_encode($response);
        $log->save();
        return $response;
    }

    // Get Check pay
    public function emoneyPay(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();
        $time = $Generate->time();
        $ref1 = $Generate->generateRef1();

        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');

        $method = LKMethod::EmoneyPay;

        // Mengambil nilai dari request pengguna
        $idPelanggan = $request->id_pelanggan;
        $nominal = $request->nominal;
        $idtransaksi = $request->idtransaksi;
        $kodeProduk = $request->kode_produk;

        // Generate Signature
        $signature = $Generate->generateSignature($clientKey, $method, $kodeProduk, $time, $idPelanggan, $idMember, $ref1);

        $data = [
            'method'    =>    $method,
            'kode_produk'    =>  $kodeProduk,
            'waktu'    =>    $time,
            'id_transaksi_inq'    =>  $idtransaksi,
            'id_pelanggan'    =>    $idPelanggan,
            'id_member'    =>    env('ID_MEM'),
            'nominal'    =>    '',
            'signature'    =>    $signature,
            'ref1'    =>    $ref1
        ];

        $url = env('LINKITA');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;

        $log = new lk_log;
        $log->customer_id = $content->id_pelanggan ?? 0;
        $log->status = $content->status ?? 0;
        $log->ket =  $content->keterangan ?? 0;
        $log->signature = $signature;
        $log->content = json_encode($response);
        $log->save();
        return $response;
    }

    public function emoneyPayAndCheckPay(Request $request)
    {
        $emoneyPayResponse = $this->payEmoney($request);
        $emoneyPayContent = $emoneyPayResponse;
        $Generate = new GenerateController;
        // Ambil nilai yang diperlukan dari transferInqResponse jika perlu
        $idtransaksi = $emoneyPayContent->id_transaksi_inq ?? null; // Menggunakan akses yang sesuai ke properti id_transaksi_inq

        if ($idtransaksi) {
        // Tunda eksekusi selama 3 detik
        sleep(3);
            // Panggil fungsi checkInq dengan idtransaksi yang didapatkan
            $checkPayRequest = $request;
            $checkPayRequest->merge(['idtransaksi' => $idtransaksi]); // Tambahkan idtransaksi ke request checkInq
            $checkPayResponse = $this->emoneyPay($checkPayRequest);
            $checkPayContent = $checkPayResponse;

            // Ambil nilai yang diperlukan dari checkInqResponse jika perlu

            // Simpan log jika perlu
            $log = new lk_log;
            $log->customer_id = $checkPayContent->id_pelanggan ?? 0;
            $log->status = $checkPayContent->status ?? 0;
            $log->ket = $checkPayContent->keterangan ?? 0;
            $log->signature = $Generate->generateSignature(env('CLIENT_KEY'), LKMethod::EmoneyPay, $checkPayRequest->kodeProduk, date('Y-m-d H:i:s'), $checkPayRequest->id_pelanggan, env('ID_MEM'), $Generate->generateRef1());
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
