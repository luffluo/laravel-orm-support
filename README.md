<h1 align="center"> laravel-orm-support </h1>

<p align="center"> 扩展 Laravel ORM</p>

添加了按月分表的支持


## Installing

```shell
$ composer require luffluo/laravel-orm-support:~1.0 -vvv
```

## Usage

### Notice
复制 `Illuminate\Database\Query\Builder` 类到根目录下 `replaces\Database\Query\Builder.php`
然后把下面的 `getBindings` 方法覆盖原来的

```php
<?php

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

并在 `composer.json` 添加如下设置, 然后执行 `composer dump`

```json
"autoload": {
    "files": [
        "replaces/Database/Query/Builder.php",
    ]
}
```

#### 按月分表的使用
```php
use \Luffluo\LaravelOrmSupport\Traits\MonthlyScale;

class Model
{
    use MonthlyScale;
}
```

添加上面的 `Trait` 到 `model` 后，就像原来使用 `model` 一样，会查询当月的数据

查询上周的数据
```php
// 上周
Model::queryForLastWeek()->get();

// 上上周
Model::queryForLastWeeks(2)->get();
```

查询某个时间段的数据, 时间支持 `Carbon`
```php
Model::queryForPeriod('2019-01', '2019-11')->get();
```

查询某年某月的数据，时间支持 `Carbon`
```php
Model::queryForYearMonth('201901')->get();
```

## License

MIT