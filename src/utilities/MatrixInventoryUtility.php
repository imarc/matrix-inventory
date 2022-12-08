<?php
/**
 * Matrix Inventory plugin for Craft CMS 3.x
 *
 * Inventories the use of matrix fields
 *
 * @link      https://www.imarc.com
 * @copyright Copyright (c) 2021 Linnea Hartsuyker
 */

namespace imarc\matrixinventory\utilities;

use imarc\matrixinventory\assetbundles\matrixinventoryutility\MatrixInventoryUtilityAsset;
use imarc\matrixinventory\variables\MatrixInventoryVariable;

use Craft;
use craft\base\Utility;

class MatrixInventoryUtility extends Utility
{
    // Static
    // =========================================================================

    /**
     * Returns the display name of this utility.
     *
     * @return string The display name of this utility.
     */
    public static function displayName(): string
    {
        return Craft::t('matrix-inventory', 'Matrix Inventory Utility');
    }

    /**
     * Returns the utility’s unique identifier in kebab-case
     */
    public static function id(): string
    {
        return 'matrixinventoryplugin-matrixinventory-plugin-utility';
    }

    /**
     * Returns the path to the utility's SVG icon.
     *
     * @return string|null The path to the utility SVG icon
     */
    public static function iconPath()
    {
        return Craft::getAlias("@imarc/matrixinventory/assetbundles/matrixinventoryutility/dist/img/MatrixInventoryUtility-icon.svg");
    }
    
    /**
     * Returns the utility's content HTML.
     */
    public static function contentHtml(): string
    {
        Craft::$app->getView()->registerAssetBundle(MatrixInventoryUtilityAsset::class);

        
        return Craft::$app->getView()->renderTemplate(
            'matrix-inventory/_components/utilities/MatrixInventoryUtility_content',
            [
            ]
        );
    }

}
