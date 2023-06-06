<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;



class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required',
            'password'  => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api1.linkita.id/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                "username"	=>	"team.itbmd@gmail.com",
                "password"	=>	"bmdsyariah2001"
            ]),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            )
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        if (!$response) {
            return response()->json([
                'success' => false,
                'message' => 'Error calling API'
            ], 500);
        }

        $response = json_decode($response, true);
        $validationToken = $response['validation'] ?? null;

        if (!$validationToken) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting validation token from API response'
            ], 500);
        }

        if (!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password Anda salah'
            ], 401);
        }

        $user = auth()->guard('api')->user();
        $user->update([
            'last_login' => now(),
            'ip_address' => $request->ip(),
            'jwt' => $validationToken
        ]);

        return response()->json([
            'success' => true,
            'token'   => $token
            // 'user' =>$user
        ], 200);
    }
}

