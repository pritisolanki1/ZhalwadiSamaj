<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class ApiController extends Controller
{
    use ApiResponser;

    public function __construct()
    {
        Validator::extend('existsWithOther', function ($attribute, $value, $parameters, $validator) {
            if (count($parameters) < 4) {
                throw new InvalidArgumentException('Validation rule game_fixture requires 4 parameters.');
            }

            $input = $validator->getData();
            $verifier = $validator->getPresenceVerifier();
            $collection = $parameters[0];
            $column = $parameters[1];
            $extra = [$parameters[2] => $parameters[3]];

            $count = $verifier->getMultiCount($collection, $column, (array) $value, $extra);

            return $count >= 1;
        });
    }
}
