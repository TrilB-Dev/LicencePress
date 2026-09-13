<?php

return [

    "title" => "Licence Renewal Policy",

    "variables" => [
        "company_name"    => "",
        "company_country" => "",
        "product_name"    => "",
        "support_email"   => "",
        "currency"        => "",
        "renewal_term"    => "",
        "grace_period"    => "",
    ],

    "sections" => [

        "purpose" => "
            This Licence Renewal Policy explains how renewals for {{product_name}} are managed by
            {{company_name}}. It outlines renewal timelines, payment requirements, grace periods,
            and the consequences of non-renewal. This policy applies to all customers regardless of
            location, unless local consumer protection laws require otherwise.
        ",

        "renewal_eligibility" => "
            A licence is eligible for renewal if it is currently active or within the defined
            {{grace_period}} grace period following expiry. Licences that remain expired beyond the
            grace period may require a new purchase instead of a renewal.
        ",

        "renewal_term" => "
            Renewals extend the licence for the standard term of {{renewal_term}}. Renewal duration
            is consistent across all regions unless otherwise specified by {{company_name}}.
        ",

        "renewal_window" => "
            Customers may renew their licence at any time up to 60 days before the expiry date and
            within {{grace_period}} after expiry. During the grace period, the licence remains active
            unless restricted by product-specific rules.
        ",

        "grace_period" => "
            The grace period allows customers to renew without losing activation status. During this
            time, updates and support remain available. New activations may be limited depending on
            vendor settings. Once the grace period ends, the licence becomes fully expired.
        ",

        "renewal_pricing" => "
            Renewal fees are charged in {{currency}}. Pricing may vary by region or promotional
            offers. {{company_name}} may provide early-renewal discounts or apply lapsed-renewal
            fees at its discretion.
        ",

        "payment_and_activation" => "
            Upon successful payment, the licence term is extended, and the new expiry date is applied.
            Activation limits and entitlements reset according to the licence plan. A renewal
            confirmation email will be sent to the customer from {{support_email}}.
        ",

        "failed_or_missed_renewals" => "
            If renewal payment fails or the grace period expires, the licence becomes inactive.
            Access to updates, support, and new activations may be restricted. Customers may be
            required to purchase a new licence depending on {{company_name}} policy.
        ",

        "auto_renewal" => "
            If auto-renewal is enabled, {{company_name}} will attempt renewal on the expiry date.
            Customers will receive advance notice of upcoming charges. Failed auto-renewals may
            trigger retry attempts and notifications.
        ",

        "policy_changes" => "
            {{company_name}} may update this renewal policy at any time. Changes apply to future
            renewals and do not affect licences already renewed.
        ",

        "governing_law" => "
            This policy is governed by the laws of {{company_country}}, unless local consumer
            protection laws require otherwise. For questions or support, contact {{support_email}}.
        ",
    ]
];
