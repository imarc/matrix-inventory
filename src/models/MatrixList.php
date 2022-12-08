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
 * MatrixList Model
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Linnea Hartsuyker
 * @package   MatrixInventory
 * @since     0.0.1
 */
class MatrixList extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $dateCreated;
    public $dateUpdated;
    public $uid;
    public $siteId = 1; 
    public $matrixName = '';
    public $matrixHandle = '';

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['id', 'siteId'], 'integer'],
            [['matrixName', 'matrixHandle'], 'string'],
            [['dateCreated', 'dateUpdated'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }
}
