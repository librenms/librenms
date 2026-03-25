<?php

header('Content-type: application/json');

if (Gate::denies('api.access')) {
    exit(json_encode(['status' => 'error', 'message' => 'ERROR: You need permission']));
}

$tokenName = trim($_POST['token_name'] ?? '');

if ($tokenName === '') {
    exit(json_encode(['status' => 'error', 'message' => 'ERROR: Token name is required']));
}

$isAdmin = Auth::user()->hasRole('admin');

if ($isAdmin && ! empty($_POST['user_id'])) {
    $user = \App\Models\User::find($_POST['user_id']);
    if (! $user) {
        exit(json_encode(['status' => 'error', 'message' => 'ERROR: User not found']));
    }
} else {
    $user = Auth::user();
}

$expiresAt = null;
$expiresIn = trim($_POST['expires_in'] ?? '');

if ($expiresIn !== '') {
    if (! is_numeric($expiresIn) || (int) $expiresIn < 1) {
        exit(json_encode(['status' => 'error', 'message' => 'ERROR: Expiration must be a positive number of days']));
    }
    $expiresAt = now()->addDays((int) $expiresIn);
}

$newToken = $user->createToken($tokenName, ['*'], $expiresAt);

echo json_encode([
    'status' => 'ok',
    'message' => 'V1 API token created successfully.',
    'token' => $newToken->plainTextToken,
    'token_id' => $newToken->accessToken->id,
    'token_name' => $newToken->accessToken->name,
    'username' => $user->username,
    'created_at' => 'Just now',
    'expires_at' => $expiresAt ? $expiresAt->diffForHumans() : 'Never',
]);
