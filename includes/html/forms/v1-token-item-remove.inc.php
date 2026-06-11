<?php

header('Content-type: application/json');

if (Gate::denies('api.access')) {
    exit(json_encode(['status' => 'error', 'message' => 'ERROR: You need permission']));
}

$tokenId = $_POST['v1_token_id'] ?? null;

if (! is_numeric($tokenId) || ($_POST['confirm'] ?? '') !== 'yes') {
    exit(json_encode(['status' => 'error', 'message' => 'ERROR: Invalid data']));
}

$token = \Laravel\Sanctum\PersonalAccessToken::where('id', $tokenId)
    ->where('tokenable_id', Auth::id())
    ->where('tokenable_type', \App\Models\User::class)
    ->first();

if (! $token) {
    exit(json_encode(['status' => 'error', 'message' => 'ERROR: Token not found']));
}

$token->delete();

echo json_encode(['status' => 'ok', 'message' => 'V1 API token has been removed']);
