<?php

return [
    'activities' => [
        'fertilizing' => 'Bón phân',
        'spraying' => 'Phun thuốc',
        'watering' => 'Tưới nước',
        'pruning' => 'Cắt tỉa',
    ],

    'fertilizers' => [
        // Phân hữu cơ (Bón lót và cải tạo đất)
        'huu_co_vi_sinh_sông_gianh' => 'Phân hữu cơ vi sinh Sông Gianh',
        'huu_co_khoáng_úc_bounce_back' => 'Phân hữu cơ khoáng Bounce Back (Úc)',

        // Phân NPK chuyên dùng (Bón thúc sinh trưởng, ra hoa, đậu quả)
        'npk_dau_trau_13_13_13' => 'Phân NPK Đầu Trâu 13-13-13 (Nuôi cây xanh lá)',
        'npk_dau_trau_15_5_20' => 'Phân NPK Đầu Trâu 15-5-20 (Nuôi quả, tăng ngọt)',
        'npk_yaramilia_complex' => 'Phân NPK YaraMila Complex 12-11-18',

        // Phân vi lượng & kích rễ
        'kich_re_orgamin' => 'Chế phẩm kích rễ Orgamin sinh học',
        'vi_luong_combi_chelax' => 'Phân vi lượng Combi Chelax (Chống rụng quả, vàng lá)',
    ],

    'pesticides' => [
        // Thuốc trừ bệnh (Nấm, vi khuẩn gây thối quả, đốm lá)
        'tri_nam_anvil_5sc' => 'Thuốc trừ nấm Anvil 5SC (Cách ly 7 ngày)',
        'tri_nam_ridomil_gold_68wg' => 'Thuốc trừ nấm Ridomil Gold 68WG (Cách ly 7 ngày)',
        'sinh_hoc_validacin_5sl' => 'Thuốc trừ bệnh sinh học Validacin 5SL (Cách ly 3 ngày)',

        // Thuốc trừ sâu, rầy, bọ trĩ (Đối tượng hại cà chua rất nặng)
        'trit_sau_sinh_hoc_radiant' => 'Thuốc trừ trĩ sâu sinh học Radiant 60SC (Cách ly 3 ngày)',
        'tru_sau_sinh_hoc_tập_kỳ' => 'Thuốc trừ sâu sinh học Tập Kỳ 1.8EC (Cách ly 3 ngày)',
        'tru_sau_sinh_hoc_emamectin' => 'Thuốc trừ sâu sinh học Map WAM 18EC (Cách ly 5 ngày)',
    ],

    'fertilizer_methods' => [
        'root' => 'Bón gốc',
        'foliar' => 'Bón qua lá',
        'irrigation' => 'Bón qua hệ thống tưới',
    ],

    'water_sources' => [
        'well' => 'Nước giếng',
        'tap' => 'Nước máy',
        'tank' => 'Nước từ bể chứa',
        'rain' => 'Nước mưa thu gom',
    ],
];