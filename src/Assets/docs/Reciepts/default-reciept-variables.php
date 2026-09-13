<?php

$vars = [

    // Company details
    "{{company_logo_url}}"   => wp_get_attachment_url(123),
    "{{company_name}}"       => "Your Company Ltd",
    "{{company_address}}"    => "123 Example Street, Leigh",
    "{{company_country}}"    => "United Kingdom",
    "{{company_email}}"      => "support@example.com",
    "{{company_phone}}"      => "+44 1234 567890",

    // Customer details
    "{{customer_name}}"      => "John Doe",
    "{{customer_email}}"     => "john@example.com",

    // Receipt metadata
    "{{receipt_number}}"     => "RCT-2026-001",
    "{{receipt_date}}"       => date("Y-m-d"),

    // Product details
    "{{product_name}}"       => "LicencePress Pro",
    "{{licence_key}}"        => "LIC-ABC123-XYZ789",

    // Payment details
    "{{currency}}"           => "GBP",
    "{{amount_paid}}"        => "59.99",
    "{{payment_method}}"     => "Credit Card",
    "{{transaction_id}}"     => "TXN-987654321",
];
