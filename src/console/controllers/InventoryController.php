<?php
/**
 * Matrix Inventory plugin for Craft CMS 3.x
 *
 * Inventories the use of matrix fields
 *
 * @link      https://www.imarc.com
 * @copyright Copyright (c) 2021 Linnea Hartsuyker
 */

namespace imarc\matrixinventory\console\controllers;

use imarc\matrixinventory\MatrixInventory;

use Craft;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Inventory Command
 *
 * The first line of this class docblock is displayed as the description
 * of the Console Command in ./craft help
 *
 * Craft can be invoked via commandline console by using the `./craft` command
 * from the project root.
 *
 * Console Commands are just controllers that are invoked to handle console
 * actions. The segment routing is plugin-name/controller-name/action-name
 *
 * The actionIndex() method is what is executed if no sub-commands are supplied, e.g.:
 *
 * ./craft matrix-inventory/inventory
 *
 * Actions must be in 'kebab-case' so actionDoSomething() maps to 'do-something',
 * and would be invoked via:
 *
 * ./craft matrix-inventory/inventory/do-something
 *
 * @author    Linnea Hartsuyker
 * @package   MatrixInventory
 * @since     0.0.1
 */
class InventoryController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Handle matrix-inventory/inventory console commands
     *
     * The first line of this method docblock is displayed as the description
     * of the Console Command in ./craft help
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $result = 'something';

        echo "Welcome to the console InventoryController actionIndex() method\n";

        return $result;
    }

    /**
     * Handle matrix-inventory/inventory/do-something console commands
     *
     * The first line of this method docblock is displayed as the description
     * of the Console Command in ./craft help
     *
     * @return mixed
     */
    public function actionDoSomething()
    {
        $result = 'something';

        echo "Welcome to the console InventoryController actionDoSomething() method\n";

        return $result;
    }
}
