<h1 align="center"> laravel-orm-support </h1>

<p align="center"> 扩展 Laravel ORM</p>

添加了按月分表的支持


## Installing

```shell
$ composer require luffluo/laravel-orm-support -vvv
```

## Usage

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