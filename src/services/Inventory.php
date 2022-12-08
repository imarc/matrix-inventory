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
use imarc\matrixinventory\records\MatrixList as MatrixListRecord;
use imarc\matrixinventory\records\BlockList as BlockListRecord;
use imarc\matrixinventory\models\MatrixList as MatrixListModel;
use imarc\matrixinventory\models\BlockList as BlockListModel;
use imarc\matrixinventory\jobs\BlockList as BlockListJob;

use Craft;
use Craft\db\Query;
use craft\base\Component;
use craft\elements\Entry;
use craft\elements\MatrixBlock;

use DateTime;

use Exception;
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
        $returnString = "";
        $sections = Craft::$app->sections->allSections;

        $inventory = [];
        
        foreach ($sections as $section) {
            $entries = Entry::find()->section($section->handle)->all();
            foreach ($entries as $entry) {
                $entryFields = $entry->getFieldLayout()->getFields();
                foreach ($entryFields as $fieldLayout) {
                    $field = Craft::$app->fields->getFieldById($fieldLayout->id);
                    if ($field::class == 'craft\\fields\\Matrix') {
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
        }

        $matrixBlock = MatrixBlock::find()->limit(10)->one();
        $returnString .= json_encode($matrixBlock, JSON_THROW_ON_ERROR) . '<br/><br/>';

        $field = Craft::$app->fields->getFieldById($matrixBlock->fieldId);
        $returnString .= json_encode($field->blockTypes, JSON_THROW_ON_ERROR) . '<br/><br/>';

        $returnString .= json_encode($inventory, JSON_THROW_ON_ERROR);
        return $returnString;

    }

    public function storeAllMatrixes()
    {
        $sections = Craft::$app->sections->allSections;
        foreach ($sections as $section) {
            $job = new BlockListJob();
            $job->setSection($section);
            Craft::$app->queue->push($job);
        }
    }

    public function removeAllMatrixes()
    {
        //write code here
    }
    

    public function storeSectionMatrixes($section) {
        if ($section) {
            $entries = Entry::find()->section($section->handle)->anyStatus()->all();
            foreach ($entries as $entry) {
                $blockRecords = (new BlockListRecord())->find()
                    ->where([
                        'entryId' => $entry->id,
                        'siteId' => $entry->siteId
                    ])->all();
                foreach ($blockRecords as $record) {
                    $record->delete();
                }
                $entryFields = $entry->getFieldLayout()->getFields();
                foreach ($entryFields as $fieldLayout) {
                    $field = Craft::$app->fields->getFieldById($fieldLayout->id);
                    if ($field::class == 'craft\\fields\\Matrix') {
                        $matrixBlocks = $entry->getFieldValue($field->handle);
                        foreach ($matrixBlocks as $block) {
                            $model = new BlockListModel();

                            $blockRecord = new BlockListRecord();
                            if ($entry->status == 'live') {
                                $model->enabled = true;
                            } else {
                                $model->enabled = false;
                            } 
                            $now = new DateTime();
                            $model->dateCreated = $now->format('Y-m-d H:i:s');
                            $model->dateUpdated = $now->format('Y-m-d H:i:s');

                            $model->matrixHandle = $field->handle;
                            $model->blockHandle = $block->type->handle;
                            $model->blockId = $block->id;
                            $model->entryId = $entry->id;
                            $model->siteId = $entry->siteId;
                            Craft::trace("storeAllMatrixes model:" . json_encode($model->getAttributes(), JSON_THROW_ON_ERROR));
                            $blockRecord->setAttributes($model->getAttributes(), false);
                            $blockRecord->save();
                        }
                    }
                }
            }
        }
    }

    public function updateEntryMatrixes($entry) {
        $entryFields = $entry->getFieldLayout()->getFields();
        foreach ($entryFields as $fieldLayout) {
            $field = Craft::$app->fields->getFieldById($fieldLayout->id);
            if ($field::class == 'craft\\fields\\Matrix') {
                $matrixBlocks = $entry->getFieldValue($field->handle);
                $blockRecords = (new BlockListRecord())->find()
                                ->where([
                                    'entryId' => $entry->id,
                                    'siteId' => $entry->siteId
                                ])->all();
                foreach ($blockRecords as $record) {
                    $record->delete();
                }
                foreach ($matrixBlocks as $block) {
                    $blockRecord = new BlockListRecord();

                    $model = new BlockListModel();
                    if ($entry->status == 'live') {
                        $model->enabled = true;
                    } else {
                        $model->enabled = false;
                    } 
                    $now = new DateTime();
                    $model->dateCreated = $now->format('Y-m-d H:i:s');
                    $model->dateUpdated = $now->format('Y-m-d H:i:s');

                    $model->matrixHandle = $field->handle;
                    $model->blockHandle = $block->type->handle;
                    $model->entryId = $entry->id;
                    $model->siteId = $entry->siteId;
                    $model->blockId = $block->id;
                    if ($model->blockId) {
                        $blockRecord->setAttributes($model->getAttributes(), false);
                        $blockRecord->save();
                    }
                    $i++;
                }
            }
        }
    }

    public function listBlockTypes($matrixHandle = null, $siteHandle = null) {
        $blockList = [];

        if ($matrixHandle) {

            $blockList = (new Query())
                ->select(['blockHandle', 'count(entryId) as entryCount', 'sum(enabled) as enabled'])
                ->from('{{%matrixinventory_matrixblocks}}')
                ->where(['matrixHandle' => $matrixHandle])
                ->groupBy('blockHandle')
                ->all();
    
        }
        return $blockList;

    }

    public function listMatrixFields() {
        $records = (new MatrixListRecord())->find()->all();

        $models = [];

        foreach ($records as $record) {
            $model = new MatrixListModel();
            $model->setAttributes($record->getAttributes(), false);
            $models[] = $model;
        }

        return $models;

    }

    public function storeMatrixFields() {
        $fields = Craft::$app->fields->getAllFields(false);
        $handles = [];
        foreach ($fields as $field) {
            $matrixRecords = (new MatrixListRecord())->find()
                ->where([
                    'matrixName' => $field->name,
                    'matrixHandle' => $field->handle
                ])->all();
            foreach ($matrixRecords as $record) {
                $record->delete();
            }
        }
        foreach ($fields as $field) {
            if ($field::class == 'craft\\fields\\Matrix') {
                if (!in_array($field->handle, $handles)) {
                    array_push($handles, $field->handle);
                    $model = new MatrixListModel();
                    $model->matrixName = $field->name;
                    $model->matrixHandle = $field->handle;
                    $record = new MatrixListRecord();
                    $record->setAttributes($model->getAttributes(), false);
                    $record->save();
                }
                
            }
        }
        return true;
    }

    public function listEntries($handle = null, $matrixBlock = null) {
        $models = [];
        if ($matrixBlock && $handle) {
            $blockRecords = (new BlockListRecord())->find()
                            ->where(['blockHandle' => $matrixBlock, 
                                    'matrixHandle' => $handle])->all();
            foreach ($blockRecords as $record) {
                $model = new BlockListModel();
                $model->setAttributes($record->getAttributes(), false);
                $models[] = $model;
            }
        }
        return $models;


    }

}
