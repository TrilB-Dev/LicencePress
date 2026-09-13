<?php

return [

    "title" => "Service Level Agreement (SLA)",

    "variables" => [
        "company_name"      => "",
        "product_name"      => "",
        "uptime_percentage" => "",
        "support_email"     => "",
        "response_time"     => "",
    ],

    "sections" => [

        "overview" => "
            This SLA defines service availability and support commitments for {{product_name}}
            provided by {{company_name}}.
        ",

        "availability" => "
            {{company_name}} aims to maintain an uptime of {{uptime_percentage}} each calendar month.
            Scheduled maintenance may occur with prior notice.
        ",

        "support" => "
            Support requests may be submitted to {{support_email}}. Typical response time is
            {{response_time}}, excluding weekends and holidays.
        ",

        "maintenance" => "
            {{company_name}} may perform updates or maintenance to ensure service quality.
            Downtime during maintenance is excluded from uptime calculations.
        ",

        "remedies" => "
            If uptime falls below the stated target, {{company_name}} may offer service credits
            at its discretion.
        ",
    ]
];
