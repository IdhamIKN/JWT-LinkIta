<?php

namespace App\Constants;

use App\Helpers\PhpConstant;

class LKMethod extends PhpConstant
{
    // Inquiry Trnasfer
    public const InqBank = 'transfer_bank_inq';
    public const InqCheck = 'transfer_bank_inq_check';

    // Paymen Transfer
    public const PayBank = 'transfer_bank_payment';
    public const PayCheck = 'transfer_bank_payment_check';

    // Inquiry Emoney
    public const InqEmoney = 'emoney_inq';
    public const EmoneyInq = 'emoney_inq_check';

    // Payment Emoney
    public const PayEmoney = 'emoney_payment';
    public const EmoneyPay = 'emoney_payment_check';

}
