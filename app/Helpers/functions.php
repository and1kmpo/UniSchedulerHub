<?php

use App\Models\AcademicPeriod;

if (!function_exists('currentAcademicPeriodId')) {
    function currentAcademicPeriodId(): ?int
    {
        return AcademicPeriod::where('is_active', true)->value('id');
    }
}
