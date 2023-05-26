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

class TransferController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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

        $log = new lk_log;
        $log->customer_id = $content->id_pelanggan ?? 0;
        $log->nama = $content->nama_pelanggan ?? 0;
        $log->method = 'tf_inq';
        $log->id_pay = $content->id_transaksi_pay ?? 0;
        $log->id_inq = $content->id_transaksi_inq ?? 0;
        $log->nominal = $nominal;
        $log->status = $content->status ?? 0;
        $log->ket =  $content->keterangan ?? 0;
        $log->content = json_encode($response);
        $log->save();

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

        $log = new lk_log;
        $log->customer_id = $content->id_pelanggan ?? 0;
        $log->nama = $content->nama_pelanggan ?? 0;
        $log->method = 'cek_inq';
        $log->id_pay = $content->id_transaksi_pay ?? 0;
        $log->id_inq = $content->id_transaksi_inq ?? 0;
        $log->nominal = $content->nominal ?? 0;
        $log->status = $content->status ?? 0;
        $log->ket =  $content->keterangan ?? 0;
        $log->content = json_encode($response);
        $log->save();
        return $response;
    }

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


    // Get Transfer pay
    public function transferPay(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();
        $time = $Generate->time();
        $ref1 = $Generate->generateRef1();

        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');

        $method = LKMethod::PayBank;
        $kodeProduk = LKConstant::TFBank;

        // Mengambil nilai dari request pengguna
        $idPelanggan = $request->id_pelanggan;
        $nominal = $request->nominal;
        $idtransaksi = $request->idtransaksi;

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
        $log->nama = $content->nama_pelanggan ?? 0;
        $log->method = 'tf_pay';
        $log->id_pay = $content->id_transaksi_pay ?? 0;
        $log->id_inq = $content->id_transaksi_inq ?? 0;
        $log->nominal = $content->nominal ?? 0;
        $log->status = $content->status ?? 0;
        $log->ket =  $content->keterangan ?? 0;
        $log->content = json_encode($response);
        $log->save();
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
        $log->nama = $content->nama_pelanggan ?? 0;
        $log->method = 'cek_pay';
        $log->id_pay = $content->id_transaksi_pay ?? 0;
        $log->id_inq = $content->id_transaksi_inq ?? 0;
        $log->nominal = $content->nominal ?? 0;
        $log->status = $content->status ?? 0;
        $log->ket =  $content->keterangan ?? 0;
        $log->content = json_encode($response);
        $log->save();
        return $response;
    }

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
}
