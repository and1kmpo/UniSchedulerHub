<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enrollment domain rules
    |--------------------------------------------------------------------------
    */

    // Statuses that count as an active enrollment.
    'active_status_codes' => [
        'pre_enrolled',
        'enrolled',
    ],

    // Default status assigned when a student enrolls.
    'default_status_code' => 'pre_enrolled',

    // Minimum academic load expected for a regular enrollment period.
    'min_credits' => 7,

    // Maximum academic load for a regular enrollment period.
    'max_credits' => 21,

    // Maximum credits allowed for students on academic probation.
    'probation_max_credits' => 12,
];
