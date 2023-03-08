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
use imarc\matrixinventory\jobs\BlockElementList as BlockElementJob;

use Craft;
use Craft\db\Query;
use craft\base\Component;
use craft\elements\Entry;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Tag;
use craft\elements\GlobalSet;
use craft\elements\User;
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
    public function exampleService(): string|array
    {
        $result = 'something';
        // Check our Plugin's settings for `someAttribute`
        if (MatrixInventory::$plugin->getSettings()->someAttribute) {
        }

        return $result;
    }

    public function listAllMatrixes(): string
    {
        $returnString = "";
        $sections = Craft::$app->sections->allSections;

        $inventory = [];
        
        foreach ($sections as $section) {
            $entries = Entry::find()->section($section->handle)->all();
            foreach ($entries as $entry) {
                $entryFields = $entry->getFieldLayout()->getCustomFields();
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
        $this->storeMatrixFields();
        foreach ($sections as $section) {
            echo $section . "\n";
            //$job = new BlockListJob();
            //$job->setSection($section);
            //\craft\helpers\Queue::push($job);
            $this->storeSectionMatrixes($section);
            //break;
        }
        $otherElements = ['category', 'asset', 'tag', 'globalset', 'user'];
        foreach ($otherElements as $elementType) {
            echo $elementType . "\n";
            $this->storeElementMatrixes($elementType);
            /*$job = new BlockElementJob();
            $job->setElementType($elementType);
            Craft::$app->queue->push($job);*/
        }        
    }

    public function storeElementMatrixes($elementType)
    {
        $elements = [];
        if ($elementType == 'category') {
            $elements = Category::find()->all();
        } elseif ($elementType == 'asset') {
            $elements = Asset::find()->all();
        } elseif ($elementType == 'tag') {
            $elements = Tag::find()->all();
        } elseif ($elementType == 'globalset') {
            $elements = GlobalSet::find()->all();
        } elseif ($elementType == 'user') {
            $elements = User::find()->all();
        }
        foreach ($elements as $element) {
            $blockRecords = (new BlockListRecord())->find()
                ->where([
                    'elementId' => $element->id,
                    'siteId' => $element->siteId
                ])->all();
            foreach ($blockRecords as $record) {
                $record->delete();
            }
            echo $element->title . "\n";
            $entryFields = $element->getFieldLayout()->getCustomFields();
            foreach ($entryFields as $fieldLayout) {
                $field = Craft::$app->fields->getFieldById($fieldLayout->id);
                if (get_class($field) == 'craft\\fields\\Matrix') {
                    $matrixBlocks = $element->getFieldValue($field->handle);
                    foreach ($matrixBlocks as $block) {
                        $model = new BlockListModel();

                        $blockRecord = new BlockListRecord();
                        $model->enabled = $elementType == 'tag' ? true : $element->enabled;
                        $now = new DateTime();
                        $model->dateCreated = $now->format('Y-m-d H:i:s');
                        $model->dateUpdated = $now->format('Y-m-d H:i:s');

                        $model->matrixHandle = $field->handle;
                        $model->blockHandle = $block->type->handle;
                        $model->blockId = $block->id;
                        $model->elementId = $element->id;
                        $model->elementType = $elementType;
                        $model->siteId = $element->siteId;
                        $blockRecord->setAttributes($model->getAttributes(), false);
                        $blockRecord->save();
                    }
                }
            }
        }
    }

    public function removeAllMatrixes()
    {
        //write code here
    }
    

    public function storeSectionMatrixes($section) {
        echo "In storeSectionMatrixes\n";
        if ($section) {
            echo "If section true\n";
            $entries = Entry::find()->section($section->handle)->anyStatus()->all();
            echo "Num entries in section: " . count($entries) . "\n";
            foreach ($entries as $entry) {
                echo $entry->title . "\n";
                $blockRecords = (new BlockListRecord())->find()
                    ->where([
                        'elementId' => $entry->id,
                        'siteId' => $entry->siteId
                    ])->all();
                foreach ($blockRecords as $record) {
                    $record->delete();
                }
                $entryFields = $entry->getFieldLayout()->getCustomFields();
                foreach ($entryFields as $fieldLayout) {
                    $field = Craft::$app->fields->getFieldById($fieldLayout->id);
                    if ($field::class == 'craft\\fields\\Matrix') {
                        $matrixBlocks = $entry->getFieldValue($field->handle);
                        foreach ($matrixBlocks as $block) {
                            $model = new BlockListModel();

                            $blockRecord = new BlockListRecord();
                            $model->enabled = $entry->enabled;
                            $now = new DateTime();
                            $model->dateCreated = $now->format('Y-m-d H:i:s');
                            $model->dateUpdated = $now->format('Y-m-d H:i:s');

                            $model->matrixHandle = $field->handle;
                            $model->blockHandle = $block->type->handle;
                            $model->blockId = $block->id;
                            $model->elementId = $entry->id;
                            $model->elementType = "entry";
                            $model->siteId = $entry->siteId;
                            $blockRecord->setAttributes($model->getAttributes(), false);
                            $blockRecord->save();
                        }
                    }
                }
            }
        }
    }

    public function updateElementMatrixes($element) {
        $elementFields = $element->getFieldLayout()->getFields();
        foreach ($elementFields as $fieldLayout) {
            $field = Craft::$app->fields->getFieldById($fieldLayout->id);
            if (get_class($field) == 'craft\\fields\\Matrix') {
                $matrixBlocks = $element->getFieldValue($field->handle);
                $blockRecords = (new BlockListRecord())->find()
                                ->where([
                                    'elementId' => $element->id,
                                    'siteId' => $element->siteId
                                ])->all();
                foreach ($blockRecords as $record) {
                    $record->delete();
                }
                foreach ($matrixBlocks as $block) {
                    $blockRecord = new BlockListRecord();

                    $model = new BlockListModel();
                    if ($element->status == 'live') {
                        $model->enabled = true;
                    } else {
                        $model->enabled = false;
                    } 
                    $now = new DateTime();
                    $model->dateCreated = $now->format('Y-m-d H:i:s');
                    $model->dateUpdated = $now->format('Y-m-d H:i:s');

                    $model->matrixHandle = $field->handle;
                    $model->blockHandle = $block->type->handle;
                    $model->elementId = $element->id;
                    if ($element instanceof Entry) {
                        $model->elementType = "entry";
                    } elseif ($element instanceof Category) {
                        $model->elementType = "category";
                    } elseif ($element instanceof Asset) {
                        $model->elementType = "asset";
                    } elseif ($element instanceof Tag) {
                        $model->elementType = "tag";
                    } elseif ($element instanceof User) {
                        $model->elementType = "user";
                    } elseif ($element instanceof GlobalSet) {
                        $model->elementType = "globalset";
                    }

                    $model->siteId = $element->siteId;
                    $model->blockId = $block->id;
                    if ($model->blockId) {
                        $blockRecord->setAttributes($model->getAttributes(), false);
                        $blockRecord->save();
                    }
                }
            }
        }
    }

    public function listBlockTypes($matrixHandle = null, $siteHandle = null): array {
        $blockList = [];

        if ($matrixHandle) {

            $blockList = (new Query())
                ->select(['blockHandle', 'count(elementId) as entryCount', 'sum(enabled) as enabled'])
                ->from('{{%matrixinventory_matrixblocks}}')
                ->where(['matrixHandle' => $matrixHandle])
                ->groupBy('blockHandle')
                ->all();
    
        }
        return $blockList;

    }

    public function listMatrixFields(): array {
        $records = (new MatrixListRecord())->find()->all();

        $models = [];

        foreach ($records as $record) {
            $model = new MatrixListModel();
            $model->setAttributes($record->getAttributes(), false);
            $models[] = $model;
        }

        return $models;

    }

    public function storeMatrixFields(): bool {
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
            echo $field->name . "\n";
            if (get_class($field) == 'craft\\fields\\Matrix') {
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

    public function listEntries($handle = null, $matrixBlock = null): array {
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
