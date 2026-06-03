<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TravelPlannerService;
use App\Models\Trip;
use Barryvdh\DomPDF\Facade\Pdf;

class TravelController extends Controller
{
    public function generate(
        Request $request,
        TravelPlannerService $planner
    ) {
        $trip = $planner->generate($request);

        return view('itinerary.result', compact('trip'));
    }

    public function show($id)
    {
        $trip = Trip::query()
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('itinerary.result', compact('trip'));
    }

    public function downloadPdf($id)
    {
        $trip = Trip::query()
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Use the same PDF template as Livewire-generated PDFs for consistent layout.
        $pdf = Pdf::loadView('pdf.itinerary-livewire', [
            'data' => [
                'fromCity' => $trip->from_city,
                'toCity' => $trip->to_city,
                'duration' => $trip->duration,
                'travelers' => $trip->travelers,
                'budget' => $trip->budget,
                'itinerary' => $trip->itinerary,
            ],
        ]);


        $san = function ($v) {
            $v = (string) $v;
            $v = trim($v);
            $v = str_replace(['?', '→'], '', $v);
            $v = preg_replace('/\s+/', '-', $v);
            $v = preg_replace('/[^A-Za-z0-9\-]/', '', $v);
            return $v;
        };

        $fromCity = $san($trip->from_city ?? 'From');
        $toCity = $san($trip->to_city ?? 'To');
        $durationDays = (string) ($trip->duration ?? '');
        $durationDays = preg_replace('/[^0-9]/', '', $durationDays);
        $durationDays = $durationDays !== '' ? $durationDays : 'Trip';

        $pax = (string) ($trip->travelers ?? 1);
        $pax = preg_replace('/[^0-9]/', '', $pax);
        $pax = $pax !== '' ? $pax : '1';

        $filename = "{$fromCity}-to-{$toCity}-{$durationDays}-Day-Trip-{$pax}pax".".pdf";

        return $pdf->download($filename);
    }
}


