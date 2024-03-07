<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Modules\Ad\app\Models\Ad;
use Modules\Core\app\Helpers\CoreHelper;
use Modules\Payment\app\Models\Payment;
use Modules\Shop\app\Models\Shop;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'phone',
        'password',
        'avatar',
        'balance'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * @return string
     */
    public function avatar(): string
    {
        $size = 200;
        $default = 'mp';
        $rating = 'g';
        $url = 'https://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($this->email)));
        $url .= "?s=$size&d=$default&r=$rating";

        foreach ([] as $key => $val) {
            $url .= ' ' . $key . '="' . $val . '"';
        }

        return $this->getAttribute("avatar") ? asset('/storage/images/' . $this->getAttribute("avatar")) : $url;
    }

     /**
     * @return string
     */
    public function fullName(): string
    {
        return "{$this->getAttribute('first_name')} {$this->getAttribute('last_name')}";
    }

    public function hiddenPhone(): string
    {
        return Str::of($this->getAttribute('phone'))->mask('X', -2,'2');
    }

    public function balance()
    {
        return CoreHelper::formatPrice($this->getAttribute('balance'));
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Ad::class,'ad_favorites');
    }

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    public function isShop(): bool
    {
        return $this->shop()->exists();
    }
}
