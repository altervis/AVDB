<?php

namespace AlterVision\AVDB;

use PDO, PDOException;

abstract class Connect
{
    protected static $config;
    protected $dbh;

    protected function __construct()
    {
        try
        {
            $this->dbh = new PDO(
                self::$config['type'] .
                ':charset=' . self::$config['charset'] .
                ';host=' . self::$config['host'] .
                ';dbname=' . self::$config['name'],
                self::$config['user'],
                self::$config['pass'],
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        } catch (PDOException $e)
        {
            die('<div style="text-align: center;">' . $e->getMessage() . '</div>');
        }
    }

    public static function factory($config)
    {
        self::$config = $config;
        $class_name   = 'AlterVision\\AVDB\\' . ucfirst(strtolower(self::$config['type']));

        return new $class_name();
    }

    protected function execute($query, $fields = array())
    {
        if (isset(self::$config['debug']) && self::$config['debug'])
        {
            print "$query<br/>\n";
            print_r($fields);
            print "<br/>\n";
        }

        $sth = $this->dbh->prepare($query);

        foreach ($fields as $name => $value)
        {
            $sth->bindValue(":" . $name, $value);
        }

        $sth->execute();

        return $sth;
    }

    public function query($query, $fields = array())
    {
        $this->execute($query, $fields);
    }

    public function select($query, $fields = array(), $fetch_style = PDO::FETCH_ASSOC)
    {
        return $this->execute($query, $fields)->fetch($fetch_style);
    }

    public function select_all($query, $fields = array(), $fetch_style = PDO::FETCH_ASSOC)
    {
        return $this->execute($query, $fields)->fetchAll($fetch_style);
    }

    public function select_cell($query, $fields = array())
    {
        return $this->execute($query, $fields)->fetchColumn(0);
    }

    public function insert($table, $fields = array(), $ignore = false)
    {
        $columns = array();
        $values  = array();
        foreach ($fields as $name => $value)
        {
            $columns[] = "{$name}";
            $values[]  = ":$name";
        }
        $columns = join(", ", $columns);
        $values  = join(", ", $values);

        $query = "INSERT" . ($ignore ? " IGNORE " : " ") . "INTO {$table} ( {$columns} ) VALUES ( {$values} )";

        return $this->execute($query, $fields)->rowCount();
    }

    public function update($table, $fields = array(), $where = array())
    {
        $pairs = array();
        foreach ($fields as $name => $value)
        {
            $pairs[] = "{$name} = :{$name}";
        }
        $pairs = join(", ", $pairs);

        $conds = array();
        foreach ($where as $name => $value)
        {
            $conds[]                  = "{$name} = :conds_{$name}";
            $fields["conds_" . $name] = $value;
        }
        $conds = join(" AND ", $conds);

        $query = /** @lang text */
            "UPDATE {$table} SET {$pairs}" . ($conds ? " WHERE " : " ") . $conds;

        return $this->execute($query, $fields)->rowCount();
    }

    public function delete($table, $where = array())
    {
        $conds  = array();
        $fields = array();
        foreach ($where as $name => $value)
        {
            $conds[]                  = "{$name} = :conds_{$name}";
            $fields["conds_" . $name] = $value;
        }
        $conds = join(" AND ", $conds);

        $query = /** @lang text */
            "DELETE FROM {$table}" . ($conds ? " WHERE " : " ") . $conds;

        return $this->execute($query, $fields)->rowCount();
    }

    public function last_insert_id()
    {
        return $this->dbh->lastInsertId();
    }
}