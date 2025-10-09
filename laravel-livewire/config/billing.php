<?php

return [
    'tax_rate' => env('BILLING_TAX_RATE', 18),

    'sunat' => [
        'mode' => env('BILLING_SUNAT_MODE', 'homologation'),
        'ruc' => env('BILLING_SUNAT_RUC'),
        'user' => env('BILLING_SUNAT_USER'),
        'password' => env('BILLING_SUNAT_PASSWORD'),
        'endpoints' => [
            'homologation' => [
                'bill_service' => env('BILLING_SUNAT_HOMOLOGATION_BILL_ENDPOINT', 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService'),
                'summary_service' => env('BILLING_SUNAT_HOMOLOGATION_SUMMARY_ENDPOINT', 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService'),
                'status_service' => env('BILLING_SUNAT_HOMOLOGATION_STATUS_ENDPOINT', 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService'),
            ],
            'production' => [
                'bill_service' => env('BILLING_SUNAT_PRODUCTION_BILL_ENDPOINT', 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'),
                'summary_service' => env('BILLING_SUNAT_PRODUCTION_SUMMARY_ENDPOINT', 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'),
                'status_service' => env('BILLING_SUNAT_PRODUCTION_STATUS_ENDPOINT', 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'),
            ],
        ],
    ],
    'certificate' => [
        'path' => env('BILLING_CERTIFICATE_PATH'),
        'passphrase' => env('BILLING_CERTIFICATE_PASSPHRASE'),
    ],
    'storage' => [
        'disk_xml_cdr' => env('BILLING_STORAGE_DISK', 'secure-billing'),
        'xml_directory' => env('BILLING_STORAGE_XML_DIRECTORY', 'xml'),
        'cdr_directory' => env('BILLING_STORAGE_CDR_DIRECTORY', 'cdr'),
        'pdf_directory' => env('BILLING_STORAGE_PDF_DIRECTORY', 'pdf'),
        'encrypt_files' => env('BILLING_STORAGE_ENCRYPT', true),
    ],
    'queues' => [
        'sunat' => env('BILLING_QUEUE_SUNAT', 'sunat'),
    ],
];
