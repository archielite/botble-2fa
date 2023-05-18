<?php

namespace ArchiElite\TwoFactorAuthentication\Models;

use Botble\ACL\Models\User;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorAuthentication extends BaseModel
{
    protected $table = 'two_factor_authentications';

    protected $fillable = [
        'user_id',
        'secret',
        'recovery_codes',
        'confirmed_at',
    ];

    protected $casts = [
        'recovery_codes' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
