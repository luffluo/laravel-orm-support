<?php

namespace Luffluo\LaravelOrmSupport;

use Illuminate\Support\Arr;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerQueryBuilderMacros();

        $this->registerEloquentBuilderMacros();
    }

    /**
     * 给 \Illuminate\Database\Eloquent\Builder 注册新方法
     */
    public function registerEloquentBuilderMacros()
    {
        /**
         * 让分页的时候不要用 count(*) 去统计总数
         * 用 countColumns 的字段去统计
         *
         * @param int $perPage 每页数量
         * @param string $countColumns 用于统计总数的列
         * @param array $columns 返回的列
         * @param string $pageName 页数字段名称
         * @param int|null $page 页数
         */
        EloquentBuilder::macro('countColumnPaginate', function ($perPage = null, $countColumns = 'id', $columns = ['*'], $pageName = 'page', $page = null) {
            $page = $page ?: \Illuminate\Pagination\Paginator::resolveCurrentPage($pageName);

            $perPage = $perPage ?: $this->model->getPerPage();

            $results = ($total = $this->toBase()->getCountForPagination(\Illuminate\Support\Arr::wrap($countColumns)))
                ? $this->forPage($page, $perPage)->get($columns)
                : $this->model->newCollection();

            return $this->paginator($results, $total, $perPage, $page, [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]);
        });
    }

    /**
     * 给 \Illuminate\Database\Query\Builder 注册新方法
     */
    public function registerQueryBuilderMacros()
    {
        /**
         * 让分页的时候不要用 count(*) 去统计总数
         * 用 countColumns 的字段去统计
         *
         * @param int $perPage 每页数量
         * @param string $countColumns 用于统计总数的列
         * @param array $columns 返回的列
         * @param string $pageName 页数字段名称
         * @param int|null $page 页数
         */
        QueryBuilder::macro('countColumnPaginate', function ($perPage = 15, $countColumns = 'id', $columns = ['*'], $pageName = 'page', $page = null) {
            $page = $page ?: \Illuminate\Pagination\Paginator::resolveCurrentPage($pageName);

            $total = $this->getCountForPagination(\Illuminate\Support\Arr::wrap($countColumns));

            $results = $total ? $this->forPage($page, $perPage)->get($columns) : collect();

            return $this->paginator($results, $total, $perPage, $page, [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]);
        });

        QueryBuilder::macro('unionSelect', function ($columns = ['*']) {

            /* @var \Illuminate\Database\Query\Builder $this */

            $columns = is_array($columns) ? $columns : func_get_args();

            $this->select($columns);

            if ($this->unions) {
                foreach ($this->unions as $union) {
                    $union['query']->select($columns);
                }
            }

            return $this;
        });

        QueryBuilder::macro('unionSelectRaw', function ($expression, array $bindings = []) {

            /* @var \Illuminate\Database\Query\Builder $this */

            $this->selectRaw($expression, $bindings);

            if ($this->unions) {
                foreach ($this->unions as $union) {
                    $union['query']->selectRaw($expression, $bindings);
                }
            }

            return $this;
        });

        /**
         * 给所有的 union 添加 where
         */
        EloquentBuilder::macro('unionWhere',
            function ($column, $operator = null, $value = null, $boolean = 'and') {

                /* @var \Illuminate\Database\Eloquent\Builder $this */

                $this->where($column, $operator, $value, $boolean);

                if ($this instanceof \Illuminate\Database\Eloquent\Builder) {

                    if ($this->getQuery()->unions) {
                        foreach ($this->getQuery()->unions as $union) {
                            $union['query']->where($column, $operator, $value, $boolean);
                        }

                        $this->addBinding(Arr::last($this->getQuery()->wheres)['value'] ?? null, 'union');
                    }
                } else {
                    if ($this->unions) {
                        foreach ($this->unions as $union) {
                            $union['query']->where($column, $operator, $value, $boolean);
                        }

                        $this->addBinding(Arr::last($this->wheres)['value'] ?? null, 'union');
                    }
                }

                return $this;
            });

        /**
         * 给所有的 union 添加 where
         */
        EloquentBuilder::macro('unionOrWhere', function ($column, $operator = null, $value = null) {

            /* @var \Illuminate\Database\Eloquent\Builder $this */

            $this->orWhere($column, $operator, $value);

            if ($this->unions) {
                foreach ($this->unions as $union) {
                    $union['query']->orWhere($column, $operator, $value);
                }

                $this->addBinding(Arr::last($this->wheres)['value'] ?? null, 'union');
            }

            return $this;
        });

        /**
         * 给所有的 union 添加 where
         */
        QueryBuilder::macro('unionWhereIn',
            function ($column, $values, $boolean = 'and', $not = false) {

                /* @var \Illuminate\Database\Query\Builder $this */

                $this->whereIn($column, $values, $boolean, $not);

                if ($this->unions) {
                    foreach ($this->unions as $union) {
                        $union['query']->whereIn($column, $values, $boolean, $not);
                    }

                    foreach ($values as $value) {
                        $this->addBinding($value, 'union');
                    }
                }

                return $this;
            });

        /**
         * 给所有的 union 添加 where
         */
        QueryBuilder::macro('unionWhereBetween',
            function ($column, array $values, $boolean = 'and', $not = false) {

                /* @var \Illuminate\Database\Query\Builder $this */

                $this->whereBetween($column, $values, $boolean, $not);

                if ($this->unions) {
                    foreach ($this->unions as $union) {
                        $union['query']->whereBetween($column, $values, $boolean, $not);
                    }

                    $this->addBinding($values, 'union');
                }

                return $this;
            });

        /**
         * 给所有的 unions 添加 group by
         */
        QueryBuilder::macro('unionGroupBy', function (...$groups) {

            /* @var \Illuminate\Database\Eloquent\Builder $this */

            $this->groupBy($groups);

            if ($this->unions) {
                foreach ($this->unions as $union) {
                    $union['query']->groupBy($groups);
                }
            }

            return $this;
        });
    }
}
