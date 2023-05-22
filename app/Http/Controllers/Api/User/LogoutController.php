<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //remove token
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

        if($removeToken) {
            //return response JSON
            return response()->json([
                'success' => true,
                'message' => 'Logout Berhasil!',
            ]);
        }
    }
}
// class LogoutController extends Controller
// {
//     /**
//      * Handle the incoming request.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @return \Illuminate\Http\Response
//      */
//     public function __invoke(Request $request)
//     {
//         // remove token
//         $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

//         if (Auth::check()) {
//             //update user's jwt column to null
//             auth()->user()->update(['jwt' => null]);
//         }

//             // return response JSON
//             return response()->json([
//                 'success' => true,
//                 'message' => 'Logout Berhasil!',
//             ]);

//     }

// }
// class LogoutController extends Controller
// {
//     /**
//      * Handle the incoming request.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @return \Illuminate\Http\Response
//      */
//     public function __invoke(Request $request)
//     {
//         //remove token
//         $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

//         if($removeToken) {
//             //update user jwt column to null
//             $user = auth()->guard('api')->user();
//             if ($user) {
//                 $user->update(['jwt' => 0]);
//                 return response()->json([
//                     'success' => true,
//                     'message' => 'Logout Berhasil!',
//                 ]);
//             } else {
//                 return response()->json([
//                     'success' => false,
//                     'message' => 'Pengguna tidak ditemukan',
//                 ], 404);
//             }
//         }
//     }
// }


