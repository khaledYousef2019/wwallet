<?php

namespace App\Events;

class FetchCurrencyInfo implements EventInterface
{
    public function __construct(
        public string $coinAddress
    ) {}
}
?>
