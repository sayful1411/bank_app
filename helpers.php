<?php 

function view(string $view, array $data = []): void{
    extract($data);

    // Replace slashes with directory separator and add .php extension
    $view = str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';

    // Construct the full path to the view file
    $viewPath = __DIR__ . "/views/{$view}";

    // Check if the file exists before requiring it
    if (file_exists($viewPath)) {
        require $viewPath;
    } else {
        echo "View not found: $view";
    }
}

function redirect($location): void{
    header("Location: {$location}");
}