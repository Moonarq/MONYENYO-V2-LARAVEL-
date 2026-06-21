<?php

namespace App\Http\Controllers\Api;

use App\Services\JneService;
use App\Http\Controllers\Controller;
use App\Models\DataPembeli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MidtransController extends Controller
{
    public function getToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_number' => 'required|string',
            'gross_amount' => 'required|numeric|min:0',
            'customer_details' => 'required|array',
            'item_details' => 'required|array',
            'order_data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $paymentMethod = $request->order_data['payment_method'] ?? '';

            $params = [
                'transaction_details' => [
                    'order_id' => $request->order_number,
                    'gross_amount' => (int) $request->gross_amount,
                ],
                'customer_details' => $request->customer_details,
                'item_details' => $request->item_details,
                'enabled_payments' => $this->getEnabledPayments($paymentMethod),
                'callbacks' => [
                    'finish' => 'http://localhost:5173/order-success',
                    'unfinish' => 'http://localhost:5173/order-success',
                    'error' => 'http://localhost:5173/order-success',
                ],
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($params);

            $orderData = $request->order_data;
            $items = $orderData['items'] ?? [];
            $totalItems = collect($items)->sum('quantity');

            DataPembeli::create([
                'name' => $orderData['name'] ?? '',
                'email' => $orderData['email'] ?? '',
                'phone' => $orderData['phone'] ?? '',
                'country' => $orderData['country'] ?? 'Indonesia',
                'address' => $orderData['address'] ?? '',
                'zip_code' => $orderData['zip_code'] ?? '',
                'province' => $orderData['province'] ?? '',
                'regency' => $orderData['regency'] ?? '',
                'district' => $orderData['district'] ?? '',
                'subdistrict' => $orderData['subdistrict'] ?? '',
                'payment_method' => $orderData['payment_method'] ?? '',
                'shipping_method' => $orderData['shipping_method'] ?? '',
                'shipping_cost' => $orderData['shipping_cost'] ?? 0,
                'is_shipping_free' => $orderData['is_shipping_free'] ?? false,
                'use_insurance' => $orderData['use_insurance'] ?? false,
                'insurance_cost' => $orderData['insurance_cost'] ?? 0,
                'items' => $items,
                'vouchers' => $orderData['vouchers'] ?? null,
                'subtotal_before_voucher' => $orderData['subtotal_before_voucher'] ?? 0,
                'total_voucher_discount' => $orderData['total_voucher_discount'] ?? 0,
                'final_total' => $orderData['final_total'] ?? 0,
                'total_items' => $totalItems,
                'grand_total' => $orderData['grand_total'] ?? 0,
                'notes' => $orderData['notes'] ?? null,
                'is_buy_now' => $orderData['is_buy_now'] ?? false,
                'status' => 'pending',
                'order_number' => $request->order_number,
                'no_resi' => $request->no_resi ?? null,
                'jne_destination_code' => $request->jne_destination_code ?? null,
                'jne_service_code' => $request->jne_service_code ?? null,
            ]);

            return response()->json([
                'success' => true,
                'token' => $snapToken,
                'order_number' => $request->order_number,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat token Midtrans',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
public function webhook(Request $request)
{
    $serverKey = env('MIDTRANS_SERVER_KEY');
    $orderId = $request->order_id;
    $statusCode = $request->status_code;
    $grossAmount = $request->gross_amount;
    
    // Verifikasi signature
    $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
    
    if ($signatureKey !== $request->signature_key) {
        return response()->json(['message' => 'Invalid signature'], 403);
    }

    $transactionStatus = $request->transaction_status;
    $fraudStatus = $request->fraud_status ?? 'accept';

    $order = DataPembeli::where('order_number', $orderId)->first();

    if (!$order) {
        return response()->json(['message' => 'Order not found'], 404);
    }

    // Cek status pembayaran
    if ($transactionStatus === 'capture' && $fraudStatus === 'accept') {
        $order->update(['status' => 'paid']);
        JneService::generateAirwaybill($order);

    } elseif ($transactionStatus === 'settlement') {
        $order->update(['status' => 'paid']);
        JneService::generateAirwaybill($order);

    } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
        $order->update(['status' => 'cancelled']);

    } elseif ($transactionStatus === 'pending') {
        $order->update(['status' => 'pending']);
    }

    return response()->json(['message' => 'OK']);
}
    private function getEnabledPayments($paymentMethod)
    {
        switch ($paymentMethod) {
            case 'mandiri':
                return ['echannel'];
            case 'qris':
                return ['other_qris'];  
            case 'bri':
                return ['bri_va'];
            case 'bni':
                return ['bni_va'];
            case 'permata':
                return ['permata_va'];
            case 'CIMB NIAGA':
                return ['cimb_va'];
            default:
                return ['echannel', 'bri_va', 'bni_va', 'permata_va', 'cimb_va'];
        }
    }
}