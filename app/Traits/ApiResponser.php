<?php

namespace App\Traits;

use App\Helpers\Constants;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\TextUI\XmlConfiguration\Constant;

trait ApiResponser
{

    protected function successResponse($data, $message = null, $code = 200)
    {
        return response()->json([
            'status' => 'Success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message = null, $code)
    {
        return response()->json([
            'status' => 'Error',
            'message' => $message,
            'data' => null
        ], $code);
    }

    public static function buildResponse(int $code = Constants::HTTP_CODE_403, string $status = Constants::ERROR_MESSAGE_403, $data = [])
    {

        return response()->json(array(
            'code' => $code,
            'status' => $status,
            'data' => $data,
            'time_stamp' => Carbon::now(),
        ));
    }
}
