<?php

namespace App\Http\Controllers;

use App\Models\LabSetting;
use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicReportController extends Controller
{
    public function lookup(Request $request)
    {
        $query = trim($request->query('q', ''));

        if (!$query) {
            return redirect('/')->with('error', 'Please enter a report number.');
        }

        // Try finding by report number (e.g. RPT-20260319-0001)
        $report = Report::where('report_number', $query)->first();

        // If not found, try as a token directly
        if (!$report) {
            $report = Report::where('public_token', $query)->first();
        }

        if (!$report || !$report->public_token) {
            return Inertia::render('Reports/NotFound', [
                'query' => $query,
            ]);
        }

        return redirect("/report/{$report->public_token}");
    }

    public function show(string $token): Response
    {
        $report = Report::where('public_token', $token)->firstOrFail();

        $report->load([
            'order.patient',
            'order.orderTests.test',
            'order.orderTests.results.parameter.ranges',
            'approvedBy',
        ]);

        $labSettings = [
            'lab_name' => LabSetting::get('lab_name', 'PathLab'),
            'lab_phone' => LabSetting::get('lab_phone', ''),
            'lab_email' => LabSetting::get('lab_email', ''),
            'lab_address' => LabSetting::get('lab_address', ''),
            'lab_logo' => LabSetting::get('lab_logo', ''),
        ];

        return Inertia::render('Reports/PublicShow', [
            'report' => $report,
            'labSettings' => $labSettings,
            'tokenValid' => $report->isTokenValid(),
        ]);
    }

    public function print(string $token)
    {
        return $this->buildPdf($token)->stream();
    }

    public function download(string $token)
    {
        $pdf = $this->buildPdf($token);
        $report = Report::where('public_token', $token)->firstOrFail();
        return $pdf->download($report->report_number . '.pdf');
    }

    private function buildPdf(string $token)
    {
        $report = Report::where('public_token', $token)->firstOrFail();

        if (! $report->isTokenValid()) {
            abort(403, 'This report link has expired.');
        }

        $report->load([
            'order.patient',
            'order.referringDoctor',
            'order.orderTests' => function ($query) {
                $query->where('status', 'approved');
            },
            'order.orderTests.test.category',
            'order.orderTests.test.parameters',
            'order.orderTests.results.parameter.ranges',
            'approvedBy',
        ]);

        $labSettings = [
            'lab_name' => LabSetting::get('lab_name', 'PathLab'),
            'lab_phone' => LabSetting::get('lab_phone', ''),
            'lab_email' => LabSetting::get('lab_email', ''),
            'lab_address' => LabSetting::get('lab_address', ''),
            'lab_logo' => LabSetting::get('lab_logo', ''),
            'doctor_signature' => LabSetting::get('doctor_signature', ''),
            'doctor_name' => LabSetting::get('doctor_name', ''),
            'doctor_qualification' => LabSetting::get('doctor_qualification', ''),
            'technician_signature' => LabSetting::get('technician_signature', ''),
            'technician_name' => LabSetting::get('technician_name', ''),
            'approver_signature' => LabSetting::get('approver_signature', ''),
            'approver_name' => LabSetting::get('approver_name', ''),
            'approver_qualification' => LabSetting::get('approver_qualification', ''),
            'report_header_text' => LabSetting::get('report_header_text', ''),
            'report_footer_text' => LabSetting::get('report_footer_text', ''),
            'report_design' => LabSetting::get('report_design', ''),
        ];

        return Pdf::loadView('reports.pdf', [
            'report' => $report,
            'order' => $report->order,
            'patient' => $report->order->patient,
            'orderTests' => $report->order->orderTests,
            'labSettings' => $labSettings,
        ]);
    }
}
