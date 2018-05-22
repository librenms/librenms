<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ApiController extends Controller
{

    /**
     * Return an api response
     * This method is used when an object ir returned.
     * @param Array $data
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    protected function object_response($data)
    {
        return response()->json($data);
    }


    /**
     * Return an api response with pagination
     * 
     * @param \Illuminate\Database\Eloquent\Collection $data
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    protected function paginate_response($data)
    {
        $per_page = (request()->per_page ? request()->per_page : 15);
        $current_page = (request()->current_page ? request()->current_page : 1);
        return response()->json($data->paginate($per_page, ['*'], 'page', $current_page));
    }


    /**
     * Return an api response
     * This method is for when data is not returned rather a status message.
     * @param String $message
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    protected function message_response($message)
    {
        return response()->json([
            'message' => $message
        ]);
    }

    /**
     * Return an api error response
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error_response($http_code = 400, $message = 'Bad Request')
    {
        return response()->json(['error' => $message], $http_code);
    }
}
