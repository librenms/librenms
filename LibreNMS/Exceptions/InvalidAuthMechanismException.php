<?php

namespace LibreNMS\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvalidAuthMechanismException extends \RuntimeException
{
    public function __construct(string $mechanism)
    {
        parent::__construct($mechanism . ' not found as auth_mechanism');
    }

    public function render(Request $request): Response|JsonResponse
    {
        $title = trans('exceptions.invalid_auth_mechanism.title');
        $message = trans('exceptions.invalid_auth_mechanism.message');

        return $request->wantsJson() ? response()->json([
            'status' => 'error',
            'message' => "$title: $message",
        ], 500) : response()->view('errors.generic', [
            'title' => $title,
            'content' => $message,
        ], 500);
    }
}
