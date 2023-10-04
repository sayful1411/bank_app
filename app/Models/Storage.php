<?php 

namespace App\Models;

interface Storage
{
    public function save(string $model, array $data): void;

    public function load(string $model): array;
    
    public function getModelPath(string $model);
    
}