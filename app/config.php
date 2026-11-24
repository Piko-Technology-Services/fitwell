<?php
/**
 * FITWELL STUDIO LIMITED — site configuration
 * ------------------------------------------------------------------
 * Everything the business needs to edit lives here.
 * Any value left as null is shown on the site as a clearly marked
 * placeholder (dashed underline + "to be confirmed"), so nothing
 * invented is ever presented as real company information.
 */

return [

    /* ---------- Database (XAMPP defaults) ---------- */
    'db' => [
        'driver'   => getenv('FITWELL_DB_DRIVER') ?: 'mysql', // 'mysql' (XAMPP) — 'sqlite' is only used for automated tests
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'fitwell_studio',
        'user'     => 'root',
        'pass'     => '',
        'sqlite_path' => getenv('FITWELL_SQLITE') ?: __DIR__ . '/../storage/fitwell.sqlite',
    ],

    /* ---------- Website / SEO ---------- */
    // Full public address, no trailing slash, e.g. 'https://fitwellstudio.com'.
    // Leave null to auto-detect (works on Hostinger); set it once your domain is final.
    'site_url' => null,
    'debug'    => false,   // true shows PHP errors on screen — never on the live site
    'seo' => [
        'site_name'   => 'Fitwell Studio',
        'locale'      => 'en_GB',
        'default_description' => 'Fitwell Studio in Ndola, Zambia — energising aerobics, dance fitness and group classes for every level, plus corporate wellness for teams. Book your first class online. Together for a Better You.',
        'twitter'     => null,  // e.g. '@fitwellstudio'
        // Paste the code from Google Search Console / Bing Webmaster Tools
        // ("HTML tag" verification method) — just the content="..." value.
        'google_verification' => null,
        'bing_verification'    => null,
    ],

    /* ---------- Studio & booking rules ---------- */
    'timezone'             => 'Africa/Lusaka', // set to the studio's local timezone
    'cancel_cutoff_hours'  => 2,     // members can cancel up to this many hours before a session
    'booking_window_days'  => 28,    // how far ahead sessions are shown on the timetable
    'show_placeholder_markers' => true, // set false once all real details are filled in

    /* ---------- Company details (null = placeholder) ---------- */
    'company' => [
        'legal_name' => 'FITWELL STUDIO LIMITED',
        'short_name' => 'Fitwell',
        'slogan'     => 'Together for a Better You.',
        'email'      => 'admin@fitwellstudiozm.com',
        'phone'      => '+260 766 545 627',   // same as WhatsApp
        'address'    => null,   // studio street address (shown on the site and to Google when set)
        'city'       => 'Ndola',
        'region'     => 'Copperbelt Province',
        'country'    => 'Zambia',
        'country_code' => 'ZM',
        'hours'      => null,   // not shown on the site while empty
        'corporate_email' => null, // empty = corporate enquiries go to 'email' above
        'whatsapp'   => '+260766545627',            // WhatsApp chat number (international format)
        'whatsapp_display' => '+260 766 545 627',
        'whatsapp_message' => "Hi Fitwell Studio. I am interested in having more information about the programme, lessons, schedule, and the place. Can I have more information please?",
    ],

    /* Social links — leave null to hide or mark as placeholder */
    'social' => [
        'Instagram' => null,
        'Facebook'  => null,
        'TikTok'    => null,
        'LinkedIn'  => null,
    ],

    /* ---------- Membership options ----------
     * Prices are null until approved — the site shows "Pricing to be confirmed".
     * Edit names, notes and benefits to match the real packages.
     */
    'currency_prefix' => 'K',  // Zambian Kwacha — shown before prices (e.g. K600)
    'memberships' => [
        [
            'name'     => 'Trial Session',
            'tagline'  => 'Try your first class with no commitment.',
            'price'    => '100',
            'period'   => 'one session',
            'featured' => false,
            'benefits' => [
                'One group class of your choice',
                'Meet the instructors and the community',
                'Free member account and online booking',
            ],
        ],
        [
            'name'     => '12-Session Plan',
            'tagline'  => 'Build a steady rhythm, three days a week.',
            'price'    => '650',
            'period'   => '12 sessions · 3 days a week',
            'featured' => true,
            'benefits' => [
                '12 group sessions',
                'Train 3 days a week',
                'Aerobics, dance and group fitness',
                'Online booking and easy cancellation',
            ],
        ],
        [
            'name'     => '12 Sessions + Coaching',
            'tagline'  => 'Group energy plus one-to-one guidance.',
            'price'    => '1200',
            'period'   => '12 sessions · 3 days a week + 1 coaching session',
            'featured' => false,
            'benefits' => [
                '12 group sessions, 3 days a week',
                '1 personal coaching session',
                'Aerobics, dance and group fitness',
                'Online booking and easy cancellation',
            ],
        ],
        [
            'name'     => 'Private Coaching',
            'tagline'  => 'Fully personal training with your own coach.',
            'price'    => '1500',
            'period'   => '12 private sessions',
            'featured' => false,
            'benefits' => [
                '12 private one-to-one sessions',
                'One dedicated coach',
                'Training built around your goals and pace',
            ],
        ],
    ],

    /* Class categories used for filters and admin */
    'categories' => [
        'aerobics'      => 'Aerobics',
        'dance'         => 'Dance Fitness',
        'group-fitness' => 'Group Fitness',
        'special'       => 'Special Sessions',
        'other'         => 'Other',
    ],

    /* Instructor profiles — add real people here when ready
     * ['name' => '', 'role' => '', 'bio' => '', 'image' => 'path/or/url'] */
    'instructors' => [],
];
