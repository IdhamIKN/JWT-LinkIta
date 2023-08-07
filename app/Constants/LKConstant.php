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

    // Produk Transfer
    public const TFBank = 'TRSFBANK';
    public const VABank = 'TRANSFERVA';

    // Produk Ewallet
    public const OVO = 'OVO';
    public const DANA = 'DANA';
    public const GOPAY = 'GOPAY';
    public const KASPRO = 'KASPRO';
    public const LINKAJA = 'LINKAJA';
    public const SHOPEEPAY = 'SHOPEEPAY';

    // Produk PLN
    public const PLNPB = 'PLNPASCA';
    public const PLNNON = 'PLNNONTAG';
    public const PLNPRA = 'PLNPRA';

    // Produk Asuransi
    public const BPJS = 'BPJSKES';
    public const BPJSK = 'BPJSKET';

    // Produk
    // public const  = '';

    // Produk
    // public const  = '';

    // Produk
    // public const  = '';

    // Produk
    // public const  = '';

    public static function nominal(): array
    {
        return [
            20000, 50000, 100000, 200000, 500000, 1000000, 5000000, 10000000, 50000000,
        ];
    }
}
