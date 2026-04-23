<?php

namespace App\Http\Controllers\Api\Technician;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SurveyReport;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class SurveyReportController extends Controller
{
    public function __construct(private NotificationService $notif) {}

    public function store(Request $request, Order $order)
    {
        $request->validate([
            'kondisi_unit'        => 'required|in:normal,kotor,rusak',
            'bagian_bermasalah'   => 'nullable|array',
            'bagian_bermasalah.*' => 'in:kompresor,freon,filter,pcb,fan,lainnya',
            'catatan'             => 'nullable|string',
            'rekomendasi'         => 'required|in:cuci_unit,perbaikan',
            'photo_before'        => 'nullable|image|max:5120',
            'photo_after'         => 'nullable|image|max:5120',
        ]);

        /** @var \App\Models\Technician $technician */
        $technician = $request->user()->technician;

        if ($order->technician_id !== $technician->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (!$order->is_perbaikan || $order->perbaikan_phase !== 'survey') {
            return response()->json(['message' => 'Order bukan fase survey.'], 422);
        }

        if ($order->status !== 'survey_in_progress') {
            return response()->json(['message' => 'Status order tidak valid untuk submit report.'], 422);
        }

        if ($order->surveyReport()->exists()) {
            return response()->json(['message' => 'Report survey sudah pernah disubmit.'], 422);
        }

        $photoBefore = $request->hasFile('photo_before')
            ? $request->file('photo_before')->store("survey-reports/{$order->id}", 'public')
            : null;

        $photoAfter = $request->hasFile('photo_after')
            ? $request->file('photo_after')->store("survey-reports/{$order->id}", 'public')
            : null;

        /** @var \App\Models\SurveyReport $report */
        $report = SurveyReport::create([
            'order_id'          => $order->id,
            'technician_id'     => $technician->id,
            'kondisi_unit'      => $request->kondisi_unit,
            'bagian_bermasalah' => $request->bagian_bermasalah,
            'catatan'           => $request->catatan,
            'rekomendasi'       => $request->rekomendasi,
            'photo_before'      => $photoBefore,
            'photo_after'       => $photoAfter,
        ]);

        $order->update(['status' => 'waiting_customer_response']);

        if ($order->user?->fcm_token) {
            $this->notif->notifySurveyResult(
                $order->user->fcm_token,
                (int) $order->id
            );
        }

        return response()->json([
            'message' => 'Report survey berhasil dikirim.',
            'report'  => $report,
        ]);
    }
}
