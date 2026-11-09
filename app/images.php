<?php
/**
 * Image library.
 * Sample photography is served from Unsplash's CDN (free licence) until
 * Fitwell's own studio photos are supplied. To swap one, replace 'src' with a
 * local path such as 'assets/img/photos/hero.webp' — every page updates.
 * If a matching '-800.webp' file exists next to it, phones get the smaller copy.
 * 'src' => null (or a key that isn't listed here) shows the animated
 * brand-gradient placeholder instead. class-* keys are used by the sample classes.
 */
return [
    // Fitwell's own photos (compressed WebP, with an -800 copy for phones)
    'highfive'          => ['src' => 'assets/img/photos/friends-walking-park.webp', 'alt' => 'Four friends in workout clothes walking and chatting together in a park'],
    'hi5'               => ['src' => 'assets/img/photos/friends-walking-park.webp', 'alt' => 'Four friends in workout clothes walking and chatting together in a park'],
    'kneeling'          => ['src' => 'assets/img/photos/women-floor-workout-studio.webp', 'alt' => 'Women doing a floor workout on mats in a bright fitness studio'],
    'class-dance'       => ['src' => 'assets/img/photos/aerobics-class-studio.webp', 'alt' => 'Men and women moving together in an energetic aerobics class'],
    'class-group'       => ['src' => 'assets/img/photos/stability-ball-fitness-class.webp', 'alt' => 'A woman stretching on a stability ball during a group fitness class'],
    'corporate-outdoor' => ['src' => 'assets/img/photos/dance-fitness-class.webp', 'alt' => 'Smiling men and women dancing together in a dance fitness class'],
    'coach'             => ['src' => 'assets/img/photos/group-dance-fitness-team.webp', 'alt' => 'A cheerful group celebrating together after a dance fitness session'],
    'good-to-see-you'     => ['src' => 'assets/img/photos/good-to-see-you.jpg', 'alt' => 'A group of people in a fitness class smiling and giving each other high fives'],
    // Stock photos (Unsplash, free licence) — replace with studio photos when available
    'pair'           => ['src' => 'unsplash:1628970937415-72e1677309c6', 'alt' => 'Two women in sportswear smiling and flexing together', 'credit' => 'April Laugh / Unsplash'],
    'class-aerobics' => ['src' => 'unsplash:1635863807845-add1be925a2f', 'alt' => 'A smiling woman holding two orange kettlebells overhead', 'credit' => 'April Laugh / Unsplash'],
    'class-special'  => ['src' => 'unsplash:1771402900038-785d2b4c39b0', 'alt' => 'A cheerful group of people at a fitness event', 'credit' => 'Ben Iwara / Unsplash'],
];
