<?php

namespace App\Http\Controllers;

use App\Models\DataPembeli;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorPNG;

class PrintResiController extends Controller
{
    public function __invoke(DataPembeli $order)
    {
        $generator = new BarcodeGeneratorPNG();

        // Barcode untuk nomor order (selalu ada)
        $orderBarcodePng = $generator->getBarcode($order->order_number, $generator::TYPE_CODE_128);
        $orderBarcode = 'data:image/png;base64,' . base64_encode($orderBarcodePng);

        // Barcode untuk nomor resi (hanya jika sudah ada resi)
        $resiBarcode = null;
        if ($order->no_resi) {
            $resiBarcodePng = $generator->getBarcode($order->no_resi, $generator::TYPE_CODE_128);
            $resiBarcode = 'data:image/png;base64,' . base64_encode($resiBarcodePng);
        }

        $items = collect($order->items ?? []);
        $totalWeight = max(1, $items->sum('quantity')) * 0.5;

        $data = [
            'order'        => $order,
            'orderBarcode' => $orderBarcode,
            'resiBarcode'  => $resiBarcode,
            'isCod'        => $order->payment_method === 'cod',
            'weight'       => number_format($totalWeight, 1),
            'shipDate'     => $order->updated_at->format('d-m-Y'),

            'storeName'    => env('JNE_SHIPPER_NAME', 'Toko Saya'),
            'storePhone'   => env('JNE_SHIPPER_PHONE', '-'),
            'storeRegion'  => env('JNE_SHIPPER_CITY', '') . ', ' . env('JNE_SHIPPER_REGION', ''),
            'serviceCode'  => strtoupper($order->jne_service_code ?? 'REG'),
            'originCode'   => env('JNE_ORIG', ''),
            'csPhone'      => env('JNE_CS_PHONE', '(021) 29278888'),
        ];

        // 100mm x 150mm dalam satuan point (1mm = 2.8346 pt)
        // 100mm = 283.46pt, 150mm = 425.20pt
       $pdf = Pdf::loadView('label-resi', $data)
    ->setPaper([0, 0, 150, 240], 'portrait')
    ->setOption('dpi', 150)
    ->setOption('isHtml5ParserEnabled', true)
    ->setOption('isRemoteEnabled', true);

        return $pdf->stream("resi-{$order->order_number}.pdf");
    }
}