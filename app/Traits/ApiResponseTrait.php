<?php

namespace App\Traits;

trait ApiResponseTrait
{

    public function successResponse($message = 'Success', $data = null, $code = 200)
    {
        return response()->json([
            'message' => $message,
            'data'    => $data,
        ], $code);
    }


    public function errorResponse($message = 'Error', $code = 400)
    {
        return response()->json([
            'message' => $message,
            'data'    => null,
        ], $code);
    }

    public function ExceptionResponse($message = 'Error',$error = null, $code = 500)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'error'    => $error,
        ], $code);
    }


}
