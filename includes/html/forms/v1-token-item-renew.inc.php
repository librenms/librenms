<?php

header('Content-type: application/json');

if (Gate::denies('api.access')) {
    exit(json_encode(['status' => 'error', 'message' => 'ERROR: You need permission']));
}

$tokenId = $_POST['v1_token_id'] ?? null;
$extendDays = trim($_POST['extend_days'] ?? '');

if (! is_numeric($tokenId)) {
    exit(json_encode(['status' => 'error', 'message' => 'ERROR: Invalid token']));
}

if (! is_numeric($extendDays) || (int) $extendDays < 1) {
    exit(json_encode(['status' => 'error', 'message' => 'ERROR: Days must be a positive number']));
}

$canManage = Gate::allows('api.management');

$query = \Laravel\Sanctum\PersonalAccessToken::where('id', $tokenId);

if (! $canManage) {
    $query->where('tokenable_id', Auth::id())
          ->where('tokenable_type', \App\Models\User::class);
}

$token = $query->first();

if (! $token) {
    exit(json_encode(['status' => 'error', 'message' => 'ERROR: Token not found']));
}

$token->expires_at = now()->addDays((int) $extendDays);
$token->save();

echo json_encode([
    'status' => 'ok',
    'message' => 'Token expiration updated successfully.',
    'expires_at' => $token->expires_at->diffForHumans(),
]);
