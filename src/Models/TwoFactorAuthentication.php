<?php

namespace ArchiElite\TwoFactorAuthentication\Models;

use ArchiElite\TwoFactorAuthentication\RecoveryCode;
use Botble\ACL\Models\User;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class TwoFactorAuthentication extends BaseModel
{
    protected $table = 'two_factor_authentications';

    protected $fillable = [
        'user_id',
        'secret',
        'recovery_codes',
        'confirmed_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
