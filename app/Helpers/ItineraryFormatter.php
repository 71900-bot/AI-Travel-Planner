<?php

namespace App\Helpers;

class ItineraryFormatter
{
    public static function format($text)
    {
        // --- Cleanup raw AI artifacts (e.g. literal "\\n" sequences) ---
        if (is_array($text)) {
            // If itinerary payload is accidentally passed as an array,
            // prefer the `itinerary` field.
            $text = $text['itinerary'] ?? '';
        }

        $text = (string) $text;
        $text = str_replace(["\\n", "\\r\\n"], "\n", $text);
        $text = preg_replace("/\\r\\n/", "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        $text = str_replace('�', '', $text);
        $text = preg_replace('/^\?+\s*$/m', '', $text);
        $text = preg_replace('/^\s*\?+\s*/m', '', $text);

        // Remove asterisks and markdown symbols (clean up without adding formatting)
        $text = preg_replace('/\*\*\*(.+?)\*\*\*/s', '$1', $text);
        $text = preg_replace('/\*\*(.+?)\*\*/s', '$1', $text);
        $text = preg_replace('/\*(.+?)\*/s', '$1', $text);
        $text = preg_replace('/__(.+?)__/s', '$1', $text);
        $text = preg_replace('/_(.+?)_/s', '$1', $text);

        // Replace common section headings with styled versions
        $patterns = [
            '/^\s*(?:\?+\s*)?(?:🚗\s*)?Transportation\s*Options\s*(:|\s*)$/mi' => '<div class="bg-blue-100 text-blue-800 font-bold px-4 py-3 rounded-lg mb-4 text-lg">🚗 Transportation Options</div>',
            '/^\s*(?:\?+\s*)?(?:🏨\s*)?Accommodation\s*Recommendations\s*(:|\s*)$/mi' => '<div class="bg-purple-100 text-purple-800 font-bold px-4 py-3 rounded-lg mb-4 text-lg">🏨 Accommodation Recommendations</div>',
            '/^\s*(?:\?+\s*)?(?:📅\s*)?Day-by-Day\s*Itinerary\s*(:|\s*)$/mi' => '<div class="bg-green-100 text-green-800 font-bold px-4 py-3 rounded-lg mb-4 text-lg">📅 Day-by-Day Itinerary</div>',
            '/^\s*(?:\?+\s*)?(?:💰\s*)?Budget\s*Breakdown\s*(:|\s*)$/mi' => '<div class="bg-yellow-100 text-yellow-800 font-bold px-4 py-3 rounded-lg mb-4 text-lg">💰 Budget Breakdown</div>',
            '/^\s*(?:\?+\s*)?(?:📱\s*)?Essential\s*Apps\s*(?:&|and)\s*Services\s*(:|\s*)$/mi' => '<div class="bg-indigo-100 text-indigo-800 font-bold px-4 py-3 rounded-lg mb-4 text-lg">📱 Essential Apps & Services</div>',
            '/^\s*(?:\?+\s*)?(?:🆘\s*)?Emergency\s*Information\s*(:|\s*)$/mi' => '<div class="bg-red-200 text-red-900 font-bold px-4 py-3 rounded-lg mb-4 text-lg">🆘 Emergency Information</div>',
            '/^\s*(?:\?+\s*)?(?:💡\s*)?Practical\s*Tips\s*(:|\s*)$/mi' => '<div class="bg-amber-100 text-amber-800 font-bold px-4 py-3 rounded-lg mb-4 text-lg">💡 Practical Tips</div>',
        ];

        $text = preg_replace_callback(
            '/^\s*(?:\?+\s*)?(?:🍽️\s*)?Food\s*(?:&|and)\s*Dining(?:\s*[:\-–—]\s*|\s+)?(.*)$/mi',
            function ($matches) {
                $content = trim($matches[1]);
                return '<div class="bg-red-100 text-red-800 font-bold px-4 py-3 rounded-lg mb-4 text-lg">🍽️ Food & Dining</div>' . ($content !== '' ? "\n" . $content : '');
            },
            $text
        );
        
        $text = preg_replace(array_keys($patterns), array_values($patterns), $text);

        // Fix stray leading ". " at the start of lines (e.g. ". Hospital and clinic:"
        $text = preg_replace('/^\.\s+/m', '', $text);

        // Highlight day-by-day even if the emoji prefix is missing (e.g. "Day-by-Day Itinerary")
        // Avoid double-highlighting if the heading was already replaced in the $patterns block.
        $text = preg_replace(
            '/^(?!<div[^>]*>)\s*(?:📅\s*)?Day-by-Day\s*Itinerary\s*$/mi',
            '<div class="bg-green-100 text-green-800 font-bold px-4 py-3 rounded-lg mb-4 text-lg">📅 Day-by-Day Itinerary</div>',
            $text
        );

        // Format numbered lists:
        $text = preg_replace(
            '/^\s*(\d+)\s*(?:[\.\):\-\x{2013}\x{2014}]\s*)*(.+)$/mu',
            '<div class="flex items-start gap-2 ml-6 my-1"><span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mt-0.5 flex-shrink-0">$1</span><span class="text-gray-800 leading-relaxed">$2</span></div>',
            $text
        );

        $text = preg_replace(
            '/^\s*(\d+)\s*$/m',
            '<div class="flex items-start gap-2 ml-6 my-1"><span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mt-0.5 flex-shrink-0">$1</span><span class="text-gray-700">&nbsp;</span></div>',
            $text
        );

        // Format bullet points ("+ ...", "- ...", "• ...", "* ...")
        $text = preg_replace(
            '/^(?:\+|[-*•])\s+(.+)$/m',
            '<div class="flex items-start gap-2 ml-6 my-1">
                <span class="text-blue-600 mt-1 text-lg">•</span>
                <span class="text-gray-800 leading-relaxed">$1</span>
            </div>',
            $text
        );

        // Format day headings
        $text = preg_replace(
            '/^Day\s+(\d+)(:?.*)$/m',
            '<div class="font-bold text-xl text-blue-700 mt-8 mb-3">📌 Day $1$2</div>',
            $text
        );

        // Remove consecutive duplicate headings
        $text = preg_replace('/(<div class="bg-[^>]+">.+?<\/div>)(?:\s*\1)+/s', '$1', $text);
        $text = preg_replace('/(<div class="font-bold text-xl text-blue-700 mt-8 mb-3">.+?<\/div>)(?:\s*\1)+/s', '$1', $text);

        // Format paragraphs (only if not already in a div)
        // Skip list/day lines and already-formatted blocks.
        $text = preg_replace(
            '/^(?!<div)(?!\d+[\.,\)]\s+)(?!\d+\s*$)(?![-*•+ ]\s+)(?!Day\s+\d+)(.+)$/m',
            '<p class="ml-6 mb-3 text-gray-700 leading-relaxed">$1</p>',
            $text
        );

        return $text;
    }

    public static function formatForPdf($text)
    {
        if (is_array($text)) {
            $text = $text['itinerary'] ?? '';
        }

        // --- Cleanup raw AI artifacts (e.g. literal "\\n" sequences) ---
        $text = (string) $text;
        $text = str_replace(["\\n", "\\r\\n"], "\n", $text);
        $text = preg_replace("/\\r\\n/", "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        // Remove unsupported symbols and emojis that often render as question marks in PDF
        $text = preg_replace('/[\x{1F300}-\x{1F6FF}\x{1F900}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $text);
        $text = str_replace('�', '', $text);

        // Remove stray/dangling question marks commonly produced by the AI in PDF text
        $text = preg_replace('/^\?+\s*$/m', '', $text);
        $text = preg_replace('/^\s*\?+\s*/m', '', $text);

        // Remove asterisks and markdown symbols (clean up without adding formatting)
        $text = preg_replace('/\*\*\*(.+?)\*\*\*/s', '$1', $text);
        $text = preg_replace('/\*\*(.+?)\*\*/s', '$1', $text);
        $text = preg_replace('/\*(.+?)\*/s', '$1', $text);
        $text = preg_replace('/__(.+?)__/s', '$1', $text);
        $text = preg_replace('/_(.+?)_/s', '$1', $text);

        // Replace section headings with styled HTML with colored backgrounds
        $patterns = [
            '/^\s*(?:\?+\s*)?(?:🚗\s*)?Transportation\s*Options\s*(:|\s*)$/mi' => '<div class="section-header-blue">Transportation Options</div>',
            '/^\s*(?:\?+\s*)?(?:🏨\s*)?Accommodation\s*Recommendations\s*(:|\s*)$/mi' => '<div class="section-header-purple">Accommodation Recommendations</div>',
            '/^\s*(?:\?+\s*)?(?:📅\s*)?Day-by-Day\s*Itinerary\s*(:|\s*)$/mi' => '<div class="section-header-green">Day-by-Day Itinerary</div>',
            '/^\s*(?:\?+\s*)?(?:💰\s*)?Budget\s*Breakdown\s*(:|\s*)$/mi' => '<div class="section-header-yellow">Budget Breakdown</div>',
            '/^\s*(?:\?+\s*)?(?:🍽️\s*)?Food\s*(?:&|and)\s*Dining\s*(:|\s*)$/mi' => '<div class="section-header-red">Food & Dining</div>',
            '/^\s*(?:\?+\s*)?(?:📱\s*)?Essential\s*Apps\s*(?:&|and)\s*Services\s*(:|\s*)$/mi' => '<div class="section-header-indigo">Essential Apps & Services</div>',
            '/^\s*(?:\?+\s*)?(?:🆘\s*)?Emergency\s*Information\s*(:|\s*)$/mi' => '<div class="section-header-red-dark">Emergency Information</div>',
            '/^\s*(?:\?+\s*)?(?:💡\s*)?Practical\s*Tips\s*(:|\s*)$/mi' => '<div class="section-header-amber">Practical Tips</div>',
        ];
        $text = preg_replace(array_keys($patterns), array_values($patterns), $text);

        $text = preg_replace('/^\.\s+/m', '', $text);

        $text = preg_replace(
            '/^(?!<div[^>]*>)\s*(?:📅\s*)?Day-by-Day\s*Itinerary\s*$/mi',
            '<div class="section-header-green">Day-by-Day Itinerary</div>',
            $text
        );

        // Format numbered lists
            $text = preg_replace('/^\s*(\d+)\s*(?:[\.\):\-\x{2013}\x{2014}]\s*)*(.+)$/mu', '<div class="numbered-item">$1 $2</div>', $text);
        $text = preg_replace('/^\s*(\d+)\s*$/m', '<div class="numbered-item">$1</div>', $text);

        // Format bullets
        $text = preg_replace('/^(?:\+|[-*•])\s+(.+)$/m', '<div class="bullet-item">$1</div>', $text);

        // Format day headings
        $text = preg_replace('/^Day\s+(\d+)(:?.*)$/m', '<div class="day-header">Day $1$2</div>', $text);

        // Remove consecutive duplicate headings that can be generated by the AI output
        $text = preg_replace('/(<div class="section-header[^>]*>.+?<\/div>)(?:\s*\1)+/s', '$1', $text);
        $text = preg_replace('/(<div class="day-header">.+?<\/div>)(?:\s*\1)+/s', '$1', $text);

        // Format paragraphs (only if not already in a div)
        $text = preg_replace('/^(?!<div)(?!\d+[\.\)]\s+)(?!\d+\s*$)(?![-*•+ ]\s+)(?!Day\s+\d+)(.+)$/m', '<p>$1</p>', $text);

        return $text;
    }
}

