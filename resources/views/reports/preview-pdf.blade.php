<?php
    $defaultDesign = ['primary_color'=>'#1a5276','accent_color'=>'#d6eaf8','abnormal_color'=>'#c0392b','normal_color'=>'#1e8449','font_size'=>'11','header_font_size'=>'22','logo_position'=>'center','logo_max_height'=>'60','header_border_style'=>'double','header_border_width'=>'3','lab_name_uppercase'=>true,'show_header_subtitle'=>true,'show_contact_info'=>true,'show_patient_section'=>true,'show_order_section'=>true,'show_results_heading'=>true,'show_test_method'=>true,'show_status_badge'=>true,'show_alternating_rows'=>true,'table_border_style'=>'solid','table_border_color'=>'#cccccc','section_heading_bg'=>'#1a5276','section_heading_color'=>'#ffffff','test_header_bg'=>'#eaf2f8','test_header_color'=>'#1a5276','signature_position'=>'right','show_report_number_footer'=>true,'show_generated_date'=>true,'footer_border_style'=>'solid','footer_border_width'=>'2','page_margin_top'=>'15','page_margin_bottom'=>'20','page_margin_sides'=>'12'];
    $ds = $defaultDesign;
    if (!empty($labSettings['report_design'])) { $p = json_decode($labSettings['report_design'], true); if (is_array($p)) $ds = array_merge($defaultDesign, $p); }
    $fs=(int)$ds['font_size']; $hfs=(int)$ds['header_font_size']; $pc=$ds['primary_color']; $ac=$ds['accent_color']; $tbc=$ds['table_border_color']; $thbg=$ds['test_header_bg']; $thc=$ds['test_header_color']; $abnc=$ds['abnormal_color']; $norc=$ds['normal_color'];
    $labNameTransform = $ds['lab_name_uppercase'] ? 'text-transform:uppercase;' : '';
    $altRowCss = $ds['show_alternating_rows'] ? ".rtable tbody tr:nth-child(even){background:#f8f9fa;}" : '';
    $footerBorderCss = $ds['footer_border_style']!=='none' ? "border-top:{$ds['footer_border_width']}px {$ds['footer_border_style']} {$pc};" : '';
    $logoPath=$labSettings['lab_logo']??null; $logoFullPath=$logoPath?storage_path('app/public/'.$logoPath):null;
    $sigPath=$labSettings['doctor_signature']??null; $sigFullPath=$sigPath?storage_path('app/public/'.$sigPath):null;
    $doctorName=$labSettings['doctor_name']??null; $doctorQual=$labSettings['doctor_qualification']??null;
    $techSigPath=$labSettings['technician_signature']??null; $techSigFull=$techSigPath?storage_path('app/public/'.$techSigPath):null;
    $techName=$labSettings['technician_name']??null;
    $appSigPath=$labSettings['approver_signature']??null; $appSigFull=$appSigPath?storage_path('app/public/'.$appSigPath):null;
    $appName=$labSettings['approver_name']??null; $appQual=$labSettings['approver_qualification']??null;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Sample Report Preview</title>
