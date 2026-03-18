<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     * @param  array<string, mixed>|null  $meta
     */
    public function log(
        string $action,
        ?Model $auditable = null,
        ?array $before = null,
        ?array $after = null,
        ?array $meta = null,
        ?int $userId = null,
        ?DateTimeInterface $occurredAt = null,
    ): AuditLog {
        $occurredAt = $occurredAt ?: now();

        return AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable ? (int) $auditable->getKey() : null,
            'before' => $this->normalizePayload($before),
            'after' => $this->normalizePayload($after),
            'meta' => $this->normalizePayload($meta),
            'ip' => Request::ip(),
            'user_agent' => $this->limitString((string) (Request::header('User-Agent') ?? ''), 512),
            'occurred_at' => Carbon::parse($occurredAt),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $payload
     * @return array<string, mixed>|null
     */
    private function normalizePayload(?array $payload): ?array
    {
        if ($payload === null) {
            return null;
        }

        // Keep it JSON-safe and avoid huge objects.
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return ['_error' => 'payload_not_json_serializable'];
        }

        // Limit payload size to avoid bloating the DB.
        // Rough limit: 32KB.
        if (strlen($json) > 32768) {
            return [
                '_truncated' => true,
                'keys' => array_slice(array_keys($payload), 0, 50),
            ];
        }

        /** @var array<string, mixed> */
        return json_decode($json, true) ?: null;
    }

    private function limitString(string $value, int $max): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (strlen($value) <= $max) {
            return $value;
        }

        return substr($value, 0, max(0, $max - 3)).'...';
    }
}
