<?php

namespace Baloot;

use Baloot\Models\Province;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class BalootServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
 
        Validator::resolver(function ($translator, $data, $rules, $messages = [], $customAttributes = []) {
            return new BalootValidator($translator, $data, $rules, $messages, $customAttributes);
        });


        $this->registerQueryBuilderMacros();
    }



    /**
     * Register query builder macros.
     */
    public function registerQueryBuilderMacros()
    {
        Builder::macro('whereJalali', function (string $column, $operator, $value = null, $boolean = 'and') {
            /**
             * @var Builder $this
             */
            [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);
            if (! $value instanceof Verta) {
                $value = Verta::parse($value);
            }
            $this->where($column, $operator, $value->DateTime(), $boolean);

            return $this;
        });

        Builder::macro('whereDateJalali', function (string $column, $operator, $value = null, $boolean = 'and') {
            /**
             * @var Builder $this
             */
            [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);
            if (! $value instanceof Verta) {
                $value = Verta::parse($value);
            }
            $this->whereDate($column, $operator, $value->DateTime(), $boolean);

            return $this;
        });

        Builder::macro('whereInMonthJalali', function (string $column, $month, $year = null) {
            /**
             * @var Builder $this
             */
            $year = $year ? $year : verta()->year;
            $this->where(function ($query) use ($column, $month, $year) {
                $query->whereDate($column, '>=', Verta::createJalaliDate($year, $month, 1)->DateTime())
                    ->whereDate($column, '<', Verta::createJalaliDate($year, $month, 1)->addMonth()->DateTime());
            });

            return $this;
        });

        Builder::macro('whereInYearJalali', function (string $column, $year = null) {
            /**
             * @var Builder $this
             */
            $year = $year ? $year : verta()->year;
            $this->where(function ($query) use ($column, $year) {
                $query->whereDate($column, '>=', Verta::createJalaliDate($year, 1, 1)->DateTime())
                    ->whereDate($column, '<', Verta::createJalaliDate($year, 1, 1)->addYear()->DateTime());
            });

            return $this;
        });
    }

}
