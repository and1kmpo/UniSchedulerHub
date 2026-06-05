<?php

namespace App\Support;

class PermissionCatalog
{
    public const MANAGE_USERS = 'manage users';
    public const MANAGE_ROLES = 'manage roles';
    public const MANAGE_PROGRAMS = 'manage programs';
    public const MANAGE_SUBJECTS = 'manage subjects';
    public const MANAGE_PROFESSORS = 'manage professors';
    public const MANAGE_STUDENTS = 'manage students';
    public const MANAGE_ACADEMIC_PERIODS = 'manage academic periods';
    public const MANAGE_INFRASTRUCTURE = 'manage infrastructure';
    public const MANAGE_CLASS_GROUPS = 'manage class groups';
    public const MANAGE_ENROLLMENTS = 'manage enrollments';
    public const MANAGE_GRADES = 'manage grades';
    public const VIEW_PROFESSOR_SUBJECTS = 'view professor subjects';
    public const VIEW_STUDENT_SUBJECTS = 'view student subjects';
    public const VIEW_STUDENT_SCHEDULE = 'view student schedule';
    public const VIEW_PROFESSOR_SCHEDULE = 'view professor schedule';
    public const SELF_ENROLL = 'self enroll subjects';
    public const VIEW_REPORTS = 'view reports';
    public const VIEW_AUDIT_LOGS = 'view audit logs';

    public static function all(): array
    {
        return array_values(array_unique(array_merge(...array_values(self::byRole()))));
    }

    public static function byRole(): array
    {
        return [
            'admin' => [
                self::MANAGE_USERS,
                self::MANAGE_ROLES,
                self::MANAGE_PROGRAMS,
                self::MANAGE_SUBJECTS,
                self::MANAGE_PROFESSORS,
                self::MANAGE_STUDENTS,
                self::MANAGE_ACADEMIC_PERIODS,
                self::MANAGE_INFRASTRUCTURE,
                self::MANAGE_CLASS_GROUPS,
                self::MANAGE_ENROLLMENTS,
                self::MANAGE_GRADES,
                self::VIEW_PROFESSOR_SUBJECTS,
                self::VIEW_STUDENT_SUBJECTS,
                self::VIEW_STUDENT_SCHEDULE,
                self::VIEW_PROFESSOR_SCHEDULE,
                self::SELF_ENROLL,
                self::VIEW_REPORTS,
                self::VIEW_AUDIT_LOGS,
            ],
            'academic_coordinator' => [
                self::MANAGE_PROGRAMS,
                self::MANAGE_SUBJECTS,
                self::MANAGE_PROFESSORS,
                self::MANAGE_STUDENTS,
                self::MANAGE_ACADEMIC_PERIODS,
                self::MANAGE_INFRASTRUCTURE,
                self::MANAGE_CLASS_GROUPS,
                self::MANAGE_ENROLLMENTS,
                self::VIEW_REPORTS,
                self::VIEW_AUDIT_LOGS,
            ],
            'professor' => [
                self::MANAGE_GRADES,
                self::VIEW_PROFESSOR_SUBJECTS,
                self::VIEW_PROFESSOR_SCHEDULE,
            ],
            'student' => [
                self::VIEW_STUDENT_SUBJECTS,
                self::VIEW_STUDENT_SCHEDULE,
                self::SELF_ENROLL,
            ],
        ];
    }
}
