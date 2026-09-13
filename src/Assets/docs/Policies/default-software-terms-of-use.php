<?php

return [

    "title" => "Terms of Use",

    "variables" => [
        "company_name"    => "",
        "company_country" => "",
        "product_name"    => "",
        "support_email"   => "",
    ],

    "sections" => [

        "overview" => "
            These Terms of Use apply to all users of {{product_name}} and related services provided
            by {{company_name}}.
        ",

        "user_responsibilities" => "
            Users must ensure that their account, licence key, and access credentials remain secure.
            Users are responsible for all activity performed under their licence.
        ",

        "prohibited_actions" => "
            Users may not engage in unlawful activity, attempt to bypass security controls,
            interfere with service availability, or misuse the software in any way.
        ",

        "content_and_data" => "
            Users retain ownership of their data. {{company_name}} may process data as required
            to provide services, subject to applicable privacy laws.
        ",

        "availability" => "
            {{company_name}} does not guarantee uninterrupted access. Availability commitments
            are defined in the Service Level Agreement.
        ",

        "contact" => "
            For questions or concerns, contact {{support_email}}.
        ",

        "governing_law" => "
            These Terms are governed by the laws of {{company_country}}.
        ",
    ]
];
