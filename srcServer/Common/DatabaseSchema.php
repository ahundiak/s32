<?php
namespace Cerad\Common;

use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\VarDumper\VarDumper;

class DatabaseSchema
{
    static function createFromArray(array $schemaData)
    {
        $schema = new Schema();

        // Predefined columns
        $columnsData = $schemaData['schema']['columns'];

        $tablesData = $schemaData['schema']['tables'];

        foreach($tablesData as $tableName => $tableData) {

            $table = $schema->createTable($tableName);

            foreach($tableData['columns'] as $columnName => $columnData) {
                $columnData = isset($columnsData[$columnName]) ? array_replace($columnsData[$columnName],$columnData) : $columnData;
                $columnType = $columnData['type'];
                unset($columnData['type']);
                $table->addColumn($columnName,$columnType,$columnData);
            }
            $table->setPrimaryKey(($tableData['primaryKey']));

            if (isset($tableData['indexes'])) {
                foreach($tableData['indexes'] as $indexName => $indexColumns) {
                    $table->addIndex($indexColumns,$indexName);
                }
            }
            if (isset($tableData['foreignKeys'])) {

                foreach($tableData['foreignKeys'] as $foreignKeyName => $foreignKeyData) {

                    $foreignTable = $schema->getTable($foreignKeyData['foreignTable']);

                    $table->addForeignKeyConstraint(
                        $foreignTable,
                        $foreignKeyData['nativeColumns'],
                        $foreignKeyData['foreignColumns'],
                        $foreignKeyData['options'],
                        $foreignKeyName);
                }
            }
        }
        return $schema;
    }
}