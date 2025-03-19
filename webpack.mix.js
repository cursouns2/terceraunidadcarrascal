const mix = require('laravel-mix');

// Compilar JS y CSS usando PostCSS (para Tailwind CSS)
mix
    .js('resources/js/app.js', 'public/js')  // Compila JS
    .postCss('resources/css/app.css', 'public/css', [
        require('tailwindcss'),  // Asegúrate de que Tailwind esté instalado
    ])
    .version();  // Agregar versionado si es necesario
