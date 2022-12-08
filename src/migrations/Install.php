<?php
/**
 * Matrix Inventory plugin for Craft CMS 3.x
 *
 * Inventories the use of matrix fields
 *
 * @link      https://www.imarc.com
 * @copyright Copyright (c) 2021 Linnea Hartsuyker
 */

namespace imarc\matrixinventory\migrations;

use imarc\matrixinventory\MatrixInventory;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * Matrix Inventory Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    Linnea Hartsuyker
 * @package   MatrixInventory
 * @since     0.0.1
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

    // matrixinventory_matrixlist table
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%matrixinventory_matrixlist}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%matrixinventory_matrixlist}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'siteId' => $this->integer()->notNull(),
                    'matrixName' => $this->string(255)->notNull()->defaultValue(''),
                    'matrixHandle' => $this->string(255)->notNull()->defaultValue('')
                ]
            );
        }

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%matrixinventory_matrixblocks}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%matrixinventory_matrixblocks}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'siteId' => $this->integer()->notNull(),
                    'matrixHandle' => $this->string(255)->notNull()->defaultValue(''),
                    'blockHandle' => $this->string(255)->notNull()->defaultValue(''),
                    'enabled' => $this->boolean()->notNull(),
                    'entryId' => $this->integer()->notNull(),
                    'blockId' => $this->integer()->notNull(),
                ]
                );
        }

        return $tablesCreated;
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
    {
    // matrixinventory_matrixlist table
        $this->createIndex(
            $this->db->getIndexName(),
            '{{%matrixinventory_matrixlist}}',
            'matrixHandle',
            true
        );
        
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {
    // matrixinventory_matrixlist table
        $this->addForeignKey(
            $this->db->getForeignKeyName(),
            '{{%matrixinventory_matrixlist}}',
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        /*$this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixinventory_matrixblocks}}', 'siteId'),
            '{{%matrixinventory_matrixblocks}}',
            'matrixHandle',
            '{{%matrixinventory_matrixlist}}',
            'matrixHandle',
            'CASCADE',
            'CASCADE'
        ); */
    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
    // matrixinventory_matrixlist table
        $this->dropTableIfExists('{{%matrixinventory_matrixlist}}');
        $this->dropTableIfExists('{{%matrixinventory_matrixblocks}}');
    }
}
