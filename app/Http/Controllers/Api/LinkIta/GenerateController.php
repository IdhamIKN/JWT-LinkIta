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
use App\Models\mutasi;



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


    // Create Lk_log
    public function createLog($content, $method)
    {
        $log = new lk_log;
        $log->customer_id = $content->id_pelanggan ?? 0;
        $log->nama = $content->nama_pelanggan ?? '';
        $log->method = $method;
        $log->id_pay = $content->id_transaksi_pay ?? '';
        $log->id_inq = $content->id_transaksi_inq ?? '';
        $log->nominal = isset($content->nominal) && is_numeric($content->nominal) ? $content->nominal : 0;
        $log->status = $content->status ?? 0;
        $log->ket = $content->keterangan ?? '';
        $log->content = json_encode($content);
        $log->save();

        return $log;
    }

    // Create Lk_log1
    public function createLog1($content, $method)
    {
        $contentObj = json_decode($content);

        $log = new lk_log;
        $log->customer_id = $contentObj->id_pelanggan ?? 0;
        $log->nama = $contentObj->nama_pelanggan ?? '';
        $log->method = $method;
        $log->id_pay = $contentObj->id_transaksi_pay ?? '';
        $log->id_inq = $contentObj->id_transaksi_inq ?? '';
        $log->nominal = isset($contentObj->nominal) && is_numeric($contentObj->nominal) ? $contentObj->nominal : 0;
        $log->status = $contentObj->status ?? 0;
        $log->ket = $contentObj->keterangan ?? '';
        $log->content = $content;
        $log->save();

        return $log;
    }

    // Generate Reff
    public function idTransaksi()
    {
        $dateTime = new \DateTime();
        $currentDateTime = $dateTime->format('sid');
        $randomNumber = mt_rand(100, 999);

        $ref1 = $currentDateTime . $randomNumber;

        return $ref1;
    }

    // Generate nominal unik
    public function unik()
    {

        $randomNumber = mt_rand(100, 999);

        $ref1 = $randomNumber;

        return $ref1;
    }

    // Generate nominal unik
    public function gagal()
    {

        $randomNumber = mt_rand(100, 999);

        $ref1 = $randomNumber;

        return $ref1;
    }

    // Save Ke mutasi
    public function mutasi($response, $nominal)
    {
        $user = auth()->guard('api')->user();
        $mutasi = Mutasi::create([
            'id_user' => $user->id,
            'id_transaksi' => $response->id_transaksi_pay,
            'jenis_transaksi' => 'Transaksi',
            'status' => 'Sukses',
            'tanggal' => now(),
            'debit' => $nominal,
            'kredit' => 0
        ]);

        return $mutasi;
    }

    // Saldo tidak cukup
    public function fail($idtransaksi, $nominal, $idPelanggan, $idMember, $ref1)
    {
        $response = [
            "method" => "transfer_bank_payment",
            "kode_produk" => "TRSFBANK",
            "waktu" => now()->format('Y-m-d H:i:s'),
            "waktu_bayar" => now()->format('Y-m-d H:i:s'),
            "id_transaksi_inq" => $idtransaksi,
            "no_ref_pln" => "",
            "id_pelanggan" => $idPelanggan,
            "nama_pelanggan" => "",
            "nominal" => $nominal,
            "biaya_admin" => "",
            "info" => "",
            "id_member" => $idMember,
            "ref1" => $ref1,
            "id_transaksi_pay" => $idtransaksi,
            "status" => "7",
            "keterangan" => "Transfer dalam gangguan"
        ];

        return $response;
    }
}
