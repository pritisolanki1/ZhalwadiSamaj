<?php

namespace App\Filters;

use App\UnlockedDriver;

class UnlockedDriverFilters extends Filters
{
    protected $filters = [
        'visits_from', 'visits_to', 'status_id', 'order_by', 'type', 'unlocker_id', 'created_at_from',
        'created_at_till', 'hired_at_from', 'hired_at_till',
    ];

    protected function visits_from($number)
    {
        $this->builder->where('visits_count', '>=', $number);
    }

    protected function created_at_from()
    {
        $this->builder->where('created_at', '>=', request('created_at_from'));
    }

    protected function created_at_till()
    {
        $this->builder->where('created_at', '<=', request('created_at_till'));
    }

    protected function hired_at_from()
    {
        $this->builder->where('hired_at', '>=', request('hired_at_from'));
    }

    protected function hired_at_till()
    {
        $this->builder->where('hired_at', '<=', request('hired_at_till'));
    }

    protected function visits_to($number)
    {
        $this->builder->where('visits_count', '<=', $number);
    }

    protected function status_id($id)
    {
        $this->builder->where('status_id', $id);
    }

    protected function order_by($column)
    {
        $this->builder->orderBy($column, request('order_direction', 'asc'));
    }

    protected function type()
    {
        $this->builder->where('type', request('type', UnlockedDriver::TYPE_MANUAL));
    }

    protected function unlocker_id()
    {
        $this->builder->where('unlocker_id', request('unlocker_id'));
    }
}
