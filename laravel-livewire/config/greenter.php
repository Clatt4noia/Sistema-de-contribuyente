<?php

use Greenter\Ws\Services\SunatEndpoints;

return [
    // Por defecto en 'beta' para usar el servidor Mock
    'mode' => env('GREENTER_MODE', 'beta'), 

    'storage' => [
        'disk_xml_cdr' => env('GREENTER_DISK_XML_CDR', 'public'),
        'xml_directory' => env('GREENTER_XML_DIR', 'xml'),
        'cdr_directory' => env('GREENTER_CDR_DIR', 'cdr'),
    ],

    'queues' => [
        'sunat' => env('GREENTER_QUEUE_SUNAT', 'sunat'),
    ], 

    'company' => [
        'ruc' => env('GREENTER_COMPANY_RUC', '20000000001'),
        'razonSocial' => env('GREENTER_COMPANY_NAME', 'GREEN SAC'),
        'nombreComercial' => env('GREENTER_COMPANY_COMMERCIAL_NAME', 'GREEN'),

        'address' => [
            'ubigeo' => env('GREENTER_COMPANY_UBIGEO', '150101'),
            'departamento' => env('GREENTER_COMPANY_DEPARTMENT', 'LIMA'),
            'provincia' => env('GREENTER_COMPANY_PROVINCE', 'LIMA'),
            'distrito' => env('GREENTER_COMPANY_DISTRICT', 'LIMA'),
            'direccion' => env('GREENTER_COMPANY_ADDRESS', 'Av. Villa Nueva 221'),
        ],

        // Valor por defecto '00001' agregado para evitar errores en validación de Transportista
        'mtc' => env('GREENTER_COMPANY_MTC', '1586716CNG'), 

        'clave_sol' => [
            'user' => env('GREENTER_SOL_USER', 'MODDATOS'),
            'password' => env('GREENTER_SOL_PASS', 'MODDATOS'),
        ],

        // Credenciales de prueba (Nubefact Mock)
        'credentials' => [
            'client_id' => env('GREENTER_CLIENT_ID', 'test-85e5b0ae-255c-4891-a595-0b98c65c9854'),
            'client_secret' => env('GREENTER_CLIENT_SECRET', 'test-Hty/M6QshYvPgItX2P0+Kw=='),
        ],
        
        'certificate_path' => env('GREENTER_CERT_PATH', public_path('certs/certificate.pem')),
    ],

    'endpoints' => [
        /* Servicios SOAP (Facturación, Retención, Guías antiguas)
        'fe' => [
            'beta' => SunatEndpoints::FE_BETA,
            'prod' => SunatEndpoints::FE_PRODUCCION,
        ],
        'retencion' => [
            'beta' => SunatEndpoints::RETENCION_BETA,
            'prod' => SunatEndpoints::RETENCION_PRODUCCION,
        ],
        'guia' => [
            'beta' => SunatEndpoints::GUIA_BETA,
            'prod' => SunatEndpoints::GUIA_PRODUCCION,
        ],*/
        
        // CORRECCIÓN PRINCIPAL: Servicios API REST (Guías Nuevas)
        'api' => [
            'beta' => [
                // Mock de Nubefact que acepta las credenciales 'test-'
                'auth' => 'https://gre-test.nubefact.com/v1',
                'cpe'  => 'https://gre-test.nubefact.com/v1'
            ],
            'dev' => [
                // Alias para modo desarrollo (usa mismos endpoints que beta)
                'auth' => 'https://gre-test.nubefact.com/v1',
                'cpe'  => 'https://gre-test.nubefact.com/v1'
            ],
            'prod' => [
                // Servidores oficiales de SUNAT
                'auth' => 'https://api-seguridad.sunat.gob.pe/v1',
                'cpe' => 'https://api-cpe.sunat.gob.pe/v1',
            ],
        ],
    ],

    'report' => [
        'params' => [
            'system' => [
                'logo' => env('GREENTER_COMPANY_LOGO', public_path('images/logo.png')),
                'hash' => '',
            ],
            'user' => [
                'header' => env('GREENTER_COMPANY_HEADER', 'Telf: <b>(01) 123456</b>'),
                'extras' => [
                    ['name' => 'CONDICIÓN DE PAGO', 'value' => 'Contado'],
                    ['name' => 'VENDEDOR', 'value' => 'VENDEDOR PRINCIPAL'],
                ],
                'footer' => env('GREENTER_COMPANY_FOOTER', '<p>Nro Resolución: <b>123456789</b></p>'),
            ]
        ],
        'twigOptions' => [
            /* 'cache' => storage_path('framework/cache/data/greenter/twig'), */
            'strict_variables' => true,
        ],
        'templates' => resource_path('views/vendor/laravel-greenter'),
        'options' => [
            'no-outline',
            'viewport-size' => '1280x1024',
            'page-width' => '21cm',
            'page-height' => '29.7cm',
        ],
        'bin_path' => env('GREENTER_PDF_BIN_PATH', 'C:/Program Files/wkhtmltopdf/bin/wkhtmltopdf.exe'),
    ],
];