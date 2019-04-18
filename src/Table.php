<?php

declare(strict_types = 1);

namespace Luffluo\LaravelOrmSupport;

use Illuminate\Support\Facades\DB;

/**
 * 数据库表操作类
 *
 * Class Table
 *
 * @package Luffluo\LaravelOrmSupport
 */
class Table
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
    protected $prefix;

    public function __construct($connectionName = null)
    {
        $this->setConnection($connectionName);
    }

    /**
     * @param string|\Illuminate\Database\ConnectionInterface $connection
     */
    public function setConnection($connection)
    {
        if (! $connection instanceof \Illuminate\Database\ConnectionInterface) {
            $connection = DB::connection($connection);
        }

        $this->connection = $connection;

        $this->prefix = $this->connection->getTablePrefix();
    }

    /**
     * @return \Illuminate\Database\Connection|\Illuminate\Database\ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * 获取数据库表前缀
     *
     * @return string
     */
    public function getPrefix()
    {
        if (empty($this->prefix)) {
            $this->prefix = $this->connection->getTablePrefix();
        }

        return $this->prefix;
    }

    /**
     * 创建数据库表
     *
     * @param string $table
     * @param string|null $fromTable
     */
    public function createUseLike(string $table, string $fromTable = null)
    {
        if (empty($fromTable)) {
            $fromTable = $table;
        }

        $sql = "CREATE TABLE {$this->getPrefix()}{$table} LIKE {$this->getPrefix()}{$fromTable}";

        $this->connection->statement($sql);
    }

    /**
     * 通过 LIKE 创建数据库表，根据相同表名
     *
     * @param string $table
     * @param string $tableSuffix
     * @param string $fromTableSuffix
     */
    public function createUseLikeForMonthlyTable(string $table, string $tableSuffix, string $fromTableSuffix)
    {
        $this->createUseLike($table . $tableSuffix, $table . $fromTableSuffix);
    }
}
