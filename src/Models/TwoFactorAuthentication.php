<?php

namespace ArchiElite\TwoFactorAuthentication\Models;

use ArchiElite\TwoFactorAuthentication\RecoveryCode;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Crypt;

class TwoFactorAuthentication extends BaseModel
{
    protected $table = 'two_factor_authentications';

    protected $fillable = [
        'authenticatable_id',
        'authenticatable_type',
        'secret',
        'recovery_codes',
        'confirmed_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): MorphTo
    {
        return $this->authenticatable();
    }

    public function recoveryCodes(): array
    {
        return json_decode(Crypt::decrypt($this->recovery_codes), true);
    }

    public function replaceRecoveryCode($code): void
    {
        $this->forceFill([
            'recovery_codes' => Crypt::encrypt(str_replace(
                $code,
                RecoveryCode::generate(),
                Crypt::decrypt($this->recovery_codes)
            )),
        ])->save();
    }
}
