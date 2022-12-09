<?php
/**
 * Matrix Inventory plugin for Craft CMS 3.x
 *
 * Inventories the use of matrix fields
 *
 * @link      https://www.imarc.com
 * @copyright Copyright (c) 2021 Linnea Hartsuyker
 */

namespace imarc\matrixinventory\models;

use imarc\matrixinventory\MatrixInventory;

use Craft;
use craft\base\Model;

/**
 * BlockList Model
 *
 * @author    Linnea Hartsuyker
 * @package   MatrixInventory
 * @since     0.0.1
 */
class BlockList extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $dateCreated;
    public $dateUpdated;
    public $uid;
    public $siteId = 1; 
    public $matrixHandle = '';
    public $blockHandle = '';
    public $enabled = true;
    public $elementId;
    public $elementType;
    public $blockId;

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'siteId', 'elementId', 'blockId'], 'integer'],
            [['matrixHandle', 'blockHandle', 'elementType'], 'string'],
            [['enabled'], 'boolean'],
            [['dateCreated', 'dateUpdated'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }
}
