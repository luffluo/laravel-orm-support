<?php

namespace Luffluo\LaravelOrmSupport\Traits;

use Illuminate\Support\Str;
use Luffluo\LaravelOrmSupport\Exceptions\InvalidArgumentException;

/**
 * 按月分表
 *
 * Trait MonthlyScale
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
        return rtrim(Str::finish($this->handleRawTable(),  today()->format('Ym') . '_'), '_');
    }

    /**
     * 获取 table 属性设置的表名
     *
     * @return mixed
     */
    public function getShortTable()
    {
        return $this->table;
    }

    /**
     * 获取昨天的表名
     *
     * @return string
     */
    public function getTableForYesterday()
    {
        return $this->handleRawTable() . today()->subDay()->format('Ym');
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
        return $this->handleRawTable() . today()->subMonthsNoOverflow($months)->format('Ym');
    }

    /**
     * 通过特定的年月获取表名
     *
     * @param \DateTime|string $yearMonth
     *
     * @return string
     */
    public function getTableForYearMonth($yearMonth)
    {
        $yearMonth = to_carbon($yearMonth)->format('Ym');

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
     * @param int $weeks
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
     * @param int $weeks
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function newQueryForLastWeeks(int $weeks)
    {
        return $this->newQueryForPeriod(today()->subWeeks($weeks)->startOfWeek(),
            today()->subWeeks($weeks)->endOfWeek());
    }

    /**
     * 获取某个月的 query
     *
     * @param \DateTime|string $yearMonth
     *
     * @return @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function newQueryForYearMonth($yearMonth)
    {
        return $this->newQuery()->from($this->getTableForYearMonth($yearMonth));
    }

    /**
     * 通过某个时间段，获取 query
     *
     * @param \DateTime|string|int $start
     * @param \DateTime|string|int|null $end
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function newQueryForPeriod($start, $end = null)
    {
        $start = to_carbon($start)->startOfDay();

        if ($end) {
            $end = to_carbon($end);
        } else {
            $end = today();
        }

        $start = $start->copy();
        $end   = $end->copy();

        if ($end < $start) {
            throw new InvalidArgumentException('$start(' . $start . ') can\'t less then' . ' $end(' . $end . ')');
        }

        $query = $this->newQuery()->from($this->getTableForYearMonth($start->copy()->toDateTimeString()));

        if ($start->isSameMonth($end, true)) {
            return $query;
        }

        while (!$end->isSameMonth($start->addMonthNoOverflow(), true)) {
            $query->unionAll($this->newQuery()->from($this->getTableForYearMonth($start->copy()->toDateTimeString())));
        }

        $query->unionAll($this->newQuery()->from($this->getTableForYearMonth($end->copy()->toDateTimeString())));

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
     * @param int $weeks
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
     * @param int $weeks
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
     * @param \DateTime|string|int $start
     * @param \DateTime|string|int|null $end
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public static function queryForPeriod($start, $end = null)
    {
        return (new static)->newQueryForPeriod($start, $end);
    }

    /**
     * 获取某个月的 query
     *
     * @param \DateTime|string $yearMonth
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public static function queryForYearMonth($yearMonth)
    {
        return (new static)->newQueryForYearMonth($yearMonth);
    }
}
