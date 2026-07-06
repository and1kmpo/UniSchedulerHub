<?php

namespace App\Support;

use App\Models\User;

class RoleNavigation
{
    public static function for(?User $user): array
    {
        if (! $user) {
            return [];
        }

        if ($user->hasRole('admin')) {
            return self::admin();
        }

        if ($user->hasRole('academic_coordinator')) {
            return self::academicCoordinator();
        }

        if ($user->hasRole('professor')) {
            return self::professor();
        }

        if ($user->hasRole('student')) {
            return self::student();
        }

        return [];
    }

    private static function admin(): array
    {
        return [
            self::group('Insights', [
                self::item('Dashboard', 'dashboard'),
                self::item('Reports', 'reports.index'),
                self::item('Audit Logs', 'academic-audit-logs.index'),
            ]),
            self::group('Core', [
                self::item('Programs', 'programs.index'),
                self::item('Subjects', 'subjects.index'),
                self::item('Professors', 'professors.index'),
                self::item('Students', 'students.index'),
                self::item('Class Groups', 'class-groups.index'),
            ]),
            self::group('Sync', [
                self::item('Enrollment Management', 'admin.group-enrollments.index'),
                self::item('Academic Periods', 'academic-periods.index'),
            ]),
            self::group('Rooms', [
                self::item('Buildings', 'buildings.index'),
                self::item('Classrooms', 'classrooms.index'),
            ]),
            self::group('Admin', [
                self::item('Identity & Access', 'users.index'),
            ]),
        ];
    }

    private static function academicCoordinator(): array
    {
        return [
            self::group('Insights', [
                self::item('Dashboard', 'dashboard'),
                self::item('Reports', 'reports.index'),
                self::item('Audit Logs', 'academic-audit-logs.index'),
            ]),
            self::group('Core', [
                self::item('Programs', 'programs.index'),
                self::item('Subjects', 'subjects.index'),
                self::item('Professors', 'professors.index'),
                self::item('Students', 'students.index'),
                self::item('Class Groups', 'class-groups.index'),
            ]),
            self::group('Sync', [
                self::item('Enrollment Management', 'admin.group-enrollments.index'),
                self::item('Academic Periods', 'academic-periods.index'),
            ]),
            self::group('Rooms', [
                self::item('Buildings', 'buildings.index'),
                self::item('Classrooms', 'classrooms.index'),
            ]),
        ];
    }

    private static function professor(): array
    {
        return [
            self::group('Insights', [
                self::item('Dashboard', 'dashboard'),
            ]),
            self::group('Teaching', [
                self::item('My Subjects', 'professor.subjects'),
                self::item('My Schedule', 'professor.schedule'),
                self::item('Group Enrollments', 'admin.group-enrollments.index'),
            ]),
            self::group('Account', [
                self::item('Profile', 'profile.show'),
            ]),
        ];
    }

    private static function student(): array
    {
        return [
            self::group('Student Flow', [
                self::item('My Subjects', 'student.subjects'),
                self::item('My Schedule', 'student.schedule'),
                self::item('Academic Record', 'student.academic-record'),
                self::item('Subject Enrollment', 'student.subject-enrollment.index'),
            ]),
            self::group('Account', [
                self::item('Profile', 'profile.show'),
            ]),
        ];
    }

    private static function item(string $label, string $routeName): array
    {
        return [
            'label' => $label,
            'route' => $routeName,
        ];
    }

    private static function group(string $label, array $children): array
    {
        return [
            'label' => $label,
            'children' => $children,
        ];
    }
}
