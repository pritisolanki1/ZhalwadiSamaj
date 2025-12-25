<?php

namespace App\Filters;

use App\Employer;
use App\InAppropriateContentRequest;
use Illuminate\Database\Eloquent\Builder;

class ReviewFilters extends Filters
{
    protected $filters = ['reported_status', 'replied_status', 'reviewer_type', 'employer', 'company'];

    protected function replied_status($value)
    {
        if ($value == InAppropriateContentRequest::STATUS_REPLIED) {
            $this->builder->whereHas('reply');
        } elseif ($value == InAppropriateContentRequest::STATUS_UN_REPLIED) {
            $this->builder->whereDoesntHave('reply');
        }
    }

    protected function reviewer_type($value)
    {
        if ($value) {
            $this->builder->where('reviewer_type_id', $value);
        }
    }

    public function reported_status($value)
    {
        if ($value) {
            if ($value == InAppropriateContentRequest::STATUS_PENDING) {
                $this->builder->whereHas('report', function ($q) use ($value) {
                    $q->where('status', $value);
                });
            } elseif ($value == InAppropriateContentRequest::STATUS_RESOLVED) {
                $this->builder->whereHas('report', function ($q) use ($value) {
                    $q->where('status', $value);
                });
            } elseif ($value == InAppropriateContentRequest::STATUS_REJECT) {
                $this->builder->whereHas('report', function ($q) use ($value) {
                    $q->where('status', $value);
                });
            } else {
                $this->builder->whereHas('report');
            }
        }
    }

    public function company($value)
    {
        if ($value) {
            $this->builder->whereHasMorph(
                'reviewable',
                [Employer::class],
                function ($query1) use ($value) {
                    $query1->whereHas('company', function ($query2) use ($value) {
                        $query2->where('id', $value);
                    });
                }
            );
        }
    }

    public function employer($value)
    {
        if ($value) {
            $this->builder->whereHasMorph(
                'reviewable',
                [Employer::class],
                function (Builder $query) use ($value) {
                    $query->where('id', $value);
                }
            );
        }
    }
}
