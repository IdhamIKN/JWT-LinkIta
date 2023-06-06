<?php

namespace App\Constants;

use App\Helpers\PhpConstant;

class LKConstant extends PhpConstant
{
    // Request
    public const Balance = 'saldo';
    public const Bank = 'bank';
    public const Struk = 'struk';
    public const Widget = 'url_widget';
    public const Trans = 'transaksi';
    public const Mutasi = 'mutasi';

    // Produk
    public const TFBank = 'TRSFBANK';
    public const VABank = 'TRANSFERVA';
    public const OVO = 'OVO';
    public const DANA = 'DANA';
    public const GOPAY = 'GOPAY';
    public const KASPRO = 'KASPRO';
    public const LINKAJA = 'LINKAJA';
    public const SHOPEEPAY = 'SHOPEEPAY';

    public static function nominal(): array
    {
        return [
            20000, 50000, 100000, 200000, 500000, 1000000, 5000000, 10000000, 50000000,
        ];
    }
}
