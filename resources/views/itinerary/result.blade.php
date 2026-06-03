@extends('layout')

@section('content')

<h1>Your AI Trip Plan</h1>

<div class="mb-4">
    <p><strong>From:</strong> {{ $trip->from_city }}</p>
    <p><strong>To:</strong> {{ $trip->to_city }}</p>
    <p><strong>Duration:</strong> {{ $trip->duration }} days</p>
    <p><strong>Budget:</strong> {{ $trip->budget }}</p>
</div>

<div class="mb-4">
    {!! \App\Helpers\ItineraryFormatter::format($trip->itinerary) !!}
</div>

<div class="mt-4">
    <a href="/trip/{{ $trip->id }}/pdf" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Download PDF
    </a>
</div>

@endsection