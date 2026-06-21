<?php

namespace App\Services;

use App\Models\DataPembeli;
use Illuminate\Support\Facades\Http;

class JneService
{
    public static function generateAirwaybill(DataPembeli $order): array
    {
        $items = $order->items;
        $totalQty = collect($items)->sum('quantity');
        $totalWeight = max(1, $totalQty);
        $goodsDesc = substr(collect($items)->map(fn($item) => $item['name'])->implode(', '), 0, 60);
        $isCOD = $order->payment_method === 'cod';

        $payload = [
            'username'               => env('JNE_USERNAME'),
            'api_key'                => env('JNE_API_KEY'),
            'OLSHOP_BRANCH'          => env('JNE_BRANCH', 'BDO000'),
            'OLSHOP_CUST'            => env('JNE_CUST_NO'),
            'OLSHOP_ORDERID'         => $order->order_number,
            'OLSHOP_SHIPPER_NAME'    => env('JNE_SHIPPER_NAME'),
            'OLSHOP_SHIPPER_ADDR1'   => env('JNE_SHIPPER_ADDR1'),
            'OLSHOP_SHIPPER_ADDR2'   => env('JNE_SHIPPER_ADDR2', ''),
            'OLSHOP_SHIPPER_ADDR3'   => '',
            'OLSHOP_SHIPPER_CITY'    => env('JNE_SHIPPER_CITY'),
            'OLSHOP_SHIPPER_REGION'  => env('JNE_SHIPPER_REGION'),
            'OLSHOP_SHIPPER_ZIP'     => env('JNE_SHIPPER_ZIP'),
            'OLSHOP_SHIPPER_PHONE'   => env('JNE_SHIPPER_PHONE'),
            'OLSHOP_RECEIVER_NAME'   => substr($order->name, 0, 30),
            'OLSHOP_RECEIVER_ADDR1'  => substr($order->address, 0, 30),
            'OLSHOP_RECEIVER_ADDR2'  => substr($order->district . ', ' . $order->subdistrict, 0, 30),
            'OLSHOP_RECEIVER_ADDR3'  => '',
            'OLSHOP_RECEIVER_CITY'   => substr($order->regency, 0, 20),
            'OLSHOP_RECEIVER_REGION' => substr($order->province, 0, 20),
            'OLSHOP_RECEIVER_ZIP'    => $order->zip_code,
            'OLSHOP_RECEIVER_PHONE'  => preg_replace('/\D/', '', $order->phone),
            'OLSHOP_QTY'             => $totalQty,
            'OLSHOP_WEIGHT'          => $totalWeight,
            'OLSHOP_GOODSDESC'       => $goodsDesc,
            'OLSHOP_GOODSVALUE'      => (int) $order->grand_total,
            'OLSHOP_GOODSTYPE'       => '2',
            'OLSHOP_INST'            => $order->notes ?? '',
            'OLSHOP_INS_FLAG'        => $order->use_insurance ? 'Y' : 'N',
            'OLSHOP_ORIG'            => env('JNE_ORIG', 'BDO10000'),
            'OLSHOP_DEST'            => $order->jne_destination_code ?? '',
            'OLSHOP_SERVICE'         => strtoupper($order->jne_service_code ?? 'REG'),
            'OLSHOP_COD_FLAG'        => $isCOD ? 'YES' : 'N',
            'OLSHOP_COD_AMOUNT'      => $isCOD ? (int) $order->grand_total : 0,
        ];

        $response = Http::asForm()->post(
            env('JNE_AIRWAYBILL_URL', 'https://apiv2.jne.co.id:10206/tracing/api/generatecnote'),
            $payload
        );
        

        $data = $response->json();

        if (isset($data['detail'][0]['status']) && strtolower($data['detail'][0]['status']) === 'sukses') {
            $noResi = $data['detail'][0]['cnote_no'];
            $order->update([
                'no_resi' => $noResi,
                'status' => 'processing'
            ]);
            return ['success' => true, 'no_resi' => $noResi];
        }

        return [
            'success' => false,
            'message' => $data['detail'][0]['reason'] ?? 'Gagal generate resi'
        ];
    }
}