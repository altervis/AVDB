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
                array(
                    PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => true,
                )
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

    protected function debug(&$sth)
    {
        if (isset(self::$config['debug']) && self::$config['debug'])
        {
            ob_start();
            $sth->debugDumpParams();
            $dump = ob_get_clean();

            $lines = explode("\n", $dump);
            $lines = array_slice($lines, 0, 2);

            $sql = preg_replace('/^Sent SQL: \[\d+\]/ ', '', $lines[1], -1, $count);

            if (!$count)
            {
                $sql = preg_replace('/^SQL: \[\d+\]/ ', '', $lines[0], -1, $count);
            }

            $sql = trim($sql);

            if ($count)
            {
                echo '<div style="border-bottom: 1px solid red; padding: 0 5px">' . "\n";
                echo \SqlFormatter::format($sql);
                echo '</div>' . "\n";
            }
        }
    }

    protected function bind_values($query, $fields = array())
    {
        foreach ($fields as $name => $value)
        {
            if (!preg_match('/^(int|float|sql)\:\/\/(.*)/', $value, $matches))
            {
                $value = $this->dbh->quote($value);
            }
            else
            {
                switch ($matches[1])
                {
                    case 'int':
                        $value = intval($matches[2]);
                        break;
                    case 'float':
                        $value = floatval($matches[2]);
                        break;
                    case 'sql':
                        $value = $matches[2];
                        break;
                }
            }

            $query = str_replace(":$name", $value, $query);
        }

        return $query;
    }

    protected function execute($query, $fields = array())
    {
        $query = self::bind_values($query, $fields);

        $sth = $this->dbh->prepare($query);

        $sth->execute();

        $this->debug($sth);

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