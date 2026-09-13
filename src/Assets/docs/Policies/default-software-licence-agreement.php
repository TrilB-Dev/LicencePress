<?php

return [

    "title" => "Software Licence Agreement",

    "variables" => [
        "company_name"    => "",
        "company_country" => "",
        "product_name"    => "",
        "support_email"   => "",
        "currency"        => "",
        "licence_term"    => "",
        "licence_type"    => "",
    ],

    "sections" => [

        "introduction" => "
            This Software Licence Agreement governs the use of {{product_name}} provided by {{company_name}}.
            By activating or using the software, the user agrees to the terms set out in this Agreement.
        ",

        "grant_of_licence" => "
            {{company_name}} grants the user a {{licence_type}} licence to install and use {{product_name}}
            for the duration of {{licence_term}}. This licence is non-exclusive and non-transferable.
        ",

        "restrictions" => "
            Users may not copy, modify, distribute, reverse engineer, or attempt to bypass licence
            verification mechanisms. Licence keys may not be shared or resold without written permission.
        ",

        "updates" => "
            {{company_name}} may provide updates, patches, or enhancements during the licence term.
            Access to updates may require an active licence.
        ",

        "fees" => "
            All licence fees are payable in {{currency}}. Prices may change, and renewal fees may differ
            from initial purchase fees.
        ",

        "support" => "
            Support is available via {{support_email}}. Support levels and response times are defined
            separately in the Service Level Agreement.
        ",

        "termination" => "
            {{company_name}} may suspend or terminate the licence if the user breaches this Agreement,
            fails to pay required fees, or engages in prohibited activities.
        ",

        "liability" => "
            {{company_name}} is not liable for indirect or consequential damages. Liability is limited
            to the total amount paid for the licence.
        ",

        "governing_law" => "
            This Agreement is governed by the laws of {{company_country}}.
        ",
    ]
];
