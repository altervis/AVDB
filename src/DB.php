<?php

namespace AlterVision\AVDB;

use PDO;

abstract class DB
{
    private static $db_connect = null;

    private static function get_connect()
    {
        if (self::$db_connect == null) {
            self::$db_connect = Connect::factory();
        }
        return self::$db_connect;
    }

    public static function query($query, $fields = array())
    {
        return self::get_connect()->query($query, $fields);
    }

    public static function select($query, $fields = array(), $fetch_style = PDO::FETCH_ASSOC)
    {
        return self::get_connect()->select($query, $fields, $fetch_style);
    }

    public static function select_cell($query, $fields = array())
    {
        return self::get_connect()->select_cell($query, $fields);
    }

    public static function select_all($query, $fields = array(), $fetch_style = PDO::FETCH_ASSOC)
    {
        return self::get_connect()->select_all($query, $fields, $fetch_style);
    }

    public static function insert($table, $fields = array(), $ignore = false)
    {
        return self::get_connect()->insert($table, $fields, $ignore);
    }

    public static function update($table, $fields = array(), $where = array())
    {
        return self::get_connect()->update($table, $fields, $where);
    }

    public static function delete($table, $where = array())
    {
        return self::get_connect()->delete($table, $where);
    }

    public static function last_insert_id()
    {
        return self::get_connect()->last_insert_id();
    }
}