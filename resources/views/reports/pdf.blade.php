<?php
    $defaultDesign = ['primary_color'=>'#1a5276','accent_color'=>'#d6eaf8','abnormal_color'=>'#c0392b','normal_color'=>'#1e8449','font_size'=>'11','header_font_size'=>'22','logo_position'=>'center','logo_max_height'=>'60','header_border_style'=>'double','header_border_width'=>'3','lab_name_uppercase'=>true,'show_header_subtitle'=>true,'show_contact_info'=>true,'show_patient_section'=>true,'show_order_section'=>true,'show_results_heading'=>true,'show_test_method'=>true,'show_status_badge'=>true,'show_alternating_rows'=>true,'table_border_style'=>'solid','table_border_color'=>'#cccccc','section_heading_bg'=>'#1a5276','section_heading_color'=>'#ffffff','test_header_bg'=>'#eaf2f8','test_header_color'=>'#1a5276','signature_position'=>'right','show_report_number_footer'=>true,'show_generated_date'=>true,'footer_border_style'=>'solid','footer_border_width'=>'2','page_margin_top'=>'15','page_margin_bottom'=>'20','page_margin_sides'=>'12'];
    $ds = $defaultDesign;
    if (!empty($labSettings['report_design'])) { $p = json_decode($labSettings['report_design'], true); if (is_array($p)) $ds = array_merge($defaultDesign, $p); }
    $fs=(int)$ds['font_size']; $hfs=(int)$ds['header_font_size']; $pc=$ds['primary_color']; $ac=$ds['accent_color']; $tbc=$ds['table_border_color']; $tbs=$ds['table_border_style']; $thbg=$ds['test_header_bg']; $thc=$ds['test_header_color']; $abnc=$ds['abnormal_color']; $norc=$ds['normal_color'];
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
    $patientGender = strtolower($order->patient->gender ?? '');
    $patientAge = $order->patient->age ?? null;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>{{ $report->report_number ?? 'Report' }}</title>
