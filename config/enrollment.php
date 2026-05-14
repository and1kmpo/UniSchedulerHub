<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enrollment domain rules
    |--------------------------------------------------------------------------
    */

    // Estados que cuentan como inscripción activa
    'active_status_codes' => [
        'pre_enrolled',
        'enrolled',
    ],

    // Estado por defecto al inscribir
    'default_status_code' => 'pre_enrolled',

    // Máximo de créditos en probation
    'probation_max_credits' => 12,
];
