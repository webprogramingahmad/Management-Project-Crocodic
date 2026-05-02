<?php

namespace App\Http\Controllers;

use App\Models\NotificationRead;
use Illuminate\Http\Request;

class OpenDashboardNotificationController extends Controller
{
    /**
     * Mark dashboard notification as read, then redirect.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'key' => ['required', 'string', 'max:255'],
            'to' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();
        $notificationKey = (string) $request->query('key');
        $target = $this->normalizeTarget((string) $request->query('to'));

        NotificationRead::query()->firstOrCreate(
            [
                'id_user' => $user->id,
                'notification_key' => $notificationKey,
            ],
            [
                'read_at' => now(),
            ]
        );

        return redirect($target);
    }

    private function normalizeTarget(string $target): string
    {
        if (str_starts_with($target, '/')) {
            return $target;
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $parsed = parse_url($target);
        $targetHost = $parsed['host'] ?? null;

        if (!$targetHost || ($appHost && strcasecmp($targetHost, (string) $appHost) !== 0)) {
            return '/';
        }

        $path = $parsed['path'] ?? '/';
        $query = isset($parsed['query']) ? ('?' . $parsed['query']) : '';

        return $path . $query;
    }
}
