<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $reports = Report::with(['order.patient', 'order.referringDoctor', 'approvedBy'])
            ->when($request->search, function ($query, $search) {
                $query->where('report_number', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($q) use ($search) {
                        $q->where('order_number', 'like', "%{$search}%")
                          ->orWhereHas('patient', function ($pq) use ($search) {
                              $pq->where('name', 'like', "%{$search}%");
                          });
                    });
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $reports->getCollection()->transform(function ($report) {
            $report->public_url = $report->getPublicUrl();
            return $report;
        });

        return Inertia::render('Reports/Index', [
            'reports' => $reports,
            'filters' => $request->only('search', 'date_from', 'date_to'),
        ]);
    }

    public function show(Report $report): Response
    {
        $report->load([
            'order.patient',
            'order.referringDoctor',
            'order.orderTests.test.category',
            'order.orderTests.test.parameters',
            'order.orderTests.results.parameter.ranges',
            'order.orderTests.approver',
            'approvedBy',
        ]);

        $report->public_url = $report->getPublicUrl();

        return Inertia::render('Reports/Show', [
            'report' => $report,
            'doctor' => $report->approvedBy,
        ]);
    }

    public function generatePdf(Report $report)
    {
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
            'lab_name' => \App\Models\LabSetting::get('lab_name', 'PathLab'),
            'lab_phone' => \App\Models\LabSetting::get('lab_phone', ''),
            'lab_email' => \App\Models\LabSetting::get('lab_email', ''),
            'lab_address' => \App\Models\LabSetting::get('lab_address', ''),
            'lab_logo' => \App\Models\LabSetting::get('lab_logo', ''),
            'doctor_signature' => \App\Models\LabSetting::get('doctor_signature', ''),
            'doctor_name' => \App\Models\LabSetting::get('doctor_name', ''),
            'doctor_qualification' => \App\Models\LabSetting::get('doctor_qualification', ''),
            'technician_signature' => \App\Models\LabSetting::get('technician_signature', ''),
            'technician_name' => \App\Models\LabSetting::get('technician_name', ''),
            'approver_signature' => \App\Models\LabSetting::get('approver_signature', ''),
            'approver_name' => \App\Models\LabSetting::get('approver_name', ''),
            'approver_qualification' => \App\Models\LabSetting::get('approver_qualification', ''),
            'report_header_text' => \App\Models\LabSetting::get('report_header_text', ''),
            'report_footer_text' => \App\Models\LabSetting::get('report_footer_text', ''),
            'report_design' => \App\Models\LabSetting::get('report_design', ''),
        ];

        $pdf = Pdf::loadView('reports.pdf', [
            'report' => $report,
            'order' => $report->order,
            'patient' => $report->order->patient,
            'orderTests' => $report->order->orderTests,
            'labSettings' => $labSettings,
        ]);

        return $pdf->stream($report->report_number . '.pdf');
    }

    public function download(Report $report)
    {
        // Always regenerate to ensure latest design
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
            'lab_name' => \App\Models\LabSetting::get('lab_name', 'PathLab'),
            'lab_phone' => \App\Models\LabSetting::get('lab_phone', ''),
            'lab_email' => \App\Models\LabSetting::get('lab_email', ''),
            'lab_address' => \App\Models\LabSetting::get('lab_address', ''),
            'lab_logo' => \App\Models\LabSetting::get('lab_logo', ''),
            'doctor_signature' => \App\Models\LabSetting::get('doctor_signature', ''),
            'doctor_name' => \App\Models\LabSetting::get('doctor_name', ''),
            'doctor_qualification' => \App\Models\LabSetting::get('doctor_qualification', ''),
            'technician_signature' => \App\Models\LabSetting::get('technician_signature', ''),
            'technician_name' => \App\Models\LabSetting::get('technician_name', ''),
            'approver_signature' => \App\Models\LabSetting::get('approver_signature', ''),
            'approver_name' => \App\Models\LabSetting::get('approver_name', ''),
            'approver_qualification' => \App\Models\LabSetting::get('approver_qualification', ''),
            'report_header_text' => \App\Models\LabSetting::get('report_header_text', ''),
            'report_footer_text' => \App\Models\LabSetting::get('report_footer_text', ''),
            'report_design' => \App\Models\LabSetting::get('report_design', ''),
        ];

        $pdf = Pdf::loadView('reports.pdf', [
            'report' => $report,
            'order' => $report->order,
            'patient' => $report->order->patient,
            'orderTests' => $report->order->orderTests,
            'labSettings' => $labSettings,
        ]);

        return $pdf->download($report->report_number . '.pdf');
    }

    public function sendSms(Report $report): RedirectResponse
    {
        $smsLog = app(SmsService::class)->sendReportReady($report);

        if (!$smsLog) {
            return back()->with('error', 'Unable to send SMS. Patient has no phone number.');
        }

        if ($smsLog->status === 'sent') {
            return back()->with('success', 'SMS sent successfully.');
        }

        return back()->with('error', 'SMS sending failed: ' . ($smsLog->gateway_response ?? 'Unknown error'));
    }
}
