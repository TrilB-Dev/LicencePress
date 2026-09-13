<?php

return [

    "title" => "Acceptable Use Policy",

    "variables" => [
        "company_name"  => "",
        "product_name"  => "",
        "support_email" => "",
    ],

    "sections" => [

        "purpose" => "
            This Acceptable Use Policy outlines acceptable behaviour when using {{product_name}}.
            Its purpose is to protect system integrity, security, and service quality.
        ",

        "security" => "
            Users must not attempt to bypass authentication, licence verification, or security controls.
            Activities such as scanning, probing, or exploiting vulnerabilities are strictly prohibited.
        ",

        "resource_usage" => "
            Excessive or abusive consumption of system resources, including API calls or automated
            requests, may result in throttling or suspension.
        ",

        "harmful_activity" => "
            Users may not upload, transmit, or distribute harmful content, malware, or any material
            intended to disrupt systems or other users.
        ",

        "enforcement" => "
            Violations may result in suspension, termination, or legal action. Reports of abuse may be
            sent to {{support_email}}.
        ",
    ]
];
