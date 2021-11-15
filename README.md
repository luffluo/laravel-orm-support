<h1 align="center"> Laravel ORM Support </h1>

<p align="center"> 扩展 Laravel ORM</p>

添加了按月分表的支持

## Installing

```shell
$ composer require luffluo/laravel-orm-support:~1.0 -vvv
```

## Usage

### Notice
复制 `Illuminate\Database\Query\Builder` 类到根目录下 `replaces\Database\Query\Builder.php`
然后用下面的 `getBindings` 方法覆盖原来的

```php
<?php

declare(strict_types = 1);

namespace Illuminate\Database\Query;

class Builder
{
    /**
    * Get the current query value bindings in a flattened array.
    *
    * @return array
    */
    public function getBindings()
    {
        $bindings = Arr::flatten($this->bindings);
        
        if ($this->unions && count($this->bindings['union']) <= 0) {
            foreach ($this->unions as $union) {
                $bindings = array_merge($bindings, $union['query']->getBindings());
            }
        }
        
        return $bindings;
    }
}
```

并在 `composer.json` 里添加如下配置, 然后执行 `composer dump`

```json
{
    "autoload": {
        "files": [
            "replaces/Database/Query/Builder.php",
        ]
    }
}
```

> 表名要使用如下格式
> xxxx_202111, xxxx_202112 等

### 按月分表的使用
```php
use \Luffluo\LaravelOrmSupport\Traits\MonthlyScale;

class Model
{
    use MonthlyScale;
}
```

添加上面的 `Trait` 到 `model` 后，就像原来使用 `model` 一样，会查询当月的数据
#### 查询当前月
```php
Model::query()->count();
```

#### 查询上周的数据
```php
// 上周
Model::queryForLastWeek()->count();

// 上上周
Model::queryForLastWeeks(2)->count();
```

#### 查询某个时间段的数据, 时间支持 `Carbon`
```php
Model::queryForPeriod('2019-01', '2019-11')->count();
```

#### 查询某年某月的数据，时间支持 `Carbon`
```php
Model::queryForYearMonth('201901')->count();
```

#### select
```php
Model::queryForPeriod('2019-11-26', '2020-11-26')
    ->unionSelect('xxx', 'xxx')
    ->unionSelectRaw('xxx', 'xxx')
    ->count();
```

#### where
```php
Model::queryForPeriod('2019-11-26', '2020-11-26')
    ->unionWhere('xxx', 'xxx')
    ->unionOrWhere('xxx', 'xxx')
    ->unionWhereIn('xxx', ['xx', 'xx'])
    ->unionWhereBetween('xx', ['xxx', 'xxx'])
    ->count();
```

#### groupBy
```php
Model::queryForPeriod('2019-11-26', '2020-11-26')
    ->unionGroupBy('xxx', 'xxx')
    ->count();
```

## License

MIT