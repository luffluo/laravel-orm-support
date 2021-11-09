<?php

namespace Luffluo\LaravelOrmSupport;

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
    }

    /**
     * 给 \Illuminate\Database\Query\Builder 注册新方法
     */
    public function registerQueryBuilderMacros()
    {
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

                if ($this instanceof EloquentBuilder) {

                    if ($this->getQuery()->unions) {
                        foreach ($this->getQuery()->unions as $union) {
                            $union['query']->where($column, $operator, $value, $boolean);
                        }
                    }
                } else {
                    if ($this->unions) {
                        foreach ($this->unions as $union) {
                            $union['query']->where($column, $operator, $value, $boolean);
                        }
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

            if ($this instanceof EloquentBuilder) {
                if ($this->getQuery()->unions) {
                    foreach ($this->getQuery()->unions as $union) {
                        $union['query']->orWhere($column, $operator, $value);
                    }
                }
            } else {

                if ($this->unions) {
                    foreach ($this->unions as $union) {
                        $union['query']->orWhere($column, $operator, $value);
                    }
                }
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
