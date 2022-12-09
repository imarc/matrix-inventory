<?php
/**
 * Matrix Inventory module for Craft CMS 3.x
 *
 * Inventories how matrix blocks are used
 *
 * @link      https://www.imarc.com
 * @copyright Copyright (c) 2021 Linnea Hartsuyker
 */

namespace imarc\matrixinventory\variables;

use imarc\matrixinventory\MatrixInventory;

use imarc\matrixinventory\services\Inventory;

use Craft;

/**
 * Matrix Inventory Variable
 *
 * Craft allows modules to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.matrixInventoryModule }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Linnea Hartsuyker
 * @package   MatrixInventoryModule
 * @since     0.0.1
 */
class MatrixInventoryVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.matrixInventory.exampleVariable }}
     *
     * Or, if your variable requires parameters from Twig:
     *
     *     {{ craft.matrixInventory.exampleVariable(twigValue) }}
     *
     * @param null $optional
     * @return string
     */
    public function exampleVariable($optional = null)
    {
        $result = "And away we go to the Twig template...";
        if ($optional) {
            $result = "I'm feeling optional today...";
        }
        return $result;
    }

    public function matrixBlockTypes($matrixHandle = null, $siteHandle = null) {

        $result = null;
        if ($matrixHandle) {
            $result = (new Inventory)->listBlockTypes($matrixHandle, $siteHandle);
        } 
        return $result;

    }

    public function matrixFieldList() {
        $result = (new Inventory)->listMatrixFields();
        return $result;
    }

    public function listElements($handle = null, $matrixBlock = null) {
        $result = (new Inventory)->listElements($handle, $matrixBlock);
        return $result;
    }

}
