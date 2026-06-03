<?php

namespace App\Services;

use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\Trip;

class TravelPlannerService
{
    public function generate(Request $request)
    {
        $prompt = "
        Create a travel itinerary.

        From: {$request->from_city}
        To: {$request->to_city}
        Duration: {$request->duration} days
        Budget: {$request->budget}

        Include:
        - Day-by-Day itinerary with Day 1, Day 2, Day 3, etc.
        - Use Morning, Afternoon, and Evening sections.
        - Do not include explicit calendar dates or ISO timestamp strings.
        - Hotel
        - Food
        - Transport
        ";

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ]);

        $itinerary = $response->choices[0]->message->content;

        $userId = auth()->id();

        // travelers is optional in request payload; default to 1
        $travelers = (int) ($request->input('travelers', 1));

        $itineraryName = sprintf(
            '%s -> %s (%s days, %d traveler%s)',
            $request->from_city,
            $request->to_city,
            $request->duration,
            $travelers,
            $travelers === 1 ? '' : 's'
        );

        $budget = $request->input('budget');
        $budget = is_string($budget) ? trim($budget) : $budget;

        // Normalize budget to a numeric decimal string (or null) before saving.
        if ($budget === null) {
            $budget = null;
        } else {
            $budgetStr = (string) $budget;
            $budgetStr = str_replace(['$', '€', '£', '¥'], '', $budgetStr);
            $budgetStr = str_replace([',', ' '], '', $budgetStr);

            if ($budgetStr === '') {
                $budget = null;
            } elseif (preg_match('/^-?\d*(?:\.\d+)?$/', $budgetStr)) {
                $budget = number_format((float) $budgetStr, 2, '.', '');
            } else {
                // If budget came in formatted text (unexpected), avoid breaking casts.
                $budget = null;
            }
        }

        $trip = Trip::create([
            'user_id' => $userId,
            'from_city' => $request->from_city,
            'to_city' => $request->to_city,
            'duration' => (int) $request->duration,
            'travelers' => $travelers,
            'budget' => $budget,
            'itinerary_name' => $itineraryName,
            'itinerary' => $itinerary,
        ]);

        return $trip;
    }
}