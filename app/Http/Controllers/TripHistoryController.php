<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TripHistoryController extends Controller
{
    public function index(): View
    {
        $trips = Trip::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('itinerary.history', compact('trips'));
    }

    public function show(int $id): View
    {
        $trip = Trip::query()
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('itinerary.history-detail', compact('trip'));
    }
}

