<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class JneController extends Controller
{
    public function price(Request $request)
    {
        $request->validate([
            'thru' => 'required',
        ]);


        $response = Http::asForm()->post(
            env('JNE_API_URL'),
            [
                'username' => env('JNE_USERNAME'),
                'api_key' => env('JNE_API_KEY'),
                'from' => env('JNE_ORIG'),
                'thru' => $request->thru,
                'weight' => 1
            ]
        );


        return response()->json(
            $response->json()
        );
    }
}