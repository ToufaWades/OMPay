<?php
namespace App\Traits;

trait ApiResponse
{
    protected function success($data = null, string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    protected function error(string $message = 'Error', int $code = 400, $data = null)
    {
        return response()->json([
            'success' => false,
            'data' => $data,
            'message' => $message,
        ], $code);
    }
        public function apiResponse($success, $data = null, $message = '', $code = 200)
        {
            return response()->json([
                'success' => $success,
                'data' => $data,
                'message' => $message,
            ], $code);
        }
}