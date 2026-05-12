<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_DEACTIVATED = 'deactivated';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_DEACTIVATED,
    ];

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'referral_code',
        'referrer_id',
        'status',
        'date_of_birth',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $member) {
            if (! $member->referral_code) {
                do {
                    $code = Str::upper(Str::random(8));
                } while (self::query()->where('referral_code', $code)->exists());

                $member->referral_code = $code;
            }
        });
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function profileImage(): MorphOne
    {
        return $this->morphOne(Document::class, 'documentable')->where('type', 'profile_image');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referrer_id')->withTrashed();
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(self::class, 'referrer_id')->whereNotNull('user_id');
    }

    public function rewardAchievers(): HasMany
    {
        return $this->hasMany(RewardAchiever::class);
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('user_id');
    }

    public function scopeRewardEligible($query)
    {
        return $query
            ->whereNotNull('user_id')
            ->where('status', self::STATUS_ACTIVE);
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim($this->first_name.' '.$this->last_name),
        );
    }
}
