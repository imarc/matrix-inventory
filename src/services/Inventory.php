<?php
/**
 * Matrix Inventory plugin for Craft CMS 3.x
 *
 * Inventories the use of matrix fields
 *
 * @link      https://www.imarc.com
 * @copyright Copyright (c) 2021 Linnea Hartsuyker
 */

namespace imarc\matrixinventory\services;

use imarc\matrixinventory\MatrixInventory;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\elements\MatrixBlock;

/**
 * Inventory Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Linnea Hartsuyker
 * @package   MatrixInventory
 * @since     0.0.1
 */
class Inventory extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     MatrixInventory::$plugin->inventory->exampleService()
     *
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';
        // Check our Plugin's settings for `someAttribute`
        if (MatrixInventory::$plugin->getSettings()->someAttribute) {
        }

        return $result;
    }

    public function listAllMatrixes()
    {
        //$matrixBlock = MatrixBlock::find()->limit(10)->one();
        $returnString = "";
        $sections = Craft::$app->sections->allSections;

        $inventory = [];
        
        foreach ($sections as $section) {
            //$returnString .= "Section: " . $section->handle . "<br/><br/>";
            $entries = Entry::find()->section($section->handle)->all();
            foreach ($entries as $entry) {
                $entryFields = $entry->getFieldLayout()->getFields();
                foreach ($entryFields as $fieldLayout) {
                    $field = Craft::$app->fields->getFieldById($fieldLayout->id);
                    if (get_class($field) == 'craft\\fields\\Matrix') {
                        //$returnString .= "Field: " . $field->handle . "<br/><br/>";
                        if (!array_key_exists($field->handle, $inventory)) {
                            $inventory[$field->handle] = [];
                        }
                        $matrixBlocks = $entry->getFieldValue($field->handle);
                        foreach ($matrixBlocks as $block) {
                            if (array_key_exists($block->type->handle, $inventory[$field->handle])){
                                $inventory[$field->handle][$block->type->handle] += 1;
                            } else {
                                $inventory[$field->handle][$block->type->handle] = 1;
                            }
                            
                        }
                        
                    }
                }
            }
        }

        foreach ($inventory as $key => $val) {
            ksort($val);
            $inventory[$key] = $val;
            //$returnString .= $key . " = " . json_encode(ksort($val)) . "<br/><br/>";
        }

        $matrixBlock = MatrixBlock::find()->limit(10)->one();
        $returnString .= json_encode($matrixBlock) . '<br/><br/>';

        $field = Craft::$app->fields->getFieldById($matrixBlock->fieldId);
        $returnString .= json_encode($field->blockTypes) . '<br/><br/>';

        $returnString .= json_encode($inventory);
        return $returnString;

    }

    /**
     * TO DO: Make this show disabled and enabled
     */

    public function listBlockTypes($matrixHandle = null) {
        $blockTypes = null;
        $inventory = [];
        if ($matrixHandle) {
            $field = Craft::$app->fields->getFieldByHandle($matrixHandle);
            if ($field) {
                $blockTypes = $field->blockTypes;
                foreach ($blockTypes as $block) {
                    $inventory[$block->handle]["enabled"] = 0;
                    $inventory[$block->handle]["disabled"] = 0;
                }
                if ($blockTypes) {
                    $sections = Craft::$app->sections->allSections;
                    foreach ($sections as $section) {
                        $entries = Entry::find()->section($section->handle)->anyStatus()->all();
                        foreach ($entries as $entry) {
                            $entryFields = $entry->getFieldLayout()->getFields();
                            foreach ($entryFields as $fieldLayout) {
                                $field = Craft::$app->fields->getFieldById($fieldLayout->id);
                                if (get_class($field) == 'craft\\fields\\Matrix' && $field->handle == $matrixHandle) {
                                    $matrixBlocks = $entry->getFieldValue($field->handle);
                                    foreach ($matrixBlocks as $block) {
                                        if (array_key_exists($block->type->handle, $inventory)){
                                            if ($entry->status == 'live') {
                                                $inventory[$block->type->handle]["enabled"] += 1;
                                            } else {
                                                $inventory[$block->type->handle]["disabled"] += 1;
                                            }
                                        } 
                                        
                                    }
                                    
                                }
                            }
                        }
                    }
            
                }
            }
        }
        ksort($inventory);
        return $inventory;
    }

    public function listMatrixFields() {
        $result = null;
        $fields = Craft::$app->fields->getAllFields(false);
        $matrixList = [];
        $handles = [];
        foreach ($fields as $field) {
            if (get_class($field) == 'craft\\fields\\Matrix') {
                if (!in_array($field->handle, $handles)) {
                    array_push($matrixList, ["name" => $field->name, "handle" => $field->handle]);
                    array_push($handles, $field->handle);
                }
                
            }
        }
        return $matrixList;
    }

    public function listEntries($handle = null, $matrixBlock = null) {
        $entries = [];
        $allEntries = Entry::find()->anyStatus()->all();
        if ($matrixBlock && $handle) {
            //return "Here";
            foreach ($allEntries as $entry) {
                $entryFields = $entry->getFieldLayout()->getFields();
                foreach ($entryFields as $fieldLayout) {
                    $field = Craft::$app->fields->getFieldById($fieldLayout->id);
                    if (get_class($field) == 'craft\\fields\\Matrix' && $field->handle == $handle) {
                        //return $field;
                        $matrixBlocks = $entry->getFieldValue($field->handle);
                        foreach ($matrixBlocks as $block) {
                            if ($block->type == $matrixBlock) {
                                if (!in_array($entry, $entries)) {
                                    array_push($entries, $entry);
                                }
                            }
                        }
                    }
                }
            
            }
        }
        return $entries;
    }

}
