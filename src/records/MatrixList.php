<?php
/**
 * Matrix Inventory plugin for Craft CMS 3.x
 *
 * Inventories the use of matrix fields
 *
 * @link      https://www.imarc.com
 * @copyright Copyright (c) 2021 Linnea Hartsuyker
 */

namespace imarc\matrixinventory\records;

use imarc\matrixinventory\MatrixInventory;

use Craft;
use craft\db\ActiveRecord;

/**
 * MatrixList Record
 *
 * @author    Linnea Hartsuyker
 * @package   MatrixInventory
 * @since     0.0.1
 */
class MatrixList extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

     /**
     * @return string the table name
     */
    public static function tableName(): string
    {
        return '{{%matrixinventory_matrixlist}}';
    }
}
