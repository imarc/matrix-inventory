<?php
/**
 * Matrix Inventory plugin for Craft CMS 3.x
 *
 * Inventories the use of matrix fields
 *
 * @link      https://www.imarc.com
 * @copyright Copyright (c) 2021 Linnea Hartsuyker
 */

namespace imarc\matrixinventory;

use imarc\matrixinventory\services\Inventory as InventoryService;
use imarc\matrixinventory\variables\MatrixInventoryVariable;
use imarc\matrixinventory\utilities\MatrixInventoryUtility;
use imarc\matrixinventory\models\Settings;
use imarc\matrixinventory\jobs\MatrixList as MatrixListJob;
use imarc\matrixinventory\jobs\BlockList as BlockListJob;
use craft\helpers\ElementHelper;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\services\Elements;
use craft\services\Utilities;
use craft\events\PluginEvent;
use craft\events\ElementEvent;
use craft\elements\Entry;
use craft\web\twig\variables\CraftVariable;
use craft\console\Application as ConsoleApplication;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterComponentTypesEvent;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
 *
 * @author    Linnea Hartsuyker
 * @package   MatrixInventory
 * @since     0.0.1
 *
 * @property  InventoryService $inventory
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class MatrixInventory extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * MatrixInventory::$plugin
     *
     * @var MatrixInventory
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public string $schemaVersion = '0.0.2';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public bool $hasCpSettings = true;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public bool $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * MatrixInventory::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Add in our console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'imarc\matrixinventory\console\controllers';
        }

        // Register our site routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['siteActionTrigger1'] = 'matrix-inventory/inventory';
            }
        );

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['cpActionTrigger1'] = 'matrix-inventory/inventory/do-something';
            }
        );

        // Register our utilities
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = MatrixInventoryUtility::class;
            }
        );

         // Register our variables
         Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('matrixInventory', MatrixInventoryVariable::class);
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed, now index all of the existing matrixes
                    $job = new MatrixListJob();
                    Craft::$app->queue->push($job); 

                    $inventoryService = new InventoryService();
                    $inventoryService->storeAllMatrixes();

                }
            }
        );

/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(
            Craft::t(
                'matrix-inventory',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );

        $this->configureHooks();
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'matrix-inventory/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }

    // Private Methods
    
    private function configureHooks() {
        Event::on(Elements::class, Elements::EVENT_AFTER_SAVE_ELEMENT, function(ElementEvent $event) {
            if ($event->element instanceof Entry) {               
                $entry = $event->element;
                if (ElementHelper::isDraftOrRevision($entry)) {
                    return;
                } else {
                    $inventoryService = new InventoryService();
                    $inventoryService->updateEntryMatrixes($entry);
                }
                
            }
        });
    }
}
