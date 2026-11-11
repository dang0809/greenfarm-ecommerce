<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    public static function contactSettings(): array
    {
        return Cache::rememberForever('system_contact_settings', function () {
            $settings = self::where('group', 'contact')->pluck('value', 'key');

            return [
                'hotline'        => $settings['hotline'] ?? '0987.654.321',
                'email'          => $settings['email'] ?? 'lienhe@datlanhfarm.vn',
                'farm_address'   => $settings['farm_address'] ?? 'Số 86 đường Lê Thánh Tôn, Phường Sài Gòn, Quận 1, Thành phố Hồ Chí Minh',
                'farm_latitude'  => (float) ($settings['farm_latitude'] ?? 10.7769),
                'farm_longitude' => (float) ($settings['farm_longitude'] ?? 106.7009),
            ];
        });
    }
}
