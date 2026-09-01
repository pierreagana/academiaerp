<?php

namespace App\Modules\Finance\Domain\Exceptions;

use Exception;

class InsufficientWalletBalanceException extends Exception
{
    public function __construct(public readonly float $balance, public readonly float $requested)
    {
        parent::__construct("Solde insuffisant : {$balance} disponible, {$requested} requis.");
    }
}
