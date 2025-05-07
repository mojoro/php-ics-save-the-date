<?php
// Load parent styles
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
});

// Bring in ICal class namespace
use ICal\ICal;

// Shortcode to display upcoming ICS events
function display_upcoming_ics_events($atts) {
    // Load the ICS parser
    require_once get_stylesheet_directory() . '/ics-parser/ICal/Event.php';
    require_once get_stylesheet_directory() . '/ics-parser/ICal/ICal.php';

    // Shortcode attributes
    $atts = shortcode_atts([
        'url' => '',
        'count' => 5
    ], $atts);

    if (empty($atts['url'])) {
        return '<p>No calendar URL provided.</p>';
    }

    // Download ICS file with cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $atts['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $icsData = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if (!$icsData) {
        return '<p>Error downloading calendar: ' . esc_html($curlError) . '</p>';
    }

    try {
        $ical = new ICal(false, [
            'defaultSpan' => 1,
            'defaultTimeZone' => 'UTC',
            'skipRecurrence' => false,
            'useTimeZoneWithRRules' => false,
        ]);

        // Parse the string directly
        $ical->initString($icsData);

        $events = $ical->eventsFromRange('now', '+1 year');

        // Sort events by start date
        usort($events, function ($a, $b) {
            return strtotime($a->dtstart) - strtotime($b->dtstart);
        });

        $events = array_slice($events, 0, $atts['count']);

        // Format the output
        $output = '<ul class="save-the-dates">';
        foreach ($events as $event) {
            $date = date('F j, Y', strtotime($event->dtstart));
            $summary = isset($event->summary) ? $event->summary : '(No title)';
            $output .= '<li><strong>' . esc_html($date) . '</strong>: ' . esc_html($summary) . '</li>';
        }
        $output .= '</ul>';

        return $output;

    } catch (Exception $e) {
        return '<p>Error parsing calendar: ' . esc_html($e->getMessage()) . '</p>';
    }
}

// Register the shortcode
add_shortcode('ics_events', 'display_upcoming_ics_events');