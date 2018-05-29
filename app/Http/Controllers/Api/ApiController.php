<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @apiDefine NotFoundError
 * @apiError NotFound The id of this resource was not found
 * 
 * @apiErrorExample Error-Response:
 *      HTTP/1.1 404 Not Found
 *      {
 *          "status": "Item not found"
 *      }
 *
 */

/**
 * @apiDefine Pagination
 * @apiParam {Number} [per_page=50] How many items to retrieve
 * @apiParam {Number} [current_page=1] Active page of items
 */
class ApiController extends Controller
{
    /**
     * Return an api response
     * This method is used when an object ir returned
     * @param Array $data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function objectResponse($data)
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
    protected function paginateResponse($data)
    {
        $per_page = (request()->per_page ? request()->per_page : 50);
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
    protected function messageResponse($message)
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
    protected function errorResponse($http_code = 400, $message = 'Bad Request')
    {
        return response()->json(['error' => $message], $http_code);
    }


    /**
     * Get Boolean input and use corresponding value
     * @param string Name of input to retrieve
     *
     * @return boolean
     */
    protected function booleanInput($value)
    {
        return (request()->input($value) != null ? strtolower(request()->input($value)) == 'true' : false);
    }
}
