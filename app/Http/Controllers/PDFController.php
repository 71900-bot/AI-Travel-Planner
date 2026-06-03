<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFController extends Controller
{
    public function downloadLivewire(Request $request)
    {
        $data = session('itinerary_data');

        if (!$data) {
            return response()->json(['error' => 'No data provided'], 400);
        }

        // Always generate the PDF using the same Livewire PDF view/layout
        // so newly generated and history-download PDFs match.
        $pdf = Pdf::loadView('pdf.itinerary-livewire', [
            'data' => $data
        ]);

        // Don't clear session after download (user might want to download again)
        // session()->forget('itinerary_data');


        $san = function ($v) {
            $v = (string) $v;
            $v = trim($v);
            $v = preg_replace('/\s+/', '-', $v);
            $v = preg_replace('/[^A-Za-z0-9\-]/', '', $v);
            return $v;
        };

        // Livewire stores itinerary content in session('itinerary_data').
        // Support both formats:
        //  1) string: itinerary text only
        //  2) array: {fromCity,toCity,duration,travelers,budget,itinerary}
        $itineraryPayload = $data;

        if (is_string($data)) {
            $itineraryPayload = [
                'itinerary' => $data,
            ];
        }

        $fromCity = $san($itineraryPayload['fromCity'] ?? 'From');
        $toCity = $san($itineraryPayload['toCity'] ?? 'To');

        $durationRaw = (string) ($itineraryPayload['duration'] ?? '');
        $durationDays = preg_replace('/[^0-9]/', '', $durationRaw);
        $durationDays = (string) $durationDays;

        // If user entered "3 days" keep just "3". If empty, keep whatever was provided.
        if ($durationDays === '') {
            $durationDays = $san($durationRaw);
        }

        $pax = (string) ($itineraryPayload['travelers'] ?? 1);
        $pax = $san($pax);
        if ($pax === '') {
            $pax = '1';
        }

        // Build filename like: Kuala-Lumpur-to-Singapore-7-Day-Trip-Itinerary.pdf
        // (Your requested URL-safe hyphenated form)
        $durationPart = $durationDays !== '' ? $durationDays : 'Trip';
        $paxPart = $pax !== '' ? "{$pax}pax" : '';

        $hasCities = ($fromCity !== '' && $fromCity !== 'From' && $toCity !== '' && $toCity !== 'To');

        if ($hasCities && $durationDays !== '') {
            $filename = "{$fromCity}-to-{$toCity}-{$durationPart}-Day-Trip-{$pax}pax".".pdf";
        } else {
            // Generic fallback if session is missing fields.
            $fallbackBase = 'AI-Travel-Itinerary';
            $filename = $fallbackBase . '-' . date('Y-m-d') . '.pdf';
        }

        return $pdf->download($filename);

    }

    public function viewItinerary(Request $request)
    {
        $data = session('itinerary_data');


        if (!$data) {
            return redirect('/')->with('error', 'No itinerary data found. Please generate an itinerary first.');
        }

        return view('itinerary-view', ['data' => $data]);
    }
}
