<?php

return [

    'show_warnings' => false,

    // Laravel-dompdf usa base_path('public') si esto es null, sin pasar por
    // el binding de 'path.public'. En produccion (Spaceship) la carpeta
    // publica real esta fuera de la raiz del proyecto Laravel
    // (ver index.php: $app->bind('path.public', ...)), asi que hay que
    // forzar el uso de public_path() para que lo resuelva correctamente.
    'public_path' => public_path(),

    'convert_entities' => true,

    'options' => [
        'font_dir' => storage_path('fonts'),
        'font_cache' => storage_path('fonts'),
        'temp_dir' => sys_get_temp_dir(),
        'chroot' => [realpath(base_path()), realpath(public_path())],
        'allowed_protocols' => [
            'data://' => ['rules' => []],
            'file://' => ['rules' => []],
            'http://' => ['rules' => []],
            'https://' => ['rules' => []],
        ],
        'artifactPathValidation' => null,
        'log_output_file' => null,
        'enable_font_subsetting' => false,
        'pdf_backend' => 'CPDF',
        'default_media_type' => 'screen',
        'default_paper_size' => 'a4',
        'default_paper_orientation' => 'portrait',
        'default_font' => 'serif',
        'dpi' => 96,
        'enable_php' => false,
        'enable_javascript' => true,
        'enable_remote' => false,
        'allowed_remote_hosts' => null,
        'font_height_ratio' => 1.1,
        'enable_html5_parser' => true,
    ],

];
