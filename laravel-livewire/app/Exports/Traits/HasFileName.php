<?php

namespace App\Exports\Traits;

trait HasFileName
{
    public string $fileName;

    public function fileName(): string
    {
        return $this->fileName;
    }
}
