```blade
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>

@page{
    margin:6px;
}

body{
    margin:0;
    padding:0;
    font-family:Arial, Helvetica, sans-serif;
    font-size:10px;
}

.label{
    width:265px;
    margin:0 auto;
    border:2px solid #444;
    padding:6px;

    transform:scale(1.08);
    transform-origin: top center;
}

.header{
    display:table;
    width:100%;
    margin-bottom:4px;
}

.header > div{
    display:table-cell;
    vertical-align:top;
}

.header-right{
    text-align:center;
}

.logo-img{
    width:60px;
}

.cs-phone{
    font-size:8px;
    margin-top:10px;
    color:gray;
}

.service-code{
    font-size:24px;
    font-weight:bold;
    line-height:1;
    margin-right:70px;
}

.origin-code{
    font-size:8px;
    font-weight:bold;
    color:#666;
    margin-top:2px;
    margin-right:70px;
}

.divider{
    border-top:1px solid #666;
    margin:4px 0;
}

.ref-box{
    border:1px solid #d5d5d5;
    border-radius:4px;
    text-align:center;
    padding:3px;
    margin-bottom:4px;
}

.ref-label{
    color:#666;
    font-size:8px;
    margin-bottom:5px;
    
}

.barcode-img{
    width:90%;
    height:28px;
}

.ref-number{
    font-size:10px;
    font-weight:bold;
    margin-top:5px;
}

.info-row{
    display:table;
    width:100%;
    font-size:9px;
    margin-bottom:1px;
}

.info-row span{
    display:table-cell;
}

.info-phone{
    text-align:right;
}

.sender-region{
    font-size:9px;
    margin-bottom:2px;
}

.address-box{
    border:1px solid #444;
    padding:4px;
    min-height:50px;
    position:relative;
    margin-top:2px;
}

.address-text{
    font-size:9px;
    line-height:1.2;
}

.city-bold{
    margin-top:2px;
    font-size:11px;
    font-weight:bold;
}

.zip-code{
    font-size:9px;
}

.cod-badge{
    display:inline-block;
    background:red;
    color:#fff;
    padding:2px 8px;
    font-size:10px;
    font-weight:bold;
    margin-top:2px;
}

.notes-box{
    border:1px dashed #999;
    padding:3px;
    margin-top:2px;
}

.meta-row{
    display:table;
    width:100%;
    margin-top:3px;
}

.meta-row div{
    display:table-cell;
    width:50%;
}

.meta-row div:last-child{
    text-align:right;
}

.dimensi-row{
    margin-top:2px;
}

.cod-banner{
    background:#000;
    color:#fff;
    text-align:center;
    font-weight:bold;
    font-size:11px;
    padding:2px;
    margin-top:4px;
}

.resi-box{
    border:2px solid #666;
    text-align:center;
    margin-top:4px;
    padding:2px;
}

.resi-number{
    font-size:18px;
    font-weight:bold;
}

.resi-barcode{
    text-align:center;
    margin-top:2px;
}

.terms{
    text-align:center;
    font-size:6px;
    color:#666;
    margin-top:2px;
}

.product-table{
    width:100%;
    border-collapse:collapse;
    margin-top:4px;
    font-size:8px;
}

.product-table th,
.product-table td{
    border:1px solid #444;
    padding:1px;
}

.qty-total-cell{
    text-align:right;
    font-weight:bold;
}

.footer{
    text-align:center;
    margin-top:4px;
    font-size:7px;
}

</style>
</head>

<body>

<div class="label">

    {{-- HEADER --}}
    <div class="header">
        <div>
            <img src="{{ public_path('assets/jne.png') }}" class="logo-img" alt="JNE">
            <div class="cs-phone">{{ $csPhone }}</div>
        </div>

        <div class="header-right">
            <div class="service-code">{{ $serviceCode }}</div>
            <div class="origin-code">{{ $originCode }}</div>
        </div>
    </div>

    <div class="divider"></div>

    {{-- ORDER REFERENCE --}}
    <div class="ref-box">
        <div class="ref-label">ORDER REFERENCE</div>
        <img src="{{ $orderBarcode }}" class="barcode-img" alt="barcode">
        <div class="ref-number">{{ $order->order_number }}</div>
    </div>

    {{-- PENGIRIM --}}
    <div class="info-row">
        <span><strong>Pengirim:</strong> {{ $storeName }}</span>
        <span class="info-phone">{{ $storePhone }}</span>
    </div>

    <div class="sender-region">
        {{ $storeRegion }}
    </div>

    {{-- PENERIMA --}}
    <div class="info-row">
        <span><strong>Penerima:</strong> {{ $order->name }}</span>
        <span class="info-phone">{{ $order->phone }}</span>
    </div>

    {{-- ADDRESS --}}
    {{-- ADDRESS --}}

<div class="address-box">

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td width="80%" valign="top">

                <div class="address-text">
                    {{ $order->address }}
                </div>

                <div class="city-bold">
                    {{ strtoupper($order->regency) }}
                </div>

                <div class="zip-code">
                    kode pos {{ $order->zip_code }}
                </div>

            </td>

            <td width="20%" align="right" valign="bottom">

                @if($isCod)
                    <span class="cod-badge">
                        COD
                    </span>
                @endif

            </td>
        </tr>
    </table>

</div>

    {{-- NOTES --}}
    @if($order->notes)
    <div class="notes-box">
        <strong>Catatan:</strong><br>
        <i>{{ $order->notes }}</i>
    </div>
    @endif

    {{-- META --}}
    <div class="meta-row">
        <div><strong>Weight:</strong> {{ $weight }} KG</div>
        <div><strong>Ship:</strong> {{ $shipDate }}</div>
    </div>

    <div class="dimensi-row">
        <strong>Dimensi:</strong> {{ $order->total_items }}pcs, Barang Default
    </div>

    {{-- COD --}}
    @if($isCod)
    <div class="cod-banner">
        CASH ON DELIVERY (COD)
    </div>
    @endif

    {{-- RESI --}}
    <div class="resi-box">
        <div class="resi-number">
            {{ $order->no_resi ?? '-' }}
        </div>
    </div>

    @if($order->no_resi)
    <div class="resi-barcode">
        <img src="{{ $resiBarcode }}" class="barcode-img" alt="resi">
        <div class="ref-number">{{ $order->no_resi }}</div>
    </div>
    @endif

    <div class="terms">
        Syarat dan ketentuan pengiriman dapat dilihat pada website www.jne.co.id
    </div>

    {{-- PRODUCT --}}
    <table class="product-table">
        <thead>
        <tr>
            <th>Product Name</th>
            <th>SKU</th>
            <th>Seller SKU</th>
            <th>Qty</th>
        </tr>
        </thead>

        <tbody>
        @foreach($order->items as $item)
            <tr>
                <td>{{ $item['name'] ?? '-' }}</td>
                <td>{{ $item['sku'] ?? 'Default' }}</td>
                <td>{{ $item['seller_sku'] ?? '' }}</td>
                <td style="text-align:center">{{ $item['quantity'] ?? 1 }}</td>
            </tr>
        @endforeach
        </tbody>

        <tfoot>
        <tr>
            <td colspan="3" class="qty-total-cell">Qty Total:</td>
            <td style="text-align:center;font-weight:bold;">
                {{ $order->total_items }}
            </td>
        </tr>
        </tfoot>
    </table>

    <div class="footer">
        In transit by: {{ $shipDate }} {{ now()->format('H:i') }}
    </div>

</div>

</body>
</html>

