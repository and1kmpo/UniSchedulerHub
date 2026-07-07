<?php

namespace App\Http\Middleware;

use App\Models\AcademicPeriod;
use App\Support\OperationalNotifications;
use App\Support\RoleNavigation;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'user.roles' => $request->user()?->roles->pluck('name') ?? [],
            'user.permissions' => $request->user()?->getPermissionsViaRoles()->pluck('name') ?? [],
            'navigation' => [
                'main' => RoleNavigation::for($request->user()),
            ],
            'i18n' => [
                'locale' => App::getLocale(),
                'supported' => [
                    ['code' => 'en', 'label' => __('ui.language.english')],
                    ['code' => 'es', 'label' => __('ui.language.spanish')],
                ],
                'messages' => trans('ui'),
            ],
            'academicContext' => function () use ($request) {
                return $this->academicContext($request);
            },
            'notifications' => function () use ($request) {
                return OperationalNotifications::for($request->user());
            },
            'flash' => function () use ($request) {
                return [
                    'success' => $request->session()->get('success'),
                    'error' => $request->session()->get('error'),
                    'info' => $request->session()->get('info'),
                ];
            },
        ]);
    }

    private function academicContext(Request $request): array
    {
        if (! $request->user()) {
            return [
                'activePeriod' => null,
            ];
        }

        $activePeriod = AcademicPeriod::with('status')->active()->latest('id')->first();

        return [
            'activePeriod' => $activePeriod ? [
                'id' => $activePeriod->id,
                'name' => $activePeriod->name,
                'status' => $activePeriod->status?->code,
                'status_label' => $activePeriod->status?->label,
                'enrollment_deadline' => $activePeriod->enrollment_deadline?->toDateString(),
                'unenrollment_deadline' => $activePeriod->unenrollment_deadline?->toDateString(),
            ] : null,
        ];
    }



    public function handle($request, Closure $next)
    {
        $locale = $request->session()->get('locale', config('app.locale'));

        if (! in_array($locale, ['en', 'es'], true)) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return parent::handle($request, $next);
    }
}
