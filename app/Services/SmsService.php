<?php

namespace App\Services;

use App\Models\LabSetting;
use App\Models\Report;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;

class SmsService
{
    /**
     * Send an SMS and log it.
     */
    public function send(string $phone, string $message, ?int $orderId = null, ?int $reportId = null, string $type = 'custom'): SmsLog
    {
        $smsLog = SmsLog::create([
            'phone' => $phone,
            'message' => $message,
            'type' => $type,
            'status' => 'pending',
            'order_id' => $orderId,
            'report_id' => $reportId,
        ]);

        $gatewayUrl = LabSetting::get('sms_gateway_url');
        $apiKey = LabSetting::get('sms_api_key');
        $senderId = LabSetting::get('sms_sender_id');
        $smsEnabled = LabSetting::get('sms_enabled');

        if (!$smsEnabled || !$gatewayUrl || !$apiKey) {
            $smsLog->update([
                'status' => 'failed',
                'gateway_response' => 'SMS not configured or not enabled.',
            ]);

            return $smsLog;
        }

        try {
            $response = Http::post($gatewayUrl, [
                'api_key' => $apiKey,
                'sender' => $senderId,
                'to' => $phone,
                'message' => $message,
            ]);

            $smsLog->update([
                'status' => $response->successful() ? 'sent' : 'failed',
                'gateway_response' => $response->body(),
                'sent_at' => $response->successful() ? now() : null,
            ]);
        } catch (\Exception $e) {
            $smsLog->update([
                'status' => 'failed',
                'gateway_response' => $e->getMessage(),
            ]);
        }

        return $smsLog;
    }

    /**
     * Send a "report ready" SMS for the given report.
     */
    public function sendReportReady(Report $report): ?SmsLog
    {
        $report->loadMissing('order.patient');

        $patient = $report->order?->patient;

        if (!$patient || !$patient->phone) {
            return null;
        }

        $template = LabSetting::get(
            'sms_report_ready_template',
            'Dear {patient_name}, your lab report #{report_number} is ready. View at: {link} - {lab_name}'
        );

        $link = $report->public_token
            ? url('/report/' . $report->public_token)
            : url('/reports/' . $report->ulid);

        $labName = LabSetting::get('lab_name', 'PathLab');

        $message = str_replace(
            ['{patient_name}', '{report_number}', '{link}', '{lab_name}'],
            [$patient->name, $report->report_number, $link, $labName],
            $template
        );

        return $this->send(
            $patient->phone,
            $message,
            $report->order_id,
            $report->id,
            'report_ready'
        );
    }
}
