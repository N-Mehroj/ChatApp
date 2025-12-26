<?php

namespace App\Http\Middleware;

use App\Models\AdminMenu;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = null;
        $menuItems = [];

        if (Auth::check()) {
            $user = Auth::user();

            $menuItems = AdminMenu::with(['children' => fn(HasMany $query) => $query->active()->orderBy('sort')])
                ->active()
                ->where('parent_id', null)
                ->orderBy('sort')
                ->get()
                ->toArray();

            foreach ($menuItems as $key => &$menuItem) {
                if ($menuItem['permission'] && !$user->hasPermissionTo($menuItem['permission']) && !$user->hasRole('admin')) {
                    unset($menuItems[$key]);
                    continue;
                }

                if (isset($menuItem['children']) && count($menuItem['children'])) {
                    $menuItem['subMenu'] = $menuItem['children'];

                    foreach ($menuItem['subMenu'] as $subItemKey => $subItem) {
                        if ($subItem['permission'] && !$user->hasPermissionTo($subItem['permission']) && !$user->hasRole('admin')) {
                            unset($menuItem['subMenu'][$subItemKey]);
                            continue;
                        }

                        $pageUrl = $request->getRequestUri();

                        $firstPattern = '/' . str_replace('/', '\/', $subItem['link']) . '\/[0-9]\/edit/';
                        $secondPattern = '/' . str_replace('/', '\/', $subItem['link']) . '\/create/';

                        if ($pageUrl == $subItem['link']) {
                            $menuItem['activeDropdown'] = true;
                        }

                        if (preg_match($firstPattern, $pageUrl) || preg_match($secondPattern, $pageUrl)) {
                            $menuItem['activeDropdown'] = true;
                        }
                    }
                } else {
                    $menuItem['activeDropdown'] = false;
                    $menuItem['subMenu'] = null;
                }
            }

            $menuItems = array_values($menuItems);
        }

        return array_merge(parent::share($request), [
            'sideMenu' => $menuItems,
            'auth' => $user
        ]);
    }
}
