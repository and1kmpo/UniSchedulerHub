<?php

namespace App\Enums;

enum GradeStatuses: string
{
    case PASSED = 'passed';
    case FAILED = 'failed';
    case FAILED_ATTENDANCE = 'failed_attendance';
}
