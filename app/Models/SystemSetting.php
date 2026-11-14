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

    public const DEFAULT_FARM_NAME = 'Đất Lành';

    public static function contactSettings(): array
    {
        return Cache::rememberForever('system_contact_settings', function () {
            $settings = self::where('group', 'contact')
                ->pluck('value', 'key');

            return [
                'farm_name' => $settings['farm_name'] ?? self::DEFAULT_FARM_NAME,

                'hotline' => $settings['hotline'] ?? '0987.654.321',

                'email' => $settings['email'] ?? 'lienhe@datlanhfarm.vn',

                'farm_address' => $settings['farm_address']
                    ?? 'Số 86 đường Lê Thánh Tôn, Phường Sài Gòn, Quận 1, Thành phố Hồ Chí Minh',

                'farm_latitude' => (float) ($settings['farm_latitude'] ?? 10.7769),

                'farm_longitude' => (float) ($settings['farm_longitude'] ?? 106.7009),
            ];
        });
    }

    public static function farmName(): string
    {
        $value = (string) (
            self::contactSettings()['farm_name'] ?? self::DEFAULT_FARM_NAME
        );

        // Chuẩn hóa khoảng trắng, loại xuống dòng khi dùng trong tiêu đề email.
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');

        return $value !== ''
            ? $value
            : self::DEFAULT_FARM_NAME;
    }
}