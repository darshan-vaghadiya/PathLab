<?php

namespace App\Http\Controllers;

use App\Models\LabSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class LabSettingsController extends Controller
{
    public function index(): Response
    {
        $settings = [
            'lab_name' => LabSetting::get('lab_name', ''),
            'lab_phone' => LabSetting::get('lab_phone', ''),
            'lab_email' => LabSetting::get('lab_email', ''),
            'lab_address' => LabSetting::get('lab_address', ''),
            'lab_logo' => LabSetting::get('lab_logo', ''),
            'doctor_signature' => LabSetting::get('doctor_signature', ''),
            'doctor_name' => LabSetting::get('doctor_name', ''),
            'doctor_qualification' => LabSetting::get('doctor_qualification', ''),
            'report_header_text' => LabSetting::get('report_header_text', ''),
            'report_footer_text' => LabSetting::get('report_footer_text', ''),
            'report_design' => LabSetting::get('report_design', ''),
            'sms_enabled' => LabSetting::get('sms_enabled', '0'),
            'sms_gateway_url' => LabSetting::get('sms_gateway_url', ''),
            'sms_api_key' => LabSetting::get('sms_api_key', ''),
            'sms_sender_id' => LabSetting::get('sms_sender_id', ''),
            'sms_report_ready_template' => LabSetting::get('sms_report_ready_template', 'Dear {patient_name}, your lab report #{report_number} is ready. View at: {link} - {lab_name}'),
        ];

        // Add new signature fields
        $settings['technician_signature'] = LabSetting::get('technician_signature', '');
        $settings['technician_name'] = LabSetting::get('technician_name', '');
        $settings['approver_signature'] = LabSetting::get('approver_signature', '');
        $settings['approver_name'] = LabSetting::get('approver_name', '');
        $settings['approver_qualification'] = LabSetting::get('approver_qualification', '');

        // Generate URLs for images
        $settings['lab_logo_url'] = $settings['lab_logo'] ? Storage::url($settings['lab_logo']) : null;
        $settings['doctor_signature_url'] = $settings['doctor_signature'] ? Storage::url($settings['doctor_signature']) : null;
        $settings['technician_signature_url'] = $settings['technician_signature'] ? Storage::url($settings['technician_signature']) : null;
        $settings['approver_signature_url'] = $settings['approver_signature'] ? Storage::url($settings['approver_signature']) : null;

        return Inertia::render('Admin/LabSettings', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lab_name' => 'required|string|max:255',
            'lab_phone' => 'nullable|string|max:50',
            'lab_email' => 'nullable|email|max:255',
            'lab_address' => 'nullable|string|max:500',
            'lab_logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'doctor_signature' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:1024',
            'doctor_name' => 'nullable|string|max:255',
            'doctor_qualification' => 'nullable|string|max:255',
            'technician_signature' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:1024',
            'technician_name' => 'nullable|string|max:255',
            'approver_signature' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:1024',
            'approver_name' => 'nullable|string|max:255',
            'approver_qualification' => 'nullable|string|max:255',
            'report_header_text' => 'nullable|string|max:500',
            'report_footer_text' => 'nullable|string|max:500',
            'remove_logo' => 'nullable|boolean',
            'remove_signature' => 'nullable|boolean',
            'remove_technician_signature' => 'nullable|boolean',
            'remove_approver_signature' => 'nullable|boolean',
            'report_design' => 'nullable|string|max:5000',
            'sms_enabled' => 'nullable|string|in:0,1',
            'sms_gateway_url' => 'nullable|string|max:500',
            'sms_api_key' => 'nullable|string|max:500',
            'sms_sender_id' => 'nullable|string|max:100',
            'sms_report_ready_template' => 'nullable|string|max:1000',
        ]);

        // Text fields
        $textFields = ['lab_name', 'lab_phone', 'lab_email', 'lab_address', 'doctor_name', 'doctor_qualification', 'technician_name', 'approver_name', 'approver_qualification', 'report_header_text', 'report_footer_text', 'report_design', 'sms_enabled', 'sms_gateway_url', 'sms_api_key', 'sms_sender_id', 'sms_report_ready_template'];
        foreach ($textFields as $key) {
            if (array_key_exists($key, $validated)) {
                LabSetting::set($key, $validated[$key]);
            }
        }

        // Handle logo upload
        if ($request->hasFile('lab_logo')) {
            $oldLogo = LabSetting::get('lab_logo');
            if ($oldLogo) {
                Storage::delete($oldLogo);
            }
            $path = $request->file('lab_logo')->store('lab', 'public');
            LabSetting::set('lab_logo', $path);
        } elseif ($request->boolean('remove_logo')) {
            $oldLogo = LabSetting::get('lab_logo');
            if ($oldLogo) {
                Storage::delete($oldLogo);
            }
            LabSetting::set('lab_logo', null);
        }

        // Handle signature upload
        if ($request->hasFile('doctor_signature')) {
            $oldSig = LabSetting::get('doctor_signature');
            if ($oldSig) {
                Storage::delete($oldSig);
            }
            $path = $request->file('doctor_signature')->store('lab', 'public');
            LabSetting::set('doctor_signature', $path);
        } elseif ($request->boolean('remove_signature')) {
            $oldSig = LabSetting::get('doctor_signature');
            if ($oldSig) { Storage::delete($oldSig); }
            LabSetting::set('doctor_signature', null);
        }

        // Handle technician signature
        if ($request->hasFile('technician_signature')) {
            $old = LabSetting::get('technician_signature');
            if ($old) { Storage::delete($old); }
            LabSetting::set('technician_signature', $request->file('technician_signature')->store('lab', 'public'));
        } elseif ($request->boolean('remove_technician_signature')) {
            $old = LabSetting::get('technician_signature');
            if ($old) { Storage::delete($old); }
            LabSetting::set('technician_signature', null);
        }

        // Handle approver signature
        if ($request->hasFile('approver_signature')) {
            $old = LabSetting::get('approver_signature');
            if ($old) { Storage::delete($old); }
            LabSetting::set('approver_signature', $request->file('approver_signature')->store('lab', 'public'));
        } elseif ($request->boolean('remove_approver_signature')) {
            $old = LabSetting::get('approver_signature');
            if ($old) { Storage::delete($old); }
            LabSetting::set('approver_signature', null);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Lab settings updated successfully.');
    }

    public function previewPdf()
    {
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

        $pdf = Pdf::loadView('reports.preview-pdf', [
            'labSettings' => $labSettings,
        ]);

        return $pdf->stream('sample-report-preview.pdf');
    }
}
