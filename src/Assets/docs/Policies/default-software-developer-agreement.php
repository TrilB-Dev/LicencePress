<?php

return [

    "title" => "Software Developer Agreement",

    "variables" => [
        "company_name"    => "",
        "developer_name"  => "",
        "company_country" => "",
        "project_name"    => "",
        "support_email"   => "",
    ],

    "sections" => [

        "engagement" => "
            {{company_name}} engages {{developer_name}} to perform development work for {{project_name}}.
        ",

        "deliverables" => "
            The developer will provide code, documentation, and related materials as agreed.
        ",

        "ip_ownership" => "
            All intellectual property created under this Agreement is assigned to {{company_name}}
            unless otherwise stated in writing.
        ",

        "confidentiality" => "
            The developer must keep all project information confidential and may not disclose
            proprietary details without permission.
        ",

        "payment" => "
            Payment terms will be agreed separately. Work must meet quality and security standards
            defined by {{company_name}}.
        ",

        "termination" => "
            Either party may terminate the Agreement with written notice. Upon termination,
            all work completed to date must be delivered to {{company_name}}.
        ",

        "governing_law" => "
            This Agreement is governed by the laws of {{company_country}}.
        ",
    ]
];
