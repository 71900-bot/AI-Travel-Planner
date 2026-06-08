<?php

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\TravelPlannerService;
use App\Helpers\ItineraryFormatter;

use Illuminate\Support\Facades\Auth;


new class extends Component
{
    public $fromCity = '';
    public $toCity = '';
    public $duration = '';
    public $travelers = 1;
    public $budget = '';
    public $travelStyles = [];

    public $userTimezone = 'UTC';
    public $userUtcOffsetMinutes = 0;
    public $userNowIso = '';

    public $result = '';
    public $errorMessage = '';

    public function generate()
    {
        set_time_limit(1200); // Increase execution time to 20 minutes

        $this->errorMessage = '';

        $this->validate([
            'fromCity' => 'required|string|min:1',
            'toCity' => 'required|string|min:1',
            'duration' => 'required|string|min:1',
            'budget' => [
                'required',
                'string',
                // Examples accepted:
                // - 800 (USD)
                // - 2500 (EUR)
                // - 3000 MYR
                // - $1200
                // - 1200 USD
                // - 1200 USD / 2025-... (extra input is rejected)
                'regex:/^\s*(?:(?:\d+(?:\.\d{1,2})?)\s*(?:\(?(USD|EUR|GBP|SGD|AUD|CAD|CHF|JPY|CNY|HKD|INR|MYR)\)?|\b(\$|€|£|RM)\b)|(?:MYR|USD|EUR|GBP|SGD|AUD|CAD|CHF|JPY|CNY|HKD|INR|RM)\s*\d+(?:\.\d{1,2})?)\s*(?:\/\s*(?:USD|EUR|GBP|SGD|AUD|CAD|CHF|JPY|CNY|HKD|INR|MYR)\s*)?$/i'
            ],

        ], [
            'budget.regex' => 'Budget must include currency, e.g. 800 (USD) or 2500 (EUR) or 3000 MYR or $1200',
        ]);

        $this->errorMessage = 'Starting generation...';

        // DEBUG: ensure Livewire state is visible even if execution blocks
        $this->dispatch('debug-message', message: 'Starting generation...');

        $userTimezone = (is_string($this->userTimezone) && $this->userTimezone !== '') ? $this->userTimezone : 'UTC';
        $userUtcOffsetMinutes = ($this->userUtcOffsetMinutes !== '' && $this->userUtcOffsetMinutes !== null)
            ? (int) $this->userUtcOffsetMinutes
            : 0;
        $userNowIso = (is_string($this->userNowIso) && $this->userNowIso !== '') ? $this->userNowIso : gmdate('c');

        $prompt = "Create a detailed travel itinerary for a trip from {$this->fromCity} to {$this->toCity} for {$this->duration} with {$this->travelers} travelers and a budget of {$this->budget}.\n\n";
        $prompt .= "The user is currently in timezone: {$userTimezone} (UTC offset: {$userUtcOffsetMinutes} minutes). Current local time (ISO): {$userNowIso}.\n\n";
        $prompt .= "IMPORTANT:\n";
        $prompt .= "- Use Day 1, Day 2, Day 3, Day 4 headings only for the trip schedule.\n";
        $prompt .= "- Do not include explicit calendar dates or timestamp strings such as 2026-06-03T08:30+00:00.\n";
        $prompt .= "- Use relative descriptions like Morning, Afternoon, and Evening rather than exact date-time stamps.\n\n";
        $prompt .= "Include these sections with clear headings:\n\n";

        $prompt .= "🚗 Transportation Options\n";
        $prompt .= "- Flight, train, bus, or driving options with estimated costs and travel time\n";
        $prompt .= "- Best booking platforms and tips\n\n";

        $prompt .= "🏨 Accommodation Recommendations\n";
        $prompt .= "- Hotels, hostels, or Airbnb options with price ranges\n";
        $prompt .= "- Best areas to stay in {$this->toCity}\n\n";

        $prompt .= "📅 Day-by-Day Itinerary\n";
        $prompt .= "- Morning, afternoon, and evening activities for each day\n";
        $prompt .= "- Specific attractions, restaurants, and landmarks\n";
        $prompt .= "- Estimated costs for activities and meals\n\n";

        $prompt .= "💰 Budget Breakdown\n";
        $prompt .= "- Detailed cost estimate for transportation, accommodation, food, activities\n";
        $prompt .= "- Money-saving tips\n\n";

        $prompt .= "🍽️ Food & Dining\n";
        $prompt .= "- Must-try local dishes and restaurants\n";
        $prompt .= "- Street food recommendations\n";
        $prompt .= "- Dietary considerations\n\n";

        $prompt .= "📱 Essential Apps & Services\n";
        $prompt .= "- Local transportation apps\n";
        $prompt .= "- Currency exchange tips\n";
        $prompt .= "- SIM card or WiFi options\n\n";

        $prompt .= "🆘 Emergency Information\n";
        $prompt .= "- Local emergency numbers\n";
        $prompt .= "- Embassy or consulate contacts\n";
        $prompt .= "- Hospitals and clinics\n\n";

        $prompt .= "💡 Practical Tips\n";
        $prompt .= "- Best time to visit\n";
        $prompt .= "- Local customs and etiquette\n";
        $prompt .= "- Weather and packing advice\n";
        $prompt .= "- Transportation within the city\n\n";

        $prompt .= "Make it realistic with specific names of places, actual costs, and practical advice.\n\n";
        $prompt .= "Formatting requirements (important):\n";
        $prompt .= "- Keep headings as single lines exactly as provided (with the same emoji + heading text).\n";
        $prompt .= "- Use numbered lists for the Day-by-Day itinerary (e.g. \"1. Morning ...\", \"2. Afternoon ...\", \"3. Evening ...\").\n\n";
        $prompt .= "Use clear section headings with emojis. English only.";

        try {
            $ollamaHost = env('OLLAMA_HOST', 'http://127.0.0.1:11434');
            $ollamaApi = rtrim($ollamaHost, '/') . '/api';

            // Check if Ollama server is running
            try {
                $check = Http::timeout(5)->get($ollamaApi . '/tags');
                if (!$check->successful()) {
                    $this->errorMessage = 'Ollama server is not responding. Please make sure Ollama is reachable at ' . $ollamaHost;
                    return;
                }

                // Check if llama3 model is available
                $tags = $check->json();
                $modelExists = false;
                if (isset($tags['models'])) {
                    foreach ($tags['models'] as $model) {
                        if (isset($model['name']) && str_contains($model['name'], 'llama3')) {
                            $modelExists = true;
                            break;
                        }
                    }
                }

                if (!$modelExists) {
                    $this->errorMessage = 'The llama3 model is not installed. Please run: ollama pull llama3';
                    return;
                }
            } catch (\Exception $e) {
                $this->errorMessage = 'Cannot connect to Ollama server. Please make sure Ollama is reachable at ' . $ollamaHost;
                return;
            }

            $response = Http::timeout(600)->post($ollamaApi . '/generate', [
                'model' => 'llama3',
                'prompt' => $prompt,
                'stream' => false,
                'options' => [
                    'num_predict' => 8192,
                    'temperature' => 0.7,
                    'num_ctx' => 4096,
                ],
            ]);

            $data = $response->json();
            $raw = $data['response'] ?? '';

            if (empty($raw)) {
                $this->errorMessage = 'Empty response from AI model.';
                return;
            }

            $this->result = trim($raw);
            $this->errorMessage = '';

            // Persist to DB so it appears in /history
            
            $normalizedBudget = $this->budget;
            if (is_string($normalizedBudget)) {
                $normalizedBudget = trim($normalizedBudget);
                $normalizedBudget = str_replace(['$', '€', '£', '¥'], '', $normalizedBudget);
                $normalizedBudget = str_replace([',', ' '], '', $normalizedBudget);
                if ($normalizedBudget === '' || !preg_match('/^-?\d*(?:\.\d+)?$/', $normalizedBudget)) {
                    $normalizedBudget = null;
                } else {
                    $normalizedBudget = number_format((float) $normalizedBudget, 2, '.', '');
                }
            } else {
                $normalizedBudget = $normalizedBudget === null ? null : (string) $normalizedBudget;
            }

            \App\Models\Trip::create([
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'from_city' => $this->fromCity,
                'to_city' => $this->toCity,
                'duration' => (int) preg_replace('/[^0-9]/', '', (string) $this->duration),
                'travelers' => (int) $this->travelers,
                'budget' => $this->budget,
                'itinerary_name' => sprintf(
                    '%s -> %s (%s days, %d traveler%s)',
                    $this->fromCity,
                    $this->toCity,
                    $this->duration,
                    (int) $this->travelers,
                    (int) $this->travelers === 1 ? '' : 's'
                ),
                'itinerary' => $this->result,
            ]);

            session(['itinerary_data' => [
                'fromCity' => $this->fromCity,
                'toCity' => $this->toCity,
                'duration' => $this->duration,
                'travelers' => (int) $this->travelers,
                'budget' => $this->budget,
                'itinerary' => $this->result,
                // also keep timezone for downstream rendering
                'userTimezone' => $userTimezone,
            ]]);

            // Trigger file download via full navigation (more reliable than window.open)
            $this->dispatch('pdf-ready');

        } catch (\Exception $e) {
            $this->result = null;
            $this->errorMessage = 'Generation error: ' . $e->getMessage();
        }
    }

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['fromCity', 'toCity', 'duration', 'travelers', 'budget', 'travelStyles', 'result', 'errorMessage']);
        $this->travelers = 1;
        $this->userTimezone = 'UTC';
        $this->userUtcOffsetMinutes = 0;
        $this->userNowIso = '';
    }

    public function downloadPdf()
    {
        if (!$this->result) {
            return;
        }

        session(['itinerary_data' => [
            'fromCity' => $this->fromCity,
            'toCity' => $this->toCity,
            'duration' => $this->duration,
            'travelers' => (int) $this->travelers,
            'budget' => $this->budget,
            'itinerary' => $this->result,
        ]]);

        $this->dispatch('pdf-ready');

    }
};

