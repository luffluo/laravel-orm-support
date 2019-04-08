<?php

namespace Luffluo\LaravelOrmSupport\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Luffluo\LaravelOrmSupport\Exceptions\InvalidArgumentException;

/**
 * 按月分表
 *
 * @property \App\Models\Model $model
 *
 * Trait MonthlyScale
 * @package App\Models\Traits
 */
trait MonthlyScale
{
    /**
     * 获取表名
     *
     * @return string
     */
    public function getTable()
    {
        return $this->handleRawTable() . today()->format('Ym');
    }

    /**
     * 获取昨天的表名
     *
     * @return string
     */
    public function getTableForYesterday()
    {
        return $this->handleRawTable() . today()->subDay();
    }

    /**
     * 获取上个月的表名
     *
     * @return string
     */
    public function getTableForLastMonth(int $months = 1)
    {
        return $this->getTableForLastMonths($months);
    }

    /**
     * 获取上几个月的表名
     *
     * @param int $months
     *
     * @return string
     */
    public function getTableForLastMonths(int $months)
    {
        return $this->handleRawTable() . today()->subMonths($months)->format('Ym');
    }

    /**
     * 通过特定的年月获取表名
     *
     * @param string $yearMonth
     *
     * @return string
     */
    public function getTableForYearMonth($yearMonth)
    {
        return $this->handleRawTable() . $yearMonth;
    }

    /**
     * 处理表名
     *
     * @return string
     */
    public function handleRawTable()
    {
        return Str::finish(parent::getTable(), '_');
    }

    /**
     * 获取昨天的查询 query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function newQueryForYesterday()
    {
        return $this->newQueryForPeriod(today()->subDay(), today()->subDay());
    }

    /**
     * 获取 上周 查询的 query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function newQueryForLastWeek(int $weeks = 1)
    {
        return $this->newQueryForLastWeeks($weeks);
    }

    /**
     * 获取 上几周 查询的 query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function newQueryForLastWeeks(int $weeks)
    {
        return $this->newQueryForPeriod(today()->subWeeks($weeks)->startOfWeek(), today()->subWeeks($weeks)->endOfWeek());
    }

    /**
     * 通过某个时间段，获取 query
     *
     * @param Carbon|string|int      $start
     * @param Carbon|string|int|null $end
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function newQueryForPeriod($start, $end = null)
    {
        $start = to_carbon($start);

        if ($end) {
            $end = to_carbon($end);
        } else {
            $end = today();
        }

        if ($end < $start) {
            throw new InvalidArgumentException('$start can\'t less then $end');
        }

        $query = $this->newQuery()->from($this->getTableForYearMonth($start->copy()->format('Ym')));

        if ($start->isSameMonth($end, true)) {
            return $query;
        }

        while (! $end->isSameMonth($start->addMonth(), true)) {
            $query->unionAll($this->newQuery()->from($this->getTableForYearMonth($start->copy()->format('Ym'))));
        }

        $query->unionAll($this->newQuery()->from($this->getTableForYearMonth($end->copy()->format('Ym'))));

        return $query;
    }

    /**
     * 获取昨天的查询 query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public static function queryForYesterday()
    {
        return (new static)->newQueryForYesterday();
    }

    /**
     * 获取 上周 查询的 query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public static function queryForLastWeek(int $weeks = 1)
    {
        return (new static)->newQueryForLastWeek($weeks);
    }

    /**
     * 获取 上几周 查询的 query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public static function queryForLastWeeks(int $weeks)
    {
        return (new static)->newQueryForLastWeeks($weeks);
    }

    /**
     * 通过某个时间段，获取 query
     *
     * @param Carbon|string|int      $start
     * @param Carbon|string|int|null $end
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public static function queryForPeriod($start, $end = null)
    {
        return (new static)->newQueryForPeriod($start, $end);
    }
}
