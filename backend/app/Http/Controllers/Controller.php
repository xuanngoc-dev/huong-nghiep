<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesApiResponse;

abstract class Controller
{
    use HandlesApiResponse;
}
