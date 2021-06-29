<?php

namespace PromoCode\LaravelSchemaHelper;

use Illuminate\Database\Connection;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\SQLiteConnection;

class LaravelSchemaHelper
{
    /**
     * @param string $table
     * @param string $index
     * @param string|null $schema
     * @return bool
     */
    public static function hasIndex(
        string $table,
        string $index,
        string $schema = null
    ): bool {
        /** @var \Illuminate\Database\Connection $db */
        $db = app('db')->connection();

        if (self::isSqlite($db)) {
            return true;
        }

        $data = $db->select('
                SELECT *
                FROM INFORMATION_SCHEMA.STATISTICS
                WHERE TABLE_SCHEMA = :schema
                ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX
            ',
            [':schema' => $schema]
        );

        if (empty($data)) {
            return false;
        }
    }

    public static function enumValues(
        string $schema,
        string $table,
        string $column,
        Connection $db = null
    ): array
    {
        if (null === $db) {
            /** @var \Illuminate\Database\Connection $db */
            $db = app('db')->connection();
        }

        if (self::isSqlite($db)) {
            return [];
        }

        $data = $db->select('
                SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = :schema 
                AND TABLE_NAME = :table
                AND COLUMN_NAME = :column
            ',
            [':schema' => $schema, ':table' => $table, ':column' => $column]
        );

        $values = str_replace(
            ["enum('", "')", "','"],
            [''      , ''  , ','  ],
            $data[0]->COLUMN_TYPE
        );

        return explode(',', $values);
    }

    public static function isMysql(Connection $db = null): bool
    {
        if (null === $db) {
            /** @var \Illuminate\Database\Connection $db */
            $db = app('db')->connection();
        }

        return MySqlConnection::class === \get_class($db);
    }

    public static function isSqlite(Connection $db = null): bool
    {
        if (null === $db) {
            /** @var \Illuminate\Database\Connection $db */
            $db = app('db')->connection();
        }

        return SQLiteConnection::class === \get_class($db);
    }
}
