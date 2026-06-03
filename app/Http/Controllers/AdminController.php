<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $users = User::query()
            ->with(['trips' => function ($query) {
                $query->latest()->take(10);
            }])
            ->latest()
            ->paginate(12);

        return view('admin.dashboard', compact('users'));
    }

    /**
     * Delete (soft-delete) a user's itinerary history.
     */
    public function deleteTripHistory(Request $request, int $tripId)
    {
        $trip = Trip::query()->withTrashed()->whereKey($tripId)->first();

        if (!$trip) {
            // Prevent a silent failure where admin UI thinks it deleted but the row doesn’t exist.
            // Also makes the mismatch obvious during debugging.
            return redirect('/admin')->with('error', "Trip not found for deletion (tripId={$tripId}).");
        }

        // Admin can delete any trip.
        $trip->delete();

        return redirect('/admin')->with('success', 'Deleted itinerary: ' . ($trip->itinerary_name ?? 'Untitled itinerary') . ' (soft delete)');
    }


    /**
     * Delete (soft-delete) a user's entire itinerary history.
     *
     * Kept for backward compatibility with existing UI.
     */
    public function deleteUserHistory(Request $request, int $userId)
    {
        $target = User::query()->findOrFail($userId);

        Trip::query()
            ->where('user_id', $target->id)
            ->delete();

        return redirect('/admin')->with('success', "Deleted itinerary history for {$target->name}. (soft delete)");
    }
}

