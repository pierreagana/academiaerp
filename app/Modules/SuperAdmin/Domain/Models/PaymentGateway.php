<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'status',
        'api_key',
        'secret_key',
        'webhook_secret',
    ];

    protected function casts(): array
    {
        return [
            // Encrypted uniformly for every gateway. Most providers' "api_key" is a
            // public/publishable key, but Wave's is a full Bearer secret — rather than
            // special-case which gateway's api_key is sensitive, encrypt all three.
            'api_key' => 'encrypted',
            'secret_key' => 'encrypted',
            'webhook_secret' => 'encrypted',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
