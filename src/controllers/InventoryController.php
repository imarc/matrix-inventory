<?php
/**
 * Matrix Inventory plugin for Craft CMS 3.x
 *
 * Inventories the use of matrix fields
 *
 * @link      https://www.imarc.com
 * @copyright Copyright (c) 2021 Linnea Hartsuyker
 */

namespace imarc\matrixinventory\controllers;

use imarc\matrixinventory\MatrixInventory;
use imarc\matrixinventory\services\Inventory as InventoryService;
use imarc\matrixinventory\jobs\MatrixList as MatrixListJob;

use imarc\matrixinventory\services\Inventory;



use Craft;
use craft\web\Controller;

/**
 * Inventory Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Linnea Hartsuyker
 * @package   MatrixInventory
 * @since     0.0.1
 */
class InventoryController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index', 'do-something'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/matrix-inventory/inventory
     *
     * @return mixed
     */
    public function actionIndex()
    {
        set_time_limit(0);
        ini_set('memory_limit', '8024M');

        $result = (new Inventory)->listAllMatrixes();

        return $result;
    }

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/matrix-inventory/inventory/do-something
     *
     * @return mixed
     */
    public function actionDoSomething()
    {
        $result = 'Welcome to the InventoryController actionDoSomething() method';

        return $result;
    }

    public function actionReindex()
    {
        $job = new MatrixListJob();
        Craft::$app->queue->push($job); 

        $inventoryService = new InventoryService();
        $inventoryService->storeAllMatrixes();

        return;
    }
}
