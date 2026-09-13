<?php

return [

    "title" => "Software Reseller Agreement",

    "variables" => [
        "company_name"    => "",
        "company_country" => "",
        "product_name"    => "",
        "commission_rate" => "",
        "support_email"   => "",
    ],

    "sections" => [

        "appointment" => "
            {{company_name}} appoints the reseller to market and sell licences for {{product_name}}
            within the agreed territory.
        ",

        "pricing" => "
            Resellers may sell licences at prices set by {{company_name}}. Commission is paid at
            {{commission_rate}} of net sales.
        ",

        "responsibilities" => "
            Resellers must represent {{product_name}} accurately and provide basic customer guidance.
            Technical support remains the responsibility of {{company_name}}.
        ",

        "branding" => "
            Resellers may use approved branding materials. Modifying or misrepresenting the brand
            is prohibited.
        ",

        "termination" => "
            Either party may terminate the agreement with written notice. Upon termination,
            reseller rights immediately cease.
        ",

        "governing_law" => "
            This Agreement is governed by the laws of {{company_country}}.
        ",
    ]
];
