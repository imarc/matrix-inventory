<?php
/**
 * Matrix Inventory plugin for Craft CMS 3.x
 *
 * Inventories the use of matrix fields
 *
 * @link      https://www.imarc.com
 * @copyright Copyright (c) 2021 Linnea Hartsuyker
 */

namespace imarc\matrixinventory\jobs;

use imarc\matrixinventory\MatrixInventory;
use imarc\matrixinventory\services\Inventory as InventoryService;

use Craft;
use craft\queue\BaseJob;

/**
 * MatrixList job
 *
 * @author    Linnea Hartsuyker
 * @package   MatrixInventory
 * @since     0.0.1
 */
class MatrixList extends BaseJob
{

    // Public Methods
    // =========================================================================

    public function execute($queue): void
    {
        $inventoryService = new InventoryService();
        $inventoryService->storeMatrixFields();
    }

    // Protected Methods
    // =========================================================================

    /**
     * Returns a default description for [[getDescription()]], if [[description]] isn’t set.
     *
     * @return string The default task description
     */
    protected function defaultDescription(): string
    {
        return Craft::t('matrix-inventory', 'Create Matrix List');
    }
}
