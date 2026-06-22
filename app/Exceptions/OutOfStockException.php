<?php

namespace App\Exceptions;

use Exception;

class OutOfStockException extends Exception
{
    public function __construct($sparePart, $availableQty, $requestedQty)
    {
        $message = "Spare part \"{$sparePart->name}\" tidak cukup. Tersedia: {$availableQty}, Diminta: {$requestedQty}";
        parent::__construct($message);
    }

    public function render()
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
