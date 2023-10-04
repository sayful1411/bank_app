<?php 

function view(string $view, array $data = []): void{
    extract($data);

    require __DIR__ . "/views/{$view}.php";
}

function redirect($location): void{
    header("Location: {$location}");
}