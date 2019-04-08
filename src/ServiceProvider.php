<?php

namespace Luffluo\LaravelOrmSupport;

use Illuminate\Support\Arr;
use Illuminate\Database\Query\Builder;
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
    }

    /**
     * 给 \Illuminate\Database\Query\Builder 注册新方法
     */
    public function registerQueryBuilderMacros()
    {
        Builder::macro('unionSelect', function ($columns = ['*']) {

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

        Builder::macro('unionSelectRaw', function ($expression, array $bindings = []) {

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
        Builder::macro('unionWhere',
            function ($column, $operator = null, $value = null, $boolean = 'and') {

                /* @var \Illuminate\Database\Query\Builder $this */

                $this->where($column, $operator, $value, $boolean);

                if ($this->unions) {
                    foreach ($this->unions as $union) {
                        $union['query']->where($column, $operator, $value, $boolean);
                    }

                    $this->addBinding(Arr::last($this->wheres)['value'] ?? null, 'union');
                }

                return $this;
            });

        /**
         * 给所有的 union 添加 where
         */
        Builder::macro('unionOrWhere', function ($column, $operator = null, $value = null) {

            /* @var \Illuminate\Database\Query\Builder $this */

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
        Builder::macro('unionWhereIn',
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
        Builder::macro('unionWhereBetween',
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
        Builder::macro('unionGroupBy', function (...$groups) {

            /* @var \Illuminate\Database\Query\Builder $this */

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
