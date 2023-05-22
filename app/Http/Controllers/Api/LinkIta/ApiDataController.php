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

class ApiDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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


    // Cek Saldo
    public function getBalance()
    {
        $token = $this->getJwtToken();

        $data = [
            'request' => 'saldo',
            'id_member' => env('ID_MEM')
        ];

        $url = env('LINKITA');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;

        $log = new lk_log;
        $log->customer_id = '001';
        $log->status = 'Check Saldo';
        $log->ket = 'Check Saldo';
        $log->signature = '0';
        $log->content = json_encode($response);
        $log->save();

        // return $response;
        $output = "Jenis Saldo: {$response->jenis}\n";
        $output .= "Nominal: {$response->nominal}\n";
        $output .= "Status: {$response->status}\n";
        $output .= "Keterangan: {$response->keterangan}\n";

        return $output;
    }

    // Get Data Bank
    public function getBank()
    {
        $token = $this->getJwtToken();

        $data = [
            'request' => 'bank',
            'id_member' => env('ID_MEM')
        ];

        $url = env('LINKITA');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;

        $log = new lk_log;
        $log->customer_id = '002';
        $log->status = 'Data Bank';
        $log->ket = 'Data Bank';
        $log->signature = '0';
        $log->content = json_encode($response);
        $log->save();
        return $response;
    }

    // Cetak Struk BELUM FIX
    public function getStruk(Request $request)
    {
        $token = $this->getJwtToken();
        $time = $this->time();
        $ref1 = $this->generateRef1();

        $clientKey = env('CLIENT_KEY');
        $idMember = env('ID_MEM');
        $method = LKMethod::InqBank;
        $kodeProduk = LKConstant::TFBank;

        // Mengambil nilai dari request pengguna
        $idPelanggan = $request->id_pelanggan;
        $id_transaksi = $request->id_transaksi;

        $signature = $this->generateSignature($clientKey, $method, $kodeProduk, $time, $idPelanggan, $idMember, $ref1);
        $data = [
            'request' => LKConstant::Struk,
            'cetak_ulang' => '0',
            'data' => json_encode([
                "method" => LKMethod::PayCheck,
                "id_transaksi_pay" => $id_transaksi,
                "kode_produk" => LKConstant::TFBank,
                "waktu" => $time,
                "id_pelanggan" => $idPelanggan,
                "id_member" => $idMember,
                "signature" => $signature,
                "ref1" => $ref1
            ])
        ];

        $url = env('LINKITA');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;

        $log = new lk_log;
        $log->customer_id = $idPelanggan;
        $log->status = 'Data Bank';
        $log->ket = 'Data Bank';
        $log->signature = '0';
        $log->content = json_encode($response);
        $log->save();
        return $response;
    }

    // Get Url Widget
    public function getUrlWidget(Request $request)
    {
        $token = $this->getJwtToken();
        // Mengambil nilai dari request pengguna
        $email = $request->email;
        $data = [
            'request' => LKConstant::Widget,
            'email_member'    => $email
        ];

        $url = env('LINKITA');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;

        $log = new lk_log;
        $log->customer_id = '003';
        $log->status = 'Get Widget';
        $log->ket = 'Get Widget';
        $log->signature = '0';
        $log->content = json_encode($response);
        $log->save();
        return $response;
    }
}
