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

use App\Http\Controllers\Api\LinkIta\TransferController;
use Dompdf\Dompdf;
use TCPDF;
use App\Models\mutasi;
use Illuminate\Support\Facades\DB;






class ApiDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // Cek Saldo
    public function getBalance()
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();

        $data = [
            'request' => 'saldo',
            'id_member' => env('ID_MEM')
        ];

        $url = env('LINKITA');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;

        // $log = new lk_log;
        // $log->customer_id = '001';
        // $log->status = 'Check Saldo';
        // $log->ket = 'Check Saldo';
        // $log->content = json_encode($response);
        // $log->save();

        return $response;
    }

    // Get Data Bank
    public function getBank()
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();

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
        $log->content = json_encode($response);
        $log->save();
        return $response;
    }

    public function getStruk(Request $request)
    {
        $Generate = new TransferController;
        $struk = $Generate->checkPay($request);
        $idtransaksi = $request->idtransaksi;

        // Ubah nilai biaya_admin menjadi 10000
        $struk->biaya_admin = "10000";

        // Hitung total_bayar
        $nominal = intval($struk->nominal);
        $biaya_admin = intval($struk->biaya_admin);
        $total_bayar = $nominal + $biaya_admin;

        // Tambahkan total_bayar ke dalam respons
        $struk->total_bayar = strval($total_bayar);

        $bankData = $this->getBank();

        // Buat array untuk menyimpan data bank dengan format kode_bank => nama_bank
        $bankList = [];
        if ($bankData && isset($bankData->data_bank)) {
            foreach ($bankData->data_bank as $bank) {
                $bankList[$bank->kode_bank] = $bank->nama_bank;
            }
        }

        // Buat PDF menggunakan data $struk
        $pdf = new TCPDF('P', 'mm', array(80, 130), true, 'UTF-8', false);
        $pdf->SetCreator('BMD Syariah');
        $pdf->SetAuthor('BMD Syariah');
        $pdf->SetTitle('Struk Transfer');
        $pdf->SetMargins(5, 5, 5);
        $pdf->AddPage();

        // Tampilkan data $struk dalam format yang diinginkan di dalam PDF
        $html = '';
        $html .= '<p style="text-align: justify;">TRANSAKSI: ' . $idtransaksi . '</p>';
        // dd($idtransaksi);
        $html .= '<p style="text-align: justify;">WAKTU: ' . $struk->waktu . '</p>';
        $html .= '<p style="text-align: center; margin-top: 10px; margin-bottom: 20px;">STRUK TRANSFER UANG</p>';
        // Pisahkan string menggunakan tanda "-" dan ambil elemen pertama sebagai kode bank
        $kodeBank = explode('-', $struk->id_pelanggan)[0];
        // Cek apakah kodeBank ada dalam bankList
        if (isset($bankList[$kodeBank])) {
            $bankName = $bankList[$kodeBank];
        } else {
            $bankName = 'Tidak Diketahui';
        }
        // Pisahkan string menggunakan tanda "-" dan ambil elemen terakhir sebagai nomor rekening
        $rekeningParts = explode('-', $struk->id_pelanggan);
        $rekeningNumber = end($rekeningParts);

        $html .= '<p style="text-align: justify;">NO. REKENING: ' . $rekeningNumber . '</p>';
        $html .= '<p style="text-align: justify;">BANK: ' . $bankName . '</p>';
        $html .= '<p style="text-align: justify;">NAMA: ' . $struk->nama_pelanggan . '</p>';
        $nominalFormatted = 'Rp ' . number_format($struk->nominal, 0, ',', '.');
        $biayaAdminFormatted = 'Rp ' . number_format($struk->biaya_admin, 0, ',', '.');
        $totalBayarFormatted = 'Rp ' . number_format($total_bayar, 0, ',', '.');
        $html .= '<p style="text-align: justify;">NOMINAL: ' . $nominalFormatted . '</p>';
        $html .= '<p style="text-align: justify;">BIAYA ADMIN: ' . $biayaAdminFormatted . '</p>';
        $html .= '<p style="text-align: justify;">TOTAL: ' . $totalBayarFormatted . '</p>';
        $html  .= '<p style="text-align: center;">Terima Kasih</p>';
        $pdf->writeHTML($html, true, false, true, false, '');

        // Simpan PDF ke direktori yang diinginkan
        $pdfPath = public_path('struk.pdf');
        $pdf->Output($pdfPath, 'F');

        // Menghapus file PDF setelah dikirim sebagai respons
        register_shutdown_function(function () use ($pdfPath) {
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }
        });

        // Mengembalikan respon berupa link ke file PDF
        return response()->download($pdfPath, 'struk.pdf')->deleteFileAfterSend(true);
    }


    // Get Url Widget
    public function getUrlWidget(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();

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
        $log->content = json_encode($response);
        $log->save();
        return $response;
    }

    // Get Transaksi
    public function transaksi(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();

        $idMember = env('ID_MEM');

        // Mengambil nilai dari request pengguna
        $idTransaksi = $request->id_transaksi;
        $idPelanggan = $request->id_pelanggan;
        $status = $request->status;
        $page = $request->page;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $data = [
            'request' => LKConstant::Trans,
            'id_member' => $idMember,
            'id_transaksi' => $idTransaksi,
            'id_pelanggan' => $idPelanggan,
            'status' => $status,
            'page' => $page,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];

        $url = env('LINKITA');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;

        $log = new lk_log;
        $log->customer_id = '004';
        $log->status = 'Get Transaksi';
        $log->ket = 'Get Transaksi';
        $log->content = json_encode($response);
        $log->save();

        return $response;
    }

    // Get Transaksi
    public function mutasi(Request $request)
    {
        $Generate = new GenerateController;
        $token = $Generate->getJwtToken();

        $idMember = env('ID_MEM');

        // Mengambil nilai dari request pengguna
        $idTransaksi = $request->id_transaksi;
        $idPelanggan = $request->id_pelanggan;
        $status = $request->status;
        $page = $request->page;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $data = [
            'request' => LKConstant::Mutasi,
            'id_member' => $idMember,
            'id_transaksi' => $idTransaksi,
            'id_pelanggan' => $idPelanggan,
            'status' => $status,
            'page' => $page,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];

        $url = env('LINKITA');
        $response = Helper::DataLinkita($url, $data, $token);

        // Simpan Log
        $content = $response;

        $log = new lk_log;
        $log->customer_id = '005';
        $log->status = 'Get Mutasi';
        $log->ket = 'Get Mutasi';
        $log->content = json_encode($response);
        $log->save();

        return $response;
    }

    public function checkBalance()
    {
        $user = auth()->guard('api')->user();

        if ($user->role == 1) {
            $Balance = new ApiDataController;
            $cash = $Balance->getBalance();

            // Jika user admin, tampilkan semua data saldo member
            $saldo = Mutasi::select('id_user', DB::raw('SUM(kredit - debit) AS saldo'))
                ->groupBy('id_user')
                ->get();

            return response()->json([

                'success' => true,
                'nama_user' => $user->name,
                'saldo global' => $cash->nominal,
                'saldo' => $saldo
            ], 200);
        } elseif ($user->role == 2) {
            // Jika user member, tampilkan saldo berdasarkan ID user
            $saldo = Mutasi::select(DB::raw('SUM(kredit - debit) AS saldo'))
                ->where('id_user', $user->id)
                ->first();

            return response()->json([
                'success' => true,
                'nama_user' => $user->name,
                'saldo' => $saldo->saldo
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk melakukan pengecekan saldo.'
            ], 403);
        }
    }
}
