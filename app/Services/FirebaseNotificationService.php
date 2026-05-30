<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class FirebaseNotificationService
{
    private const TOKEN_CACHE_KEY = 'firebase.messaging.access_token';

    public function getFirebaseAccessToken(): string
    {
        $cachedToken = Cache::get(self::TOKEN_CACHE_KEY);
        if (!empty($cachedToken)) {
            return $cachedToken;
        }

        $credentialsPath = config('fcm.credentials_path');

        if (!is_string($credentialsPath) || !file_exists($credentialsPath)) {
            Log::error('Firebase credentials file is missing', [
                'credentials_path' => $credentialsPath,
            ]);

            throw new RuntimeException('Firebase credentials file is missing.');
        }

        try {
            $credentials = new ServiceAccountCredentials(
                [config('fcm.scope')],
                $credentialsPath
            );

            $token = $credentials->fetchAuthToken();
        } catch (Throwable $e) {
            Log::error('Firebase access token generation failed', [
                'credentials_path' => $credentialsPath,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new RuntimeException('Firebase credentials could not be used.', 0, $e);
        }

        if (empty($token['access_token'])) {
            Log::error('Firebase access token response was invalid', [
                'token_response_keys' => array_keys($token),
            ]);

            throw new RuntimeException('Firebase access token response was invalid.');
        }

        $ttl = max((int) ($token['expires_in'] ?? 3600) - 60, 60);
        Cache::put(self::TOKEN_CACHE_KEY, $token['access_token'], now()->addSeconds($ttl));

        return $token['access_token'];
    }

    public function sendPushNotification(
        string $deviceToken,
        string $title,
        string $message,
        array $data = []
    ): array {
        $deviceToken = trim($deviceToken);

        if ($deviceToken === '') {
            Log::warning('Firebase notification skipped because device token is missing', [
                'title' => $title,
                'data' => $data,
            ]);

            return [
                'success' => false,
                'invalid_token' => true,
                'message' => 'Missing device token.',
            ];
        }

        $projectId = config('fcm.project_id');
        if (!is_string($projectId) || trim($projectId) === '') {
            Log::error('Firebase project ID is missing');

            throw new InvalidArgumentException('Firebase project ID is missing.');
        }

        $payload = $this->buildPayload($deviceToken, $title, $message, $data);
        $url = sprintf(config('fcm.endpoint'), $projectId);

        Log::info('Firebase notification request payload', [
            'url' => $url,
            'payload' => $this->maskPayloadToken($payload),
        ]);

        try {
            $response = Http::withToken($this->getFirebaseAccessToken())
                ->acceptJson()
                ->timeout((int) config('fcm.timeout', 30))
                ->post($url, $payload);
        } catch (Throwable $e) {
            Log::error('Firebase HTTP request failed', [
                'device_token' => $this->maskToken($deviceToken),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'invalid_token' => false,
                'message' => $e->getMessage(),
            ];
        }

        $responseBody = $response->json() ?? ['raw_body' => $response->body()];

        Log::info('Firebase notification response', [
            'status' => $response->status(),
            'body' => $responseBody,
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'invalid_token' => false,
                'response' => $responseBody,
            ];
        }

        if (in_array($response->status(), [401, 403], true)) {
            Cache::forget(self::TOKEN_CACHE_KEY);

            Log::error('Firebase credentials were rejected or expired', [
                'status' => $response->status(),
                'body' => $responseBody,
            ]);
        }

        $invalidToken = $this->isInvalidTokenResponse($response, $responseBody);

        if ($invalidToken) {
            Log::warning('Firebase reported an invalid device token', [
                'device_token' => $this->maskToken($deviceToken),
                'status' => $response->status(),
                'body' => $responseBody,
            ]);
        } else {
            Log::error('Firebase API error', [
                'device_token' => $this->maskToken($deviceToken),
                'status' => $response->status(),
                'body' => $responseBody,
            ]);
        }

        return [
            'success' => false,
            'invalid_token' => $invalidToken,
            'status' => $response->status(),
            'response' => $responseBody,
        ];
    }

    private function buildPayload(string $deviceToken, string $title, string $message, array $data): array
    {
        return [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $message,
                ],
                'data' => $this->stringifyData($data),
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'sound' => 'default',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function stringifyData(array $data): array
    {
        $stringData = [];

        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $stringData[$key] = json_encode($value);
                continue;
            }

            if (is_bool($value)) {
                $stringData[$key] = $value ? '1' : '0';
                continue;
            }

            $stringData[$key] = (string) $value;
        }

        return $stringData;
    }

    private function isInvalidTokenResponse(Response $response, array $responseBody): bool
    {
        $status = $responseBody['error']['status'] ?? null;
        $message = $responseBody['error']['message'] ?? '';

        if (in_array($status, ['INVALID_ARGUMENT', 'NOT_FOUND', 'UNREGISTERED'], true)) {
            return true;
        }

        return $response->status() === 404
            || str_contains($message, 'registration token is not a valid FCM registration token')
            || str_contains($message, 'Requested entity was not found');
    }

    private function maskToken(string $token): string
    {
        return substr($token, 0, 8) . '...' . substr($token, -6);
    }

    private function maskPayloadToken(array $payload): array
    {
        if (!empty($payload['message']['token'])) {
            $payload['message']['token'] = $this->maskToken($payload['message']['token']);
        }

        return $payload;
    }
}
