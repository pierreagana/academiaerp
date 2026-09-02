<?php

namespace App\Support\Notifications;

use App\Modules\Academic\Domain\Models\DeviceToken;
use App\Modules\Academic\Domain\Models\ParentAccount;
use App\Modules\SuperAdmin\Domain\Models\GlobalSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

/**
 * Thin wrapper around kreait/firebase-php — never lets a Firebase failure
 * (misconfigured project, unreachable API, unregistered device token) bubble
 * up as an exception to the caller. Same discipline as the payment gateway
 * HTTP calls: log and degrade, don't crash the request.
 */
class FirebasePushService
{
    private const SERVICE_ACCOUNT_PATH = 'firebase/service-account.json';

    public function isConfigured(): bool
    {
        $projectId = GlobalSetting::where('key', 'firebase_project_id')->value('value');

        return !empty($projectId) && Storage::disk('local')->exists(self::SERVICE_ACCOUNT_PATH);
    }

    /**
     * Sends to a single device token. Returns false (never throws) on any
     * failure. An unregistered/uninstalled token is detected and wiped from
     * whichever ParentAccount still holds it, so it stops being retried.
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('Firebase push skipped — not configured', ['title' => $title]);
            return false;
        }

        try {
            $messaging = (new Factory)
                ->withServiceAccount(Storage::disk('local')->path(self::SERVICE_ACCOUNT_PATH))
                ->createMessaging();

            $message = CloudMessage::new()
                ->withNotification(FirebaseNotification::create($title, $body))
                ->withData($data)
                ->toToken($token);

            $messaging->send($message);

            return true;
        } catch (NotFound $e) {
            DeviceToken::where('token', $e->token())->delete();
            Log::info('Firebase push token no longer registered — cleared', ['token' => $e->token()]);
            return false;
        } catch (MessagingException|\Throwable $e) {
            Log::error('Firebase push send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Sends the same notification to every device (web + mobile, however many
     * each parent has registered) belonging to every parent in the list.
     * A parent with zero registered devices is counted once as skipped.
     *
     * @param iterable<ParentAccount> $parents
     * @return array{sent:int,failed:int,skipped:int}
     */
    public function sendToParents(iterable $parents, string $title, string $body, array $data = []): array
    {
        $result = ['sent' => 0, 'failed' => 0, 'skipped' => 0];

        foreach ($parents as $parent) {
            $tokens = $parent->deviceTokens;

            if ($tokens->isEmpty()) {
                $result['skipped']++;
                continue;
            }

            foreach ($tokens as $deviceToken) {
                if ($this->sendToToken($deviceToken->token, $title, $body, $data)) {
                    $result['sent']++;
                } else {
                    $result['failed']++;
                }
            }
        }

        return $result;
    }
}
