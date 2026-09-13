<?php

$vars = [

    // Company details
    "{{company_logo_url}}"   => wp_get_attachment_url(123), // Replace 123 with your media ID
    "{{company_name}}"       => "Your Company Ltd",
    "{{company_address}}"    => "123 Example Street, Leigh",
    "{{company_country}}"    => "United Kingdom",
    "{{company_email}}"      => "support@example.com",
    "{{company_phone}}"      => "+44 1234 567890",

    // Customer details
    "{{customer_name}}"      => "John Doe",
    "{{customer_email}}"     => "john@example.com",

    // Invoice metadata
    "{{invoice_number}}"     => "INV-2026-001",
    "{{invoice_date}}"       => date("Y-m-d"),

    // Product details
    "{{product_name}}"       => "LicencePress Pro",
    "{{licence_key}}"        => "LIC-ABC123-XYZ789",

    // Pricing
    "{{currency}}"           => "GBP",
    "{{amount}}"             => "49.99",
    "{{vat_amount}}"         => "10.00",
    "{{total_amount}}"       => "59.99",

    // Payment
    "{{payment_method}}"     => "Credit Card",
];
