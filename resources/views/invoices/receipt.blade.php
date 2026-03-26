<?php
    $defaultDesign = ['primary_color'=>'#1a5276','accent_color'=>'#d6eaf8','abnormal_color'=>'#c0392b','normal_color'=>'#1e8449','font_size'=>'11','header_font_size'=>'22','logo_position'=>'center','logo_max_height'=>'60','header_border_style'=>'double','header_border_width'=>'3','lab_name_uppercase'=>true,'show_header_subtitle'=>true,'show_contact_info'=>true,'table_border_color'=>'#cccccc','test_header_bg'=>'#eaf2f8','footer_border_style'=>'solid','footer_border_width'=>'2'];
    $ds = $defaultDesign;
    if (!empty($labSettings['report_design'])) { $p = json_decode($labSettings['report_design'], true); if (is_array($p)) $ds = array_merge($defaultDesign, $p); }
    $pc=$ds['primary_color']; $thbg=$ds['test_header_bg'];
    $labNameTransform = $ds['lab_name_uppercase'] ? 'text-transform:uppercase;' : '';
    $footerBorderCss = $ds['footer_border_style']!=='none' ? "border-top:{$ds['footer_border_width']}px {$ds['footer_border_style']} {$pc};" : '';
    $logoPath=$labSettings['lab_logo']??null; $logoFullPath=$logoPath?storage_path('app/public/'.$logoPath):null;
    $sigPath=$labSettings['doctor_signature']??null; $sigFullPath=$sigPath?storage_path('app/public/'.$sigPath):null;
    $doctorName=$labSettings['doctor_name']??null; $doctorQual=$labSettings['doctor_qualification']??null;
    $techSigPath=$labSettings['technician_signature']??null; $techSigFull=$techSigPath?storage_path('app/public/'.$techSigPath):null;
    $techName=$labSettings['technician_name']??null;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Receipt - {{$order->order_number}}</title>
