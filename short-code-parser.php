<?php
require 'vendor/autoload.php';

use ICal\ICal;

// Replace with your actual ICS URL
$icsUrl = 'https://ics.calendarlabs.com/67/9fa9febe/Singapore_Holidays.ics';

try {
    $ical = new ICal($icsUrl, [
        'defaultSpan' => 1,
        'defaultTimeZone' => 'UTC',
        'skipRecurrence' => false,
        'useTimeZoneWithRRules' => false,
    ]);

    $events = $ical->eventsFromRange('now', '+1 year');

    usort($events, function ($a, $b) {
        return strtotime($a->dtstart) - strtotime($b->dtstart);
    });

    echo "Next 5 Upcoming Events:\n\n";
    foreach (array_slice($events, 0, 5) as $event) {
        $date = date('F j, Y', strtotime($event->dtstart));
        echo "📅 {$date}: {$event->summary}\n";
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
