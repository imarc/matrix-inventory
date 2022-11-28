<?php
/**
 * Matrix Inventory plugin for Craft CMS 3.x
 *
 * Inventories the use of matrix fields
 *
 * @link      https://www.imarc.com
 * @copyright Copyright (c) 2021 Linnea Hartsuyker
 */

 namespace imarc\matrixinventory\assetbundles\matrixinventoryutility;

 use craft\web\AssetBundle;
 use craft\web\assets\cp\CpAsset;

/**
 * MatrixInventoryUtilityAsset AssetBundle
 * 
 * Defines the asset bundle for the screen a CMS admin can refresh inventory fields
 * templates/components/utilities/MatrixInventoryUtility_content.twig. Currently only the image is used
 * 
 * @author    Linnea Hartsuyker
 * @package   MatrixInventory
 * @since     1.0.0
 */
class MatrixInventoryUtilityAsset extends AssetBundle 
 {

    // Public Methods

    /**
     * Initializes the bundle
     */

     public function init() {
        $this->sourcePath = "@imarc/matrixinventory/assetbundles/matrixinventoryutility/dist";

        $this->depends = [
            CpAsset::class,
        ];

        parent::init();

     }

 }
