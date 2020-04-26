<?php namespace Xitara\Core;

use App;
use Backend;
use BackendMenu;
use Event;
use System\Classes\PluginBase;
use System\Classes\PluginManager;

class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => 'xitara.core::lang.plugin.name',
            'description' => 'xitara.core::lang.plugin.description',
            'author' => 'xitara.core::lang.plugin.author',
            'homepage' => 'xitara.core::lang.plugin.homepage',
            'icon' => '',
        ];
    }

    public function register()
    {
        BackendMenu::registerContextSidenavPartial(
            'Xitara.Core',
            'core',
            '$/xitara/core/partials/_sidebar.htm'
        );
    }

    public function boot()
    {
        // Check if we are currently in backend module.
        if (!App::runningInBackend()) {
            return;
        }

        /**
         * add items to sidemenu
         */
        $this->getSideMenu('Xitara.Core', 'core');

        /**
         * Listen for `backend.page.beforeDisplay` event
         * and inject js to current controller instance.
         */
        Event::listen('backend.page.beforeDisplay', function ($controller) {
            // $controller->addCss("/plugins/xitara/core/assets/css/app.css", "1.0.0");
            // $controller->addJs("/plugins/xitara/core/assets/js/app.js", "1.0.0");
        });
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [
            'core' => [
                'label' => 'xitara.core::lang.plugin.name',
                'url' => Backend::url('xitara/core/dashboard'),
                'icon' => 'icon-leaf',
                'iconSvg' => 'plugins/xitara/core/assets/icon.svg',
                'permissions' => ['xitara.core.*'],
                'order' => 200,
            ],
        ];
    }

    /**
     * grab sidemenu items
     * $inject contains addidtional menu-items with the following strcture
     *
     * name = [
     *     group => [string],
     *     label => [string],
     *     url => [string], (full backend url)
     *     icon => [string],
     * ]
     *
     * name -> unique name
     * group -> same group where to inject
     * label -> shown name in menu
     * url -> url relative to backend
     *
     * @autor   mburghammer
     * @date    2018-05-15T20:49:04+0100
     * @version 0.0.2
     * @since   0.0.1
     * @param   string                   $owner
     * @param   string                   $code
     * @param   array                   $inject
     */
    public static function getSideMenu(string $owner, string $code)
    {
        $items = [
            'dashboard' => [
                'group' => 'xitara.core::lang.core.mainmenu',
                'label' => 'xitara.core::lang.core.dashboard',
                'url' => Backend::url('xitara/core/dashboard'),
                'icon' => 'icon-archive',
            ],
        ];

        foreach (PluginManager::instance()->getPlugins() as $name => $plugin) {
            // if (strpos($name, 'Xitara.' . self::PLUGIN_SUFFIX) !== false) {
            $namespace = str_replace('.', '\\', $name) . '\Plugin';

            if (method_exists($namespace, 'injectSideMenu')) {
                $inject = $namespace::injectSideMenu();
                $items = array_merge($items, $inject);
            }
            // }
        }

        Event::listen('backend.menu.extendItems', function ($manager) use ($owner, $code, $items) {
            $manager->addSideMenuItems($owner, $code, $items);
        });
    }
}
