<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TraceabilityEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'planting_batch_id',
        'event_name',
        'description',
        'image',
        'recorded_by',
        'recorded_at',
        'data_hash',
        'blockchain_tx_hash',
        'hash_version',
        'image_sha256',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'hash_version' => 'integer',
        ];
    }

    public function plantingBatch(): BelongsTo
    {
        return $this->belongsTo(PlantingBatch::class);
    }

    public function calculateDataHash(): string
    {
        $payload = [
            'hash_version' => (int) $this->hash_version,
            'planting_batch_id' => (int) $this->planting_batch_id,
            'event_name' => (string) $this->event_name,
            'description' => (string) ($this->description ?? ''),
            'recorded_by' => (string) $this->recorded_by,
            'recorded_at' => $this->recorded_at?->format('Y-m-d H:i:s'),
            'image' => (string) ($this->image ?? ''),
            'image_sha256' => (string) ($this->image_sha256 ?? ''),
        ];

        return hash('sha256', json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
        ));
    }

    public function hasValidHash(): ?bool
    {
        if ($this->hash_version !== 1) {
            return null;
        }

        return hash_equals(
            (string) $this->data_hash,
            $this->calculateDataHash()
        );
    }
}