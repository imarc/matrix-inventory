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
class BlockList extends BaseJob
{
    // Public Properties
    // =========================================================================

    /**
     * Some attribute
     *
     * @var string
     */
    protected $section = null;

    // Public Methods
    // =========================================================================

    public function setSection($section) {
        $this->section = $section;
    }
    
    public function execute($queue)
    {
        $inventoryService = new InventoryService();
        $inventoryService->storeSectionMatrixes($this->section);
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
        return Craft::t('matrix-inventory', 'Create Block List');
    }
}
