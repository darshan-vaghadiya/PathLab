<?php
    $defaultDesign = [
        'primary_color' => '#1a5276', 'accent_color' => '#d6eaf8', 'abnormal_color' => '#c0392b', 'normal_color' => '#1e8449',
        'font_size' => '11', 'header_font_size' => '22', 'logo_position' => 'center', 'logo_max_height' => '60',
        'header_border_style' => 'double', 'header_border_width' => '3', 'lab_name_uppercase' => true,
        'show_header_subtitle' => true, 'show_contact_info' => true, 'show_patient_section' => true,
        'show_order_section' => true, 'show_results_heading' => true, 'show_test_method' => true,
        'show_status_badge' => true, 'show_alternating_rows' => true, 'table_border_style' => 'solid',
        'table_border_color' => '#cccccc', 'section_heading_bg' => '#1a5276', 'section_heading_color' => '#ffffff',
        'test_header_bg' => '#eaf2f8', 'test_header_color' => '#1a5276', 'signature_position' => 'right',
        'show_report_number_footer' => true, 'show_generated_date' => true,
        'footer_border_style' => 'solid', 'footer_border_width' => '2',
        'page_margin_top' => '15', 'page_margin_bottom' => '20', 'page_margin_sides' => '12',
    ];
    $ds = $defaultDesign;
    if (!empty($labSettings['report_design'])) {
        $parsed = json_decode($labSettings['report_design'], true);
        if (is_array($parsed)) { $ds = array_merge($defaultDesign, $parsed); }
    }

    $fs = (int) $ds['font_size'];
    $hfs = (int) $ds['header_font_size'];
    $pc = $ds['primary_color'];
    $ac = $ds['accent_color'];
    $tbc = $ds['table_border_color'];
    $tbs = $ds['table_border_style'];
    $shbg = $ds['section_heading_bg'];
    $shc = $ds['section_heading_color'];
    $thbg = $ds['test_header_bg'];
    $thc = $ds['test_header_color'];
    $abnc = $ds['abnormal_color'];
    $norc = $ds['normal_color'];

    $headerBorderCss = $ds['header_border_style'] !== 'none'
        ? "border-bottom: {$ds['header_border_width']}px {$ds['header_border_style']} {$pc};"
        : '';
    $labNameTransform = $ds['lab_name_uppercase'] ? 'text-transform: uppercase;' : '';
    $altRowCss = $ds['show_alternating_rows']
        ? ".results-table tbody tr:nth-child(even), .param-table tbody tr:nth-child(even) { background-color: #f9f9f9; }"
        : '';
    $footerBorderCss = $ds['footer_border_style'] !== 'none'
        ? "border-top: {$ds['footer_border_width']}px {$ds['footer_border_style']} {$pc};"
        : '';
    $sigAlign = $ds['signature_position'] === 'left' ? 'left' : ($ds['signature_position'] === 'center' ? 'center' : 'right');

    $logoPath = $labSettings['lab_logo'] ?? null;
    $logoFullPath = $logoPath ? storage_path('app/public/' . $logoPath) : null;
    $sigPath = $labSettings['doctor_signature'] ?? null;
    $sigFullPath = $sigPath ? storage_path('app/public/' . $sigPath) : null;
    $doctorName = $labSettings['doctor_name'] ?? null;
    $doctorQual = $labSettings['doctor_qualification'] ?? null;
?>
