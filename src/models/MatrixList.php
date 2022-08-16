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
 * Inventory Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
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

    /**
     * Some model attribute
     *
     * @var string
     */
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
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'siteId'], 'integer'],
            [['matrix_name', 'matrix_handle'], 'string']
        ];
    }
}