<style>
    *{margin:0;padding:0;box-sizing:border-box;}
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'DejaVu Sans',Arial,sans-serif;font-size:{{$fs}}px;color:#222;line-height:1.35;}
    @page{size:A4 portrait;margin:{{$ds['page_margin_top']}}mm {{$ds['page_margin_sides']}}mm {{$ds['page_margin_bottom']}}mm {{$ds['page_margin_sides']}}mm;}

    /* ─── HEADER ─── */
    .hdr{width:100%;border-collapse:collapse;margin-bottom:0;}
    .hdr td{vertical-align:middle;padding:10px 12px;}
    .hdr-bg{background:{{$pc}};}
    .hdr .logo-cell{width:65px;}
    .hdr .name{font-size:{{$hfs-2}}px;font-weight:bold;color:#fff;{{$labNameTransform}}letter-spacing:0.5px;}
    .hdr .tagline{font-size:{{$fs-2}}px;color:rgba(255,255,255,0.8);margin-top:2px;}
    .hdr .contact{font-size:{{$fs-2}}px;color:rgba(255,255,255,0.85);text-align:right;line-height:1.6;}
    .addr-bar{background:{{$thbg}};padding:4px 12px;font-size:{{$fs-2}}px;color:#444;text-align:center;border-bottom:2px solid {{$pc}};}

    /* ─── PATIENT / ORDER ─── */
    .info-grid{width:100%;border-collapse:collapse;margin:10px 0;border:1px solid #ddd;}
    .info-grid td{padding:4px 10px;font-size:{{$fs-0.5}}px;border:1px solid #eee;vertical-align:top;}
    .info-grid .hd{background:{{$pc}};color:#fff;font-weight:bold;font-size:{{$fs}}px;text-transform:uppercase;letter-spacing:0.5px;padding:5px 10px;}
    .info-grid .lbl{color:#666;font-size:{{$fs-1.5}}px;margin-bottom:1px;}
    .info-grid .val{color:#111;font-weight:500;}

    /* ─── TEST TITLE ─── */
    .ttitle{background:{{$pc}};color:#fff;font-size:{{$fs+1}}px;font-weight:bold;text-align:center;padding:6px 10px;margin-top:14px;letter-spacing:0.5px;text-transform:uppercase;}
    .tsample{font-size:{{$fs-1}}px;color:#555;padding:4px 10px;background:#f9f9f9;border:1px solid #eee;border-top:none;}

    /* ─── RESULTS TABLE ─── */
    .rtable{width:100%;border-collapse:collapse;margin-bottom:4px;}
    .rtable th{background:{{$ac}};color:{{$pc}};font-size:{{$fs-1}}px;font-weight:bold;text-transform:uppercase;padding:5px 8px;border:1px solid {{$tbc}};text-align:left;}
    .rtable td{padding:4px 8px;border:1px solid {{$tbc}};font-size:{{$fs-0.5}}px;}
    {!!$altRowCss!!}
    .abn{color:{{$abnc}};font-weight:bold;}
    .nor{color:#222;}
    .hl{color:{{$abnc}};font-size:{{$fs-2}}px;font-weight:bold;margin-left:3px;}

    /* ─── INSTRUMENT / REMARKS ─── */
    .inst{font-size:{{$fs-2}}px;color:#666;padding:3px 10px;}
    .rmk{font-size:{{$fs-1}}px;padding:5px 10px;background:#fffbeb;border-left:3px solid #f59e0b;margin:4px 0;}

    /* ─── FOOTER ─── */
    .endmark{text-align:center;font-size:{{$fs-2}}px;color:#bbb;margin:12px 0 6px;letter-spacing:3px;}
    .ftr-meta{font-size:{{$fs-2}}px;color:#999;margin-bottom:10px;text-align:center;}
    .sig-tbl{width:100%;border-collapse:collapse;}
    .sig-tbl td{text-align:center;vertical-align:bottom;padding:0 6px;width:33%;}
    .sig-space{height:40px;}
    .sig-ln{border-top:1px solid #555;padding-top:3px;display:inline-block;min-width:130px;}
    .sig-nm{font-size:{{$fs-1}}px;font-weight:bold;color:{{$pc}};}
    .sig-ql{font-size:{{$fs-2}}px;color:#666;font-weight:normal;}
    .disc{text-align:center;margin-top:8px;font-size:{{$fs-2.5}}px;color:#aaa;font-style:italic;}

    .ftr{ {{$footerBorderCss}} padding-top:8px;margin-top:20px;page-break-inside:avoid;}
</style>
</head>
<body>

{{-- ═══════════ HEADER ═══════════ --}}
<table class="hdr hdr-bg">
<tr>
    <td class="logo-cell">
        @if($logoFullPath && file_exists($logoFullPath))
            <img src="{{$logoFullPath}}" style="max-height:{{$ds['logo_max_height']-10}}px;max-width:55px;">
        @else
            <div style="width:45px;height:45px;background:rgba(255,255,255,0.15);border-radius:8px;text-align:center;line-height:45px;font-size:20px;font-weight:bold;color:#fff;">{{substr($labSettings['lab_name']??'P',0,1)}}</div>
        @endif
    </td>
    <td>
        <div class="name">{{$labSettings['lab_name']??'Pathology Laboratory'}}</div>
        @if($ds['show_header_subtitle'])
        <div class="tagline">{{!empty($labSettings['report_header_text'])?$labSettings['report_header_text']:'Accurate | Caring | Instant'}}</div>
        @endif
    </td>
    @if($ds['show_contact_info'])
    <td class="contact">
        @if(!empty($labSettings['lab_phone'])){{$labSettings['lab_phone']}}<br>@endif
        @if(!empty($labSettings['lab_email'])){{$labSettings['lab_email']}}@endif
    </td>
    @endif
</tr>
</table>
@if(!empty($labSettings['lab_address']))
<div class="addr-bar">{{$labSettings['lab_address']}}</div>
@endif

{{-- ═══════════ PATIENT INFO ═══════════ --}}
@if($ds['show_patient_section'])
<table class="info-grid">
<tr><td colspan="4" class="hd">Patient Information</td></tr>
<tr>
    <td style="width:25%"><div class="lbl">Patient Name</div><div class="val">{{$order->patient->name??'—'}}</div></td>
    <td style="width:25%"><div class="lbl">Age / Gender</div><div class="val">{{$order->patient->age??'—'}} {{ucfirst($order->patient->age_unit??'yrs')}} / {{$order->patient->gender?ucfirst($order->patient->gender):'—'}}</div></td>
    <td style="width:25%"><div class="lbl">Patient ID</div><div class="val">{{$order->patient->patient_id??'—'}}</div></td>
    <td style="width:25%"><div class="lbl">Phone</div><div class="val">{{$order->patient->phone??'—'}}</div></td>
</tr>
</table>
@endif

{{-- ═══════════ ORDER INFO ═══════════ --}}
@if($ds['show_order_section'])
<table class="info-grid">
<tr><td colspan="4" class="hd">Report Details</td></tr>
<tr>
    <td style="width:25%"><div class="lbl">Order No</div><div class="val">{{$order->order_number??'—'}}</div></td>
    <td style="width:25%"><div class="lbl">Report No</div><div class="val">{{$report->report_number??'—'}}</div></td>
    <td style="width:25%"><div class="lbl">Ref. Doctor</div><div class="val">@if($order->referringDoctor)Dr. {{$order->referringDoctor->name}}@else Self @endif</div></td>
    <td style="width:25%"><div class="lbl">Report Date</div><div class="val">{{$report->created_at?$report->created_at->format('d M Y, h:i A'):now()->format('d M Y, h:i A')}}</div></td>
</tr>
</table>
@endif

{{-- ═══════════ TEST RESULTS ═══════════ --}}
@forelse($order->orderTests as $orderTest)
@php
    $test = $orderTest->test;
    $parameters = $test->parameters ?? collect();
    $results = $orderTest->results ?? collect();
@endphp

<div class="ttitle">{{$test->name??'—'}}</div>
<div class="tsample">
    @if($test->sample_type)<strong>Sample:</strong> {{$test->sample_type}} &nbsp; @endif
    @if($ds['show_test_method'] && $test->method)<strong>Method:</strong> {{$test->method}}@endif
</div>

@if($parameters->count())
<table class="rtable">
<thead>
<tr>
    <th style="width:35%;">Investigation</th>
    <th style="width:20%;">Result</th>
    <th style="width:25%;">Reference Range</th>
    <th style="width:20%;">Unit</th>
</tr>
</thead>
<tbody>
@foreach($parameters->where('is_active', true) as $param)
@php
    $result = $results->firstWhere('test_parameter_id', $param->id);
    $rv = $result->result_value ?? null;
    $ar = null; $ranges = $param->ranges ?? collect();
    if ($ranges->count()) {
        $ar = $ranges->filter(function($r) use($patientGender,$patientAge){
            $gm=!$r->range_group||strtolower($r->range_group)==='all'||strtolower($r->range_group)===$patientGender;
            $am=true;
            if(!is_null($patientAge)&&(!is_null($r->age_min)||!is_null($r->age_max))){
                $am=(!is_null($r->age_min)?$patientAge>=$r->age_min:true)&&(!is_null($r->age_max)?$patientAge<=$r->age_max:true);
            }
            return $gm&&$am;
        })->sortBy(fn($r)=>$patientGender&&strtolower($r->range_group)===$patientGender?0:(strtolower($r->range_group??'')==='all'?1:2))->first();
    }
    $rd = $ar ? ($ar->getDisplayRange()?:'—') : '—';
    $isAbn=false; $hl='';
    if($param->field_type==='numeric'&&is_numeric($rv)&&$ar){
        $nv=(float)$rv;
        if(!is_null($ar->min_value)&&$nv<(float)$ar->min_value){$isAbn=true;$hl='Low';}
        if(!is_null($ar->max_value)&&$nv>(float)$ar->max_value){$isAbn=true;$hl='High';}
    }
@endphp
<tr>
    <td style="font-weight:500;">{{$param->name}}</td>
    <td class="{{$isAbn?'abn':'nor'}}">{{$rv??'—'}}@if($hl)<span class="hl">{{$hl}}</span>@endif</td>
    <td>{{$rd}}</td>
    <td>{{$param->unit?:''}}</td>
</tr>
@endforeach
</tbody>
</table>
@endif

@if(!empty($orderTest->result_remarks))
<div class="rmk"><strong>Interpretation:</strong> {{$orderTest->result_remarks}}</div>
@endif

@empty
<div style="text-align:center;padding:30px;color:#999;">No test results available.</div>
@endforelse

@if(!empty($report->notes))
<div class="rmk"><strong>Notes:</strong> {{$report->notes}}</div>
@endif

{{-- ═══════════ END MARK ═══════════ --}}
<div class="endmark">--- End of Report ---</div>

{{-- ═══════════ FOOTER ═══════════ --}}
<div class="ftr">
    <div class="ftr-meta">
        @if($ds['show_report_number_footer'])Report: <strong>{{$report->report_number??'—'}}</strong> &nbsp;|&nbsp; @endif
        Order: <strong>{{$order->order_number??'—'}}</strong>
        @if($ds['show_generated_date']) &nbsp;|&nbsp; Generated: {{now()->format('d M Y, h:i A')}}@endif
    </div>

    @php
        $showTech = $techName || ($techSigFull && file_exists($techSigFull));
        $showDoc = $doctorName || ($sigFullPath && file_exists($sigFullPath));
        $showApp = $appName || ($appSigFull && file_exists($appSigFull)) || $report->approvedBy;
        $sigCount = ($showTech?1:0) + ($showDoc?1:0) + ($showApp?1:0);
        $sigWidth = $sigCount > 0 ? (int)(100/$sigCount) : 33;
    @endphp
    @if($sigCount > 0)
    <table class="sig-tbl">
    <tr>
        @if($showTech)
        <td style="width:{{$sigWidth}}%;">
            @if($techSigFull && file_exists($techSigFull))
            <div style="margin-bottom:2px;"><img src="{{$techSigFull}}" style="max-height:40px;max-width:130px;"></div>
            @else
            <div class="sig-space"></div>
            @endif
            <div class="sig-ln"><div class="sig-nm">{{$techName ?? 'Lab Technician'}}</div></div>
        </td>
        @endif
        @if($showDoc)
        <td style="width:{{$sigWidth}}%;">
            @if($sigFullPath && file_exists($sigFullPath))
            <div style="margin-bottom:2px;"><img src="{{$sigFullPath}}" style="max-height:40px;max-width:130px;"></div>
            @else
            <div class="sig-space"></div>
            @endif
            <div class="sig-ln">
                <div class="sig-nm">{{$doctorName}}</div>
                @if($doctorQual)<div class="sig-ql">({{$doctorQual}})</div>@endif
            </div>
        </td>
        @endif
        @if($showApp)
        <td style="width:{{$sigWidth}}%;">
            @if($appSigFull && file_exists($appSigFull))
            <div style="margin-bottom:2px;"><img src="{{$appSigFull}}" style="max-height:40px;max-width:130px;"></div>
            @else
            <div class="sig-space"></div>
            @endif
            <div class="sig-ln">
                @if($appName)
                <div class="sig-nm">{{$appName}}</div>
                @if($appQual)<div class="sig-ql">({{$appQual}})</div>@endif
                @elseif($report->approvedBy)
                <div class="sig-nm">Dr. {{$report->approvedBy->name}}</div>
                @endif
            </div>
        </td>
        @endif
    </tr>
    </table>
    @endif

    <div class="disc">{{!empty($labSettings['report_footer_text'])?$labSettings['report_footer_text']:'This is a computer-generated report. Please correlate clinically.'}}</div>
</div>

</body>
</html>
