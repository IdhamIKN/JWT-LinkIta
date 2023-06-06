<?php

namespace App\Http\Controllers\Api\Member;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use meter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\LinkIta\GenerateController;
use App\Models\tiket;
use App\Models\User;
use App\Models\mutasi;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\LinkIta\ApiDataController;


class MemberController extends Controller
{

    public function topUp(Request $request)
    {
        $Generate = new GenerateController;
        $tiket = $Generate->idTransaksi();
        $uniq = $Generate->unik();

        $user = auth()->guard('api')->user()->id;

        // Set validation rules
        $validator = Validator::make($request->all(), [
            'nominal' => 'required|numeric|min:100000'
        ], [
            'nominal.required' => 'Nominal harus diisi.',
            'nominal.numeric' => 'Nominal harus berupa angka.',
            'nominal.min' => 'Nominal minimal adalah Rp. 100000.'
        ]);

        // If validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Mengambil 3 digit belakang dari nominal sebagai angka random
        $nominal = $request->nominal;
        $randomDigit = substr($nominal, -3);
        $nominalWithRandom = $nominal - $randomDigit + $uniq; // Menggabungkan nominal dengan angka random

        // Create member
        $member = tiket::create([
            'deposit'  => $nominalWithRandom,
            'status'  => '1',
            'id_user' => $user,
            'tiket' => 'T-' . $tiket
        ]);

        // Return JSON response if member is created
        if ($member) {
            return response()->json([
                'SUCCESS' => true,
                'TIKET' => 'T-' . $tiket,
                'MEMBER ID' => $user,
                'NOMINAL'  => $nominalWithRandom,
                'STATUS'  => 'SEDANG DIPROSES'
            ], 201);
        }

        // Return JSON response if the insertion process failed
        return response()->json([
            'success' => false
        ], 409);
    }


    public function verifytopUp(Request $request)
    {
        // Cek role pengguna yang telah login
        $user = auth()->guard('api')->user();
        if ($user->role != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk melakukan verifikasi top up.'
            ], 403);
        }

        $Generate = new GenerateController;
        $tiket = $Generate->idTransaksi();

        // Set validation rules
        $validator = Validator::make($request->all(), [
            'nominal' => 'required',
            'tiket' => 'required'
        ], [
            'nominal.required' => 'Nominal harus diisi.',
            'tiket.required' => 'Tiket harus diisi.'
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Periksa apakah tiket ada di dalam database
        $member = tiket::where('tiket', $request->tiket)->first();

        // Jika tiket tidak ditemukan
        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak valid.'
            ], 404);
        }

        // Periksa apakah tiket telah kedaluwarsa (1 jam dalam contoh ini)
        $expirationTime = now()->subHour();
        if ($member->created_at < $expirationTime) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket telah kadaluarsa.'
            ], 400);
        }

        // Update member
        $member->deposit = $request->nominal;
        $member->status = '2';
        $member->tiket = 'S-' . $tiket;
        $member->save();

        // Simpan data ke tabel mutasi jika statusnya adalah 200
        if ($member->status === '2') {
            $mutasi = mutasi::create([
                'id_user' => $member->id_user,
                'id_transaksi' => $tiket,
                'jenis_transaksi' => 'Deposit',
                'status' => $member->status,
                'tanggal' => now(),
                'debit' => 0,
                'kredit' => $request->nominal
            ]);
        }

        // Mengembalikan respons JSON jika member berhasil diperbarui
        return response()->json([
            'success' => true,
            'MEMBER ID' => $member->id_user,
            'NOMINAL'  => $request->nominal,
            'STATUS'  => 'TRANSAKSI SUKSES'
        ], 200);
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
