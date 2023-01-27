<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use App\Models\Menu;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(function (BuildingMenu $event) {
            $pmenus = Menu::whereNull('parent_id');
            if (!auth()->user()->flg_admin) {
                $pmenus = $pmenus->where('flg_admin', 0);
            }
            $pmenus = $pmenus->orderBy('sort_number')->get();
            foreach ($pmenus as $pmenu) {
                $menu = [ 'text' => $pmenu->title ];
                if ($pmenu->icon) {
                    $menu['icon'] = $pmenu->icon;
                }
                if ($pmenu->link) {
                    $menu['url'] = url($pmenu->link);
                }
                $submenus = [];
                $cmenus = Menu::where('parent_id', $pmenu->id);
                if (!auth()->user()->flg_admin) {
                    $cmenus = $cmenus->where('flg_admin', 0);
                }
                $cmenus = $cmenus->orderBy('sort_number')->get();
                foreach ($cmenus as $cmenu) {
                    $submenu = [ 'text' => $cmenu->title ];
                    if ($cmenu->icon) {
                        $submenu['icon'] = $cmenu->icon;
                    }
                    if ($cmenu->link) {
                        $submenu['url'] = url($cmenu->link);
                    }                
                    $submenus[] = $submenu;
                }
                if (count($submenus) > 0) {
                    $menu['submenu'] = $submenus;
                }
                $event->menu->add($menu);
            }
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