?>

<div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
    <div class="bg-blue-600 p-6 text-white">
        <h2 class="text-xl font-bold">Plan your trip</h2>
        <p class="text-xs text-blue-100 mt-1">Fill in the details below</p>
    </div>

    <div class="p-6 space-y-5">

        @if($errorMessage)
            <div class="bg-red-100 text-red-700 p-3 rounded-lg text-sm">
                <p>{{ $errorMessage }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded-lg text-sm">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="text-xs font-bold uppercase tracking-wide text-slate-500">From City</label>
                <input type="text" wire:model.lazy="fromCity" placeholder="e.g. Kuala Lumpur"
                       class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 placeholder-slate-300">
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold uppercase tracking-wide text-slate-500">To City</label>
                <input type="text" wire:model.lazy="toCity" placeholder="e.g. Penang"
                       class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 placeholder-slate-300">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Duration</label>
                <input type="text" wire:model.lazy="duration" placeholder="e.g. 3 days"
                       class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 placeholder-slate-300">
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Travelers</label>
                <input type="number" wire:model="travelers" placeholder="e.g. 2"
                       class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 placeholder-slate-300">
            </div>
        </div>

        <div class="space-y-1">
            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Budget</label>
            <input type="text" wire:model.lazy="budget" placeholder="e.g. 800 (USD) or 2500 (EUR) or RM 3000 or $1200"
                   class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 placeholder-slate-300">
        </div>

        <div class="space-y-2">
            <label class="text-xs font-bold uppercase tracking-wide text-slate-500 block">Travel Style</label>
            <div class="grid grid-cols-2 gap-2 text-sm text-slate-700">
                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" wire:model="travelStyles" value="Adventure" class="rounded text-blue-600"> Adventure</label>
                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" wire:model="travelStyles" value="Relaxing" class="rounded text-blue-600"> Relaxing</label>
                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" wire:model="travelStyles" value="Cultural" class="rounded text-blue-600"> Cultural</label>
                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" wire:model="travelStyles" value="Family" class="rounded text-blue-600"> Family-friendly</label>
            </div>
        </div>

        <button
            wire:click="generate"
            wire:loading.attr="disabled"
            class="w-full bg-blue-500 hover:bg-blue-600 transition text-white font-semibold py-3 px-4 rounded-xl shadow-md shadow-blue-200">

            <span wire:loading.remove="generate">
                Generate Itinerary 🚀
            </span>

            <span wire:loading="generate">
                Generating...
            </span>
        </button>

        @if($result)
            <div class="mt-6 bg-green-50 border border-green-200 rounded-2xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-xl text-green-800">✅ Itinerary Generated Successfully!</h3>
                        <p class="text-green-700 text-sm mt-1">Times are anchored to your local time zone: <strong>{{ $userTimezone }}</strong></p>
                        <p class="text-green-700 text-sm mt-1">Click the button to view your itinerary.</p>
                    </div>
                    <a href="/view-itinerary" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                        Open in New Window
                    </a>
                </div>
            </div>
        @endif

    </div>
</div>

<script>
    // Livewire component is declared inside this view, so we need to set its public fields from JS.
    // We capture timezone/offset once on page load.
    document.addEventListener('DOMContentLoaded', () => {
        try {
            const tz = Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
            const offsetMinutes = -new Date().getTimezoneOffset ? 0 : new Date().getTimezoneOffset();
        } catch (e) {}

        const tz = (window.Intl && Intl.DateTimeFormat().resolvedOptions().timeZone) ? Intl.DateTimeFormat().resolvedOptions().timeZone : 'UTC';
        // getTimezoneOffset() doesn't exist; use offset between local and UTC.
        const now = new Date();
        const utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
        const diffMinutes = Math.round((now.getTime() - utc.getTime()) / 60000);

        const userNowIso = now.toISOString();

        // Livewire 3: use window.Livewire.find(...)? The view uses inline component, but Livewire still exposes global.
        // We set values by dispatching a custom event that Livewire can catch via @this.
        // Simpler: update hidden fields via direct DOM inputs is not possible here.
        // So we do a best-effort Livewire call if available.
        if (window.Livewire && typeof window.Livewire.find === 'function') {
            // Find first Livewire component root on page and set properties.
            const roots = document.querySelectorAll('[wire\:id]');
            if (roots.length > 0) {
                const id = roots[0].getAttribute('wire:id');
                const comp = window.Livewire.find(id);
                if (comp) {
                    comp.set('userTimezone', tz);
                    comp.set('userUtcOffsetMinutes', diffMinutes);
                    comp.set('userNowIso', userNowIso);
                }
            }
        }

        function resetTravelFormState() {
        const form = document.querySelector('.bg-white.rounded-2xl');
        if (!form) {
            return;
        }

        form.querySelectorAll('input[type="text"], input[type="number"]').forEach((input) => {
            if (input.matches('[wire\\:model]') || input.matches('[wire\\:model\.lazy]')) {
                input.value = input.type === 'number' ? 1 : '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        form.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
            checkbox.checked = false;
            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
        });

        if (window.Livewire && typeof window.Livewire.find === 'function') {
            const roots = document.querySelectorAll('[wire\:id]');
            if (roots.length > 0) {
                const id = roots[0].getAttribute('wire:id');
                const comp = window.Livewire.find(id);
                if (comp && typeof comp.call === 'function') {
                    comp.call('resetForm');
                }
            }
        }
    }

    window.addEventListener('pageshow', (event) => {
        let isBackForward = false;
        try {
            isBackForward = performance && typeof performance.getEntriesByType === 'function'
                ? performance.getEntriesByType('navigation').some((nav) => nav.type === 'back_forward')
                : false;
        } catch (e) {
            isBackForward = false;
        }

        if (event.persisted || isBackForward) {
            resetTravelFormState();
        }
    });

    document.addEventListener('livewire:navigated', () => {
            // no-op
        });
    });

    document.addEventListener('livewire:pdf-ready', () => {
        // Mobile browsers often block pop-ups; navigate directly instead.
        window.location.href = '/download-livewire-pdf';
    });
</script>

