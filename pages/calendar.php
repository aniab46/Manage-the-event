<?php
// Get the current month and year
$current_month = date('n');
$current_year = date('Y');
$current_month_name = date('F');

// Get the number of days in the current month
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);

// Get the first day of the month
$first_day_of_month = date('N', strtotime("{$current_year}-{$current_month}-01"));

// Query events for the current month
$args = array(
    'post_type' => 'events',
    'posts_per_page' => -1,
    'meta_query' => array(
        'relation' => 'AND',
        array(
            'key' => 'event_date',
            'value' => "{$current_year}-{$current_month}-01",
            'compare' => '>=',
            'type' => 'DATE'
        ),
        array(
            'key' => 'event_date',
            'value' => "{$current_year}-{$current_month}-{$days_in_month}",
            'compare' => '<=',
            'type' => 'DATE'
        )
    )
);

$events_query = new WP_Query($args);

// Create the event data array
$event_data = array();

if ($events_query->have_posts()) {
    while ($events_query->have_posts()) {
        $events_query->the_post();
        $event_date = get_post_meta(get_the_ID(), 'event_date', true);
        $event_data[] = array(
            'date' => $event_date,
            'title' => get_the_title()
        );
    }
}

// Create the calendar table
echo '<div class="wrap">';
echo '<h1 class="wp-heading-inline">Event Calendar</h1>';
echo '<table class="event-calendar">';
echo '<thead>';
echo '<tr class="month_year"> <td colspan="7">'. esc_html($current_month_name).' '. esc_html($current_year).'</td></tr>';
echo '<tr>';
echo '<th>Sun</th>';
echo '<th>Mon</th>';
echo '<th>Tue</th>';
echo '<th>Wed</th>';
echo '<th>Thu</th>';
echo '<th>Fri</th>';
echo '<th>Sat</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

// Start the first row
echo '<tr>';

// Add empty cells for days before the first day of the month
for ($i = 1; $i <= $first_day_of_month; $i++) {
    echo '<td></td>';
}
// Loop through each day of the month
for ($day = 1; $day <= $days_in_month; $day++) {
    // Get the day of the week for the current day
    $day_of_week = date('N', strtotime("{$current_year}-{$current_month}-{$day}"));

    // Check if the current day has any events
    $events_for_day = array_filter($event_data, function ($event) use ($day) {
        return date('j', strtotime($event['date'])) == $day;
    });

    // Start a new row on Sundays or after adding the last day of the month
    if ($day_of_week == 6 || $day == $days_in_month) {
        echo '<td>';
        echo '<p>' . $day . '</p>';

        // Output events for the day
        foreach ($events_for_day as $event) {
            $event_id = get_page_by_title($event['title'], OBJECT, 'events')->ID;
            // Get the URL of the event's single page
            $event_url = get_permalink($event_id);
            echo '<p><a href="' . esc_url($event_url) . '">' . esc_html($event['title']) . '</a></p>';
        }

        echo '</td>';
        if ($day < $days_in_month) {
            echo '</tr><tr>'; // Start a new row if not the last day of the month
        }
    } else {
        echo '<td>';
        echo '<p>' . $day . '</p>';

        // Output events for the day
        foreach ($events_for_day as $event) {
             // Get the event ID
             $event_id = get_page_by_title($event['title'], OBJECT, 'events')->ID;
             // Get the URL of the event's single page
             $event_url = get_permalink($event_id);
             // Output a clickable event title linked to the event's single page
            echo '<p><a href="' . esc_url($event_url) . '">' . esc_html($event['title']) . '</a></p>';
        }

        echo '</td>';
    }
}

echo '</tr>';
echo '</tbody>';
echo '</table>';
echo '</div>';

// Reset post data
wp_reset_postdata();
?>