<style>
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'DejaVu Sans',Arial,sans-serif;font-size:{{$fs}}px;color:#222;line-height:1.35;}
    @page{size:A4 portrait;margin:{{$ds['page_margin_top']}}mm {{$ds['page_margin_sides']}}mm {{$ds['page_margin_bottom']}}mm {{$ds['page_margin_sides']}}mm;}

    .hdr{width:100%;border-collapse:collapse;}
    .hdr td{vertical-align:middle;padding:10px 12px;}
    .hdr-bg{background:{{$pc}};}
    .hdr .name{font-size:{{$hfs-2}}px;font-weight:bold;color:#fff;{{$labNameTransform}}letter-spacing:0.5px;}
    .hdr .tagline{font-size:{{$fs-2}}px;color:rgba(255,255,255,0.8);margin-top:2px;}
    .hdr .contact{font-size:{{$fs-2}}px;color:rgba(255,255,255,0.85);text-align:right;line-height:1.6;}
    .addr-bar{background:{{$thbg}};padding:4px 12px;font-size:{{$fs-2}}px;color:#444;text-align:center;border-bottom:2px solid {{$pc}};}

    .info-grid{width:100%;border-collapse:collapse;margin:10px 0;border:1px solid #ddd;}
    .info-grid td{padding:4px 10px;font-size:{{$fs-0.5}}px;border:1px solid #eee;}
    .info-grid .hd{background:{{$pc}};color:#fff;font-weight:bold;font-size:{{$fs}}px;text-transform:uppercase;letter-spacing:0.5px;padding:5px 10px;}
    .info-grid .lbl{color:#666;font-size:{{$fs-1.5}}px;}
    .info-grid .val{color:#111;font-weight:500;}

    .ttitle{background:{{$pc}};color:#fff;font-size:{{$fs+1}}px;font-weight:bold;text-align:center;padding:6px 10px;margin-top:14px;text-transform:uppercase;}
    .tsample{font-size:{{$fs-1}}px;color:#555;padding:4px 10px;background:#f9f9f9;border:1px solid #eee;border-top:none;}

    .rtable{width:100%;border-collapse:collapse;margin-bottom:4px;}
    .rtable th{background:{{$ac}};color:{{$pc}};font-size:{{$fs-1}}px;font-weight:bold;text-transform:uppercase;padding:5px 8px;border:1px solid {{$tbc}};text-align:left;}
    .rtable td{padding:4px 8px;border:1px solid {{$tbc}};font-size:{{$fs-0.5}}px;}
    {!!$altRowCss!!}
    .abn{color:{{$abnc}};font-weight:bold;}
    .hl{color:{{$abnc}};font-size:{{$fs-2}}px;font-weight:bold;margin-left:3px;}
    .inst{font-size:{{$fs-2}}px;color:#666;padding:3px 10px;}
    .endmark{text-align:center;font-size:{{$fs-2}}px;color:#bbb;margin:12px 0 6px;letter-spacing:3px;}

    .ftr{ {{$footerBorderCss}} padding-top:8px;margin-top:20px;page-break-inside:avoid;}
    .ftr-meta{font-size:{{$fs-2}}px;color:#999;margin-bottom:10px;text-align:center;}
    .sig-tbl{width:100%;border-collapse:collapse;}
    .sig-tbl td{text-align:center;vertical-align:bottom;padding:0 6px;width:33%;}
    .sig-ln{border-top:1px solid #555;padding-top:3px;display:inline-block;min-width:130px;}
    .sig-nm{font-size:{{$fs-1}}px;font-weight:bold;color:{{$pc}};}
    .sig-ql{font-size:{{$fs-2}}px;color:#666;}
    .disc{text-align:center;margin-top:8px;font-size:{{$fs-2.5}}px;color:#aaa;font-style:italic;}

    .watermark{position:fixed;top:40%;left:15%;font-size:70px;color:rgba(26,82,118,0.06);font-weight:bold;text-transform:uppercase;transform:rotate(-35deg);letter-spacing:15px;}
</style>
</head>
<body>

<div class="watermark">SAMPLE</div>

<table class="hdr hdr-bg">
<tr>
    <td style="width:65px;">
        @if($logoFullPath && file_exists($logoFullPath))
            <img src="{{$logoFullPath}}" style="max-height:{{$ds['logo_max_height']-10}}px;max-width:55px;">
        @else
            <div style="width:45px;height:45px;background:rgba(255,255,255,0.15);border-radius:8px;text-align:center;line-height:45px;font-size:20px;font-weight:bold;color:#fff;">{{substr($labSettings['lab_name']??'P',0,1)}}</div>
        @endif
    </td>
    <td>
        <div class="name">{{$labSettings['lab_name']??'Pathology Laboratory'}}</div>
        @if($ds['show_header_subtitle'])<div class="tagline">{{!empty($labSettings['report_header_text'])?$labSettings['report_header_text']:'Accurate | Caring | Instant'}}</div>@endif
    </td>
    @if($ds['show_contact_info'])
    <td class="contact">@if(!empty($labSettings['lab_phone'])){{$labSettings['lab_phone']}}<br>@endif @if(!empty($labSettings['lab_email'])){{$labSettings['lab_email']}}@endif</td>
    @endif
</tr>
</table>
@if(!empty($labSettings['lab_address']))<div class="addr-bar">{{$labSettings['lab_address']}}</div>@endif

<table class="info-grid">
<tr><td colspan="4" class="hd">Patient Information</td></tr>
<tr>
    <td style="width:25%"><div class="lbl">Patient Name</div><div class="val">Rajesh Kumar</div></td>
    <td style="width:25%"><div class="lbl">Age / Gender</div><div class="val">35 Yrs / Male</div></td>
    <td style="width:25%"><div class="lbl">Patient ID</div><div class="val">PL-0042</div></td>
    <td style="width:25%"><div class="lbl">Phone</div><div class="val">9811001001</div></td>
</tr>
</table>

<table class="info-grid">
<tr><td colspan="4" class="hd">Report Details</td></tr>
<tr>
    <td style="width:25%"><div class="lbl">Order No</div><div class="val">ORD-{{now()->format('Ymd')}}-0001</div></td>
    <td style="width:25%"><div class="lbl">Report No</div><div class="val">RPT-{{now()->format('Ymd')}}-0001</div></td>
    <td style="width:25%"><div class="lbl">Ref. Doctor</div><div class="val">Dr. Sharma</div></td>
    <td style="width:25%"><div class="lbl">Report Date</div><div class="val">{{now()->format('d M Y, h:i A')}}</div></td>
</tr>
</table>

<div class="ttitle">Complete Blood Count (CBC)</div>
<div class="tsample"><strong>Sample:</strong> EDTA Blood &nbsp; <strong>Method:</strong> Automated Hematology Analyzer</div>
<table class="rtable">
<thead><tr><th style="width:35%;">Investigation</th><th style="width:20%;">Result</th><th style="width:25%;">Reference Range</th><th style="width:20%;">Unit</th></tr></thead>
<tbody>
<tr><td style="font-weight:500;">Hemoglobin</td><td class="abn">12.5 <span class="hl">Low</span></td><td>13.0 - 17.0</td><td>g/dL</td></tr>
<tr><td style="font-weight:500;">RBC Count</td><td>4.8</td><td>4.5 - 5.5</td><td>mill/cumm</td></tr>
<tr><td style="font-weight:500;">WBC Count</td><td>7200</td><td>4000 - 11000</td><td>/cumm</td></tr>
<tr><td style="font-weight:500;">Platelet Count</td><td>2.5</td><td>1.5 - 4.0</td><td>Lakh/cumm</td></tr>
<tr><td style="font-weight:500;">PCV</td><td class="abn">38.2 <span class="hl">Low</span></td><td>40.0 - 50.0</td><td>%</td></tr>
</tbody>
</table>

<div class="ttitle">Blood Sugar Fasting</div>
<div class="tsample"><strong>Sample:</strong> Fluoride Blood &nbsp; <strong>Method:</strong> GOD-POD</div>
<table class="rtable">
<thead><tr><th style="width:35%;">Investigation</th><th style="width:20%;">Result</th><th style="width:25%;">Reference Range</th><th style="width:20%;">Unit</th></tr></thead>
<tbody>
<tr><td style="font-weight:500;">Blood Sugar (Fasting)</td><td class="abn">110 <span class="hl">High</span></td><td>70 - 100</td><td>mg/dL</td></tr>
</tbody>
</table>

<div class="endmark">--- End of Report ---</div>

<div class="ftr">
    <div class="ftr-meta">Report: <strong>RPT-{{now()->format('Ymd')}}-0001</strong> &nbsp;|&nbsp; Order: <strong>ORD-{{now()->format('Ymd')}}-0001</strong> &nbsp;|&nbsp; Generated: {{now()->format('d M Y, h:i A')}}</div>
    <table class="sig-tbl">
    <tr>
        @php $showTech=$techName||($techSigFull&&file_exists($techSigFull)); $showDoc=$doctorName||($sigFullPath&&file_exists($sigFullPath)); $showApp=$appName||($appSigFull&&file_exists($appSigFull)); $sc=($showTech?1:0)+($showDoc?1:0)+($showApp?1:0); $sw=$sc>0?(int)(100/$sc):33; @endphp
        @if($showTech)<td style="width:{{$sw}}%;">@if($techSigFull&&file_exists($techSigFull))<div style="margin-bottom:2px;"><img src="{{$techSigFull}}" style="max-height:40px;max-width:130px;"></div>@else<div style="height:40px;"></div>@endif<div class="sig-ln"><div class="sig-nm">{{$techName??'Lab Technician'}}</div></div></td>@endif
        @if($showDoc)<td style="width:{{$sw}}%;">@if($sigFullPath&&file_exists($sigFullPath))<div style="margin-bottom:2px;"><img src="{{$sigFullPath}}" style="max-height:40px;max-width:130px;"></div>@else<div style="height:40px;"></div>@endif<div class="sig-ln"><div class="sig-nm">{{$doctorName}}</div>@if($doctorQual)<div class="sig-ql">({{$doctorQual}})</div>@endif</div></td>@endif
        @if($showApp)<td style="width:{{$sw}}%;">@if($appSigFull&&file_exists($appSigFull))<div style="margin-bottom:2px;"><img src="{{$appSigFull}}" style="max-height:40px;max-width:130px;"></div>@else<div style="height:40px;"></div>@endif<div class="sig-ln">@if($appName)<div class="sig-nm">{{$appName}}</div>@if($appQual)<div class="sig-ql">({{$appQual}})</div>@endif @else<div class="sig-nm">Authorized Signatory</div>@endif</div></td>@endif
    </tr>
    </table>
    <div class="disc">{{!empty($labSettings['report_footer_text'])?$labSettings['report_footer_text']:'This is a computer-generated report. Please correlate clinically.'}}</div>
</div>

</body>
</html>
