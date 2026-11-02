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
        'default_description' => 'Fitwell Studio — energising aerobics, dance fitness and group classes for every level, plus corporate wellness for teams. Book your first class online. Together for a Better You.',
        'twitter'     => null,  // e.g. '@fitwellstudio'
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
        'address'    => null,   // studio street address
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
            'name'     => 'Drop-In',
            'tagline'  => 'Try a class, no commitment.',
            'price'    => '100',
            'period'   => 'per class',
            'featured' => false,
            'benefits' => [
                'Book any single scheduled class',
                'Perfect for your first session',
                'Free member account and online booking',
            ],
        ],
        [
            'name'     => 'Monthly Membership',
            'tagline'  => 'For people ready to build a rhythm.',
            'price'    => '600',
            'period'   => 'per month',
            'featured' => true,
            'benefits' => [
                'Book scheduled group classes all month',
                'Priority access to special sessions',
                'Manage bookings from your phone',
                'Welcome session with an instructor',
            ],
        ],
        // [
        //     'name'     => 'Class Pack',
        //     'tagline'  => 'Flexible sessions on your schedule.',
        //     'price'    => null,
        //     'period'   => 'per pack',
        //     'featured' => false,
        //     'benefits' => [
        //         'A set number of classes to use your way',
        //         'Mix aerobics, dance and group fitness',
        //         'Online booking and easy cancellation',
        //     ],
        // ],
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
