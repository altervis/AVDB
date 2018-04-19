<?php

namespace AlterVision\AVDB;

abstract class Connect
{
	protected $dbh = NULL;

	protected function __construct()
	{
		try
		{
			$this->dbh = new \PDO(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
		}
		catch (\PDOException $e)
		{
			die("<center>" . $e->getMessage() . "<center>");
		}
	}

	public static function factory()
	{
		$class_name = 'AlterVision\\AVDB\\' . ucfirst(strtolower(DB_TYPE));

		if (!class_exists($class_name))
		{
			trigger_error('Ошибка. Не найден класс "' . $class_name . '".', E_USER_ERROR);
			exit;
		}

		return new $class_name();
	}

	protected function execute($query, $fields = array())
	{
		if (isset($GLOBALS['DB_DEBUG']) && $GLOBALS['DB_DEBUG'])
		{
			print "$query<br/>\n";
			print_r($fields);
			print "<br/>\n";
		}

		$sth = $this->dbh->prepare($query);

		foreach ($fields as $name => $value)
			$sth->bindValue(":" . $name, $value);

		$sth->execute();

		return $sth;
	}

	public function query($query, $fields = array())
	{
		$this->execute($query, $fields);
	}

	public function select($query, $fields = array(), $fetch_style = \PDO::FETCH_ASSOC)
	{
		return $this->execute($query, $fields)->fetch($fetch_style);
	}

	public function select_all($query, $fields = array(), $fetch_style = \PDO::FETCH_ASSOC)
	{
		return $this->execute($query, $fields)->fetchAll($fetch_style);
	}

	public function select_cell($query, $fields = array())
	{
		return $this->execute($query, $fields)->fetchColumn(0);
	}

	public function insert($table, $fields = array())
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

		$query = "INSERT INTO {$table} ( {$columns} ) VALUES ( {$values} )";

		return $this->execute($query, $fields)->rowCount();
	}

	public function update($table, $fields = array(), $where = array())
	{
		$pairs = array();
		foreach ($fields as $name => $value)
			$pairs[] = "{$name} = :{$name}";
		$pairs = join(", ", $pairs);

		$conds = array();
		foreach ($where as $name => $value)
		{
			$conds[]                  = "{$name} = :conds_{$name}";
			$fields["conds_" . $name] = $value;
		}
		$conds = join(" and ", $conds);

		$query = "UPDATE {$table} SET {$pairs}" . ($conds ? " where " : " ") . $conds;

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
		$conds = join(" and ", $conds);

		$query = "DELETE FROM {$table}" . ($conds ? " where " : " ") . $conds;

		return $this->execute($query, $fields)->rowCount();
	}

	public function last_insert_id()
	{
		return $this->dbh->lastInsertId();
	}

	public function create()
	{
		return $this->dbh->create();
	}
}
