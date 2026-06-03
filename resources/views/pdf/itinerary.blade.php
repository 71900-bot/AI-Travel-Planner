<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Travel Itinerary - Trip #{{ $trip->id }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;600;700&display=swap');
        
        body {
            font-family: 'Noto Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.8;
            margin: 20px;
            color: #333;
            font-size: 12px;
        }
        h1 {
            color: #1e40af;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 24px;
        }
        h2 {
            color: #1e40af;
            font-size: 18px;
            margin: 20px 0 12px 0;
        }
        .section-header {
            background-color: #f3f4f6;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 20px 0 12px 0;
            font-weight: bold;
            font-size: 16px;
            color: #1e40af;
            border-left: 4px solid #1e40af;
        }
        .section-header-blue {
            background-color: #dbeafe;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 20px 0 12px 0;
            font-weight: bold;
            font-size: 16px;
            color: #1e40af;
            border-left: 4px solid #1e40af;
        }
        .section-header-purple {
            background-color: #f3e8ff;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 20px 0 12px 0;
            font-weight: bold;
            font-size: 16px;
            color: #6b21a8;
            border-left: 4px solid #6b21a8;
        }
        .section-header-green {
            background-color: #dcfce7;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 20px 0 12px 0;
            font-weight: bold;
            font-size: 16px;
            color: #166534;
            border-left: 4px solid #166534;
        }
        .section-header-yellow {
            background-color: #fef9c3;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 20px 0 12px 0;
            font-weight: bold;
            font-size: 16px;
            color: #854d0e;
            border-left: 4px solid #854d0e;
        }
        .section-header-red {
            background-color: #fee2e2;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 20px 0 12px 0;
            font-weight: bold;
            font-size: 16px;
            color: #991b1b;
            border-left: 4px solid #991b1b;
        }
        .section-header-indigo {
            background-color: #e0e7ff;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 20px 0 12px 0;
            font-weight: bold;
            font-size: 16px;
            color: #3730a3;
            border-left: 4px solid #3730a3;
        }
        .section-header-red-dark {
            background-color: #fecaca;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 20px 0 12px 0;
            font-weight: bold;
            font-size: 16px;
            color: #7f1d1d;
            border-left: 4px solid #7f1d1d;
        }
        .section-header-amber {
            background-color: #fef3c7;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 20px 0 12px 0;
            font-weight: bold;
            font-size: 16px;
            color: #92400e;
            border-left: 4px solid #92400e;
        }
        .day-header {
            font-weight: bold;
            font-size: 18px;
            color: #1e40af;
            margin: 24px 0 12px 0;
            padding: 8px 0;
            border-bottom: 2px solid #e5e7eb;
        }
        .bullet-item {
            margin: 8px 0 8px 20px;
            padding-left: 8px;
        }
        .bullet-item::before {
            content: "•";
            color: #1e40af;
            font-weight: bold;
            margin-right: 8px;
        }
        .numbered-item {
            margin: 8px 0 8px 20px;
            padding-left: 8px;
        }
        strong {
            color: #1e40af;
            font-weight: 600;
        }
        p {
            margin: 8px 0;
            text-align: justify;
        }
        .trip-details {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .trip-details p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <h1>AI Travel Itinerary</h1>

    <div class="trip-details">
        <p><strong>Trip ID:</strong> #{{ $trip->id }}</p>
        <p><strong>From:</strong> {{ $trip->from_city }}</p>
        <p><strong>To:</strong> {{ $trip->to_city }}</p>
        <p><strong>Duration:</strong> {{ $trip->duration }} days</p>
        <p><strong>Budget:</strong> {{ $trip->budget }}</p>
        <p><strong>Generated:</strong> {{ $trip->created_at->format('F j, Y, g:i a') }}</p>
    </div>

    <div class="itinerary-content">
        {!! \App\Helpers\ItineraryFormatter::formatForPdf($trip->itinerary) !!}
    </div>
</body>
</html>


