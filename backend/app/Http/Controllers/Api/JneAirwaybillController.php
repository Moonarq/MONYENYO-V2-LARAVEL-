<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataPembeli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class JneAirwaybillController extends Controller
{
    public function generate(Request $request)
    {
        $orderId = $request->order_id;
        
        $order = DataPembeli::where('order_number', $orderId)->first();
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan'
            ], 404);
        }

        // Hitung total quantity dan weight
        $items = $order->items;
        $totalQty = collect($items)->sum('quantity');
        $totalWeight = max(1, $totalQty); // 1kg per item minimum

        // Deskripsi barang
        $goodsDesc = collect($items)->map(fn($item) => $item['name'])->implode(', ');
        $goodsDesc = substr($goodsDesc, 0, 60); // max 60 char

        // COD flag
        $isCOD = $order->payment_method === 'cod';
$goodsValue = $order->grand_total ?? $order->final_total ?? 0;
$rawTotal = $order->grand_total ?? $order->final_total ?? 1000;
$cleanTotal = (int) floatval($rawTotal);
// Jika tetap 0 atau null, paksa ke 1000 agar tidak error
if (empty($goodsValue) || $goodsValue <= 0) {
    $goodsValue = 1000;
}
       $payload = [
        'username'              => env('JNE_USERNAME'),
        'api_key'               => env('JNE_API_KEY'),
        'OLSHOP_BRANCH'         => env('JNE_BRANCH'),
        'OLSHOP_CUST'           => env('JNE_CUST_NO'),
        'OLSHOP_ORDERID'        => (string) $order->order_number,
        'OLSHOP_SHIPPER_NAME'   => 'BrowniesPastryMonyenyo', 
        'OLSHOP_SHIPPER_ADDR1'  => 'JlContohNo1', // Hapus titik dan spasi untuk sementara
        'OLSHOP_SHIPPER_CITY'   => 'BANDUNG',
        'OLSHOP_SHIPPER_REGION' => 'JAWABARAT',
        'OLSHOP_SHIPPER_ZIP'    => '40000',
        'OLSHOP_SHIPPER_PHONE'  => '081234567890',
        'OLSHOP_RECEIVER_NAME'  => 'TEST',
        'OLSHOP_RECEIVER_ADDR1' => 'TEST',
        'OLSHOP_RECEIVER_CITY'  => 'BANDUNG',
        'OLSHOP_RECEIVER_REGION'=> 'JAWABARAT',
        'OLSHOP_RECEIVER_ZIP'   => '40000',
        'OLSHOP_RECEIVER_PHONE' => '081234567890',
        'OLSHOP_QTY'            => (string) $totalQty,
        'OLSHOP_WEIGHT'         => (string) $totalWeight,
        'OLSHOP_GOODSDESC'      => 'Brownies',
        'OLSHOP_GOODSVALUE'     => '10000', // Hardcode dulu untuk tes
        'OLSHOP_GOODSTYPE'      => '2',
        'OLSHOP_ORIG'           => 'BDO10000',
        'OLSHOP_DEST'           => 'CGK01',
        'OLSHOP_SERVICE'        => 'REG',
        'OLSHOP_COD_FLAG'       => 'N',
    ];

    // MENGIRIM REQUEST DENGAN RAW BODY
    $ch = curl_init(env('JNE_AIRWAYBILL_URL'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    curl_setopt($ch, CURLOPT_POST, true);
    
    $result = curl_exec($ch);
    curl_close($ch);

    return response()->json(json_decode($result, true));
        dd([
    'final_total' => $order->final_total,
    'payload' => $payload
]);
dd($payload);
        try {
            $response = Http::asForm()->post(
                env('JNE_AIRWAYBILL_URL', 'https://apiv2.jne.co.id:10206/tracing/api/generatecnote'),
                $payload
            );

            $data = $response->json();

            if (isset($data['detail'][0]['status'])) {
                $detail = $data['detail'][0];
                
                if (strtolower($detail['status']) === 'sukses') {
                    // Simpan no resi ke database
                    $order->update([
                        'no_resi' => $detail['cnote_no'],
                        'status' => 'processed'
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Airwaybill berhasil digenerate',
                        'data' => [
                            'no_resi' => $detail['cnote_no'],
                            'order_number' => $order->order_number
                        ]
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => $detail['reason'] ?? 'Gagal generate airwaybill',
                        'cnote_no' => $detail['cnote_no'] ?? null
                    ], 422);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Response tidak valid dari JNE',
                'raw' => $data
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi JNE API',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}