<style>
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:DejaVu Sans,Arial,sans-serif;font-size:11px;color:#333;padding:8px;}

    .hdr{background:{{$pc}};color:#fff;padding:8px 10px;text-align:center;}
    .hdr .name{font-size:14px;font-weight:bold;{{$labNameTransform}}letter-spacing:0.5px;}
    .hdr .tag{font-size:8px;opacity:0.8;margin-top:1px;}
    .addr{background:{{$thbg}};padding:3px 8px;font-size:8px;color:#444;text-align:center;border-bottom:1.5px solid {{$pc}};margin-bottom:8px;}
    .title{text-align:center;font-size:11px;font-weight:bold;color:{{$pc}};text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;padding:3px 0;border:1px solid {{$pc}};}
    .sec{margin-bottom:8px;border-bottom:1px dashed #ccc;padding-bottom:6px;}
    .row{display:block;margin-bottom:2px;font-size:10px;}
    .lbl{font-weight:bold;display:inline-block;width:75px;color:#444;}
    table{width:100%;border-collapse:collapse;margin-bottom:6px;}
    table th{background:{{$pc}};color:#fff;padding:3px 5px;font-size:9px;text-align:left;}
    table th:last-child{text-align:right;}
    table td{padding:3px 5px;font-size:10px;border-bottom:1px dotted #ddd;}
    table td:last-child{text-align:right;}
    .totals{border-top:1px dashed #999;padding-top:4px;margin-top:4px;}
    .tr{display:block;overflow:hidden;margin-bottom:2px;font-size:10px;}
    .tl{float:left;}.tv{float:right;}
    .tr.net{font-weight:bold;font-size:12px;border-top:2px solid {{$pc}};padding-top:3px;margin-top:3px;color:{{$pc}};}
    .tr.bal{font-weight:bold;color:#c0392b;}
    .ftr{ {{$footerBorderCss}} padding-top:8px;margin-top:10px;page-break-inside:avoid;}
    .sig-ln{border-top:1px solid #555;padding-top:3px;display:inline-block;min-width:80px;text-align:center;}
    .sig-nm{font-size:9px;font-weight:bold;color:{{$pc}};}
    .sig-ql{font-size:7px;color:#555;}
    .disc{text-align:center;margin-top:6px;font-size:8px;color:#888;font-style:italic;}
</style>
</head>
<body>

<div class="hdr">
    @if($logoFullPath && file_exists($logoFullPath))<div style="margin-bottom:3px;"><img src="{{$logoFullPath}}" style="max-height:30px;max-width:100px;"></div>@endif
    <div class="name">{{$labSettings['lab_name']}}</div>
    @if(!empty($labSettings['report_header_text']))<div class="tag">{{$labSettings['report_header_text']}}</div>@endif
</div>
@if(!empty($labSettings['lab_address']))<div class="addr">{{$labSettings['lab_address']}} @if(!empty($labSettings['lab_phone'])) | Ph: {{$labSettings['lab_phone']}}@endif</div>@endif

<div class="title">Receipt / Invoice</div>

<div class="sec">
    <div class="row"><span class="lbl">Patient:</span> {{$order->patient->name??'N/A'}}</div>
    @if($order->patient?->patient_id)<div class="row"><span class="lbl">Patient ID:</span> {{$order->patient->patient_id}}</div>@endif
    @if($order->patient?->phone)<div class="row"><span class="lbl">Phone:</span> {{$order->patient->phone}}</div>@endif
</div>
<div class="sec">
    <div class="row"><span class="lbl">Order #:</span> {{$order->order_number}}</div>
    <div class="row"><span class="lbl">Date:</span> {{$order->created_at->format('d/m/Y h:i A')}}</div>
    @if($order->referringDoctor)<div class="row"><span class="lbl">Ref. Doctor:</span> Dr. {{$order->referringDoctor->name}}</div>@endif
</div>

<table>
<thead><tr><th>#</th><th>Test</th><th style="text-align:right;">₹</th></tr></thead>
<tbody>
@foreach($order->orderTests as $i=>$ot)
<tr><td>{{$i+1}}</td><td>{{$ot->test->name??'-'}}</td><td>{{number_format($ot->price,2)}}</td></tr>
@endforeach
</tbody>
</table>

<div class="totals">
    <div class="tr"><span class="tl">Subtotal:</span><span class="tv">₹{{number_format($order->total_amount,2)}}</span></div>
    @if($order->discount>0)<div class="tr"><span class="tl">Discount:</span><span class="tv">- ₹{{number_format($order->discount,2)}}</span></div>@endif
    <div class="tr net"><span class="tl">Net Amount:</span><span class="tv">₹{{number_format($order->net_amount,2)}}</span></div>
    <div class="tr"><span class="tl">Paid:</span><span class="tv">₹{{number_format($order->amount_paid,2)}}</span></div>
    @php $bal=$order->net_amount-$order->amount_paid; @endphp
    @if($bal>0)<div class="tr bal"><span class="tl">Balance:</span><span class="tv">₹{{number_format($bal,2)}}</span></div>@endif
    @if($order->payment_mode)<div class="tr" style="margin-top:4px;"><span class="tl">Mode:</span><span class="tv" style="text-transform:uppercase;">{{$order->payment_mode}}</span></div>@endif
</div>

<div class="ftr">
    <table style="width:100%;"><tr>
        <td style="width:50%;text-align:left;vertical-align:bottom;font-size:9px;">
            <div style="color:#888;">Thank you!</div>
            <div style="color:{{$pc}};font-weight:bold;">{{$labSettings['lab_name']}}</div>
        </td>
        <td style="width:50%;text-align:right;vertical-align:bottom;">
            <div style="display:inline-block;text-align:center;">
                @if($sigFullPath && file_exists($sigFullPath))<div style="margin-bottom:2px;"><img src="{{$sigFullPath}}" style="max-height:25px;max-width:80px;"></div>@else<div style="height:25px;"></div>@endif
                <div class="sig-ln">@if($doctorName)<div class="sig-nm">{{$doctorName}}</div>@if($doctorQual)<div class="sig-ql">({{$doctorQual}})</div>@endif @else<div class="sig-nm">Auth. Signatory</div>@endif</div>
            </div>
        </td>
    </tr></table>
    <div class="disc">{{!empty($labSettings['report_footer_text'])?$labSettings['report_footer_text']:'Computer-generated receipt.'}}</div>
</div>

</body>
</html>
