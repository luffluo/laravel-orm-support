<?php

declare(strict_types = 1);

namespace Luffluo\LaravelOrmSupport;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB as LaravelDB;

/**
 * 数据库表操作类
 * Class DB
 *
 * @package Luffluo\LaravelOrmSupport
 */
class DB
{
    /**
     * Database connection
     *
     * @var \Illuminate\Database\Connection
     */
    protected $connection;

    /**
     * 数据库表前缀
     *
     * @var string
     */
    protected $tablePrefix;

    /**
     * 是否删除已存在的表，视图或存储过程
     *
     * @var bool
     */
    protected $drop = false;

    public function __construct($connectionName = null)
    {
        $this->setConnection($connectionName);
    }

    /**
     * 过滤 sql 语句，让其成为一个可执行的语句
     *
     * @param string $sql
     * @return string
     */
    public static function filterSql(string $sql): string
    {
        $sql = str_replace(PHP_EOL, '', $sql);

        return (string) preg_replace('/[\s]+/', ' ', $sql);
    }

    /**
     * @param string|\Illuminate\Database\ConnectionInterface $connection
     */
    public function setConnection($connection): self
    {
        if (! $connection instanceof \Illuminate\Database\ConnectionInterface) {
            $connection = LaravelDB::connection($connection);
        }

        $this->connection = $connection;

        $this->tablePrefix = $this->connection->getTablePrefix();

        return $this;
    }

    /**
     * @return \Illuminate\Database\Connection|\Illuminate\Database\ConnectionInterface
     */
    public function getConnection(): \Illuminate\Database\ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * 获取数据库表前缀
     *
     * @return string
     */
    public function getTablePrefix(): string
    {
        if (empty($this->tablePrefix)) {
            $this->tablePrefix = $this->connection->getTablePrefix();
        }

        return $this->tablePrefix;
    }

    /**
     * 设置 drop
     *
     * @param bool $value
     * @return $this
     */
    public function setDrop(bool $value): self
    {
        $this->drop = $value;

        return $this;
    }

    /**
     * 创建数据库表
     *
     * @param string $table
     * @param string|null $fromTable
     */
    public function createTableUseLike(string $table, string $fromTable = null)
    {
        if (empty($fromTable)) {
            $fromTable = $table;
        }

        if ($this->drop) {
            $this->connection->statement("DROP TABLE IF EXISTS {$this->getTablePrefix()}{$table}");
        }

        $sql = "CREATE TABLE IF NOT EXISTS {$this->getTablePrefix()}{$table} LIKE {$this->getTablePrefix()}{$fromTable}";

        $this->connection->statement($sql);
    }

    /**
     * 通过 LIKE 创建数据库表，根据相同表名
     *
     * @param string $table
     * @param string $tableSuffix
     * @param string $fromTableSuffix
     */
    public function createTableUseLikeForMonthlyTable(string $table, string $tableSuffix, string $fromTableSuffix)
    {
        $this->createTableUseLike($table . $tableSuffix, $table . $fromTableSuffix);
    }

    /**
     * 获取数据库里的表名
     *
     * @param string|null $like 待过滤的表名
     * @return array
     */
    public function getTables(string $like = null): array
    {
        $tables = $this->getConnection()
            ->select(LaravelDB::raw('show tables'));

        $tables = collect(object_to_array($tables))->flatten();

        if ($like) {
            $tables = $tables->filter(function ($item) use ($like) {
                return Str::contains($item, $like);
            });
        }

        return $tables
            ->values()
            ->toArray();
    }
}
