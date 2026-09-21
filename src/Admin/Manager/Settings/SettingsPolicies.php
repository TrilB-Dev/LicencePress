<?php
/**
 * Class SettingsPolicies
 *
 * Manages the policies settings within the LicencePress admin interface.
 * 
 * @package LicencePress
 * @subpackage Admin/Manager/Settings
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class SettingsPolicies {
    function variables() {
        $vars = [
            // Company details
            "{{company_logo_url}}"   => wp_get_attachment_url(123),
            "{{company_name}}"       => "Your Company Ltd",
            "{{company_address}}"    => "123 Example Street, Leigh",
            "{{company_country}}"    => "United Kingdom",
            "{{company_email}}"      => "support@example.com",
            "{{company_phone}}"      => "+44 1234 567890",
        ];

        return $vars;
    }
    function acceptable_use_policy(){
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
    }
    function software_developer_agreement(){
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
    }
    function software_license_agreement(){
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
    }
    function software_renewal_policy() {
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
    }
    function software_reseller_agreement(): array{
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
    }
    function software_service_level_agreement(){
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
    }
    function software_terms_of_use(){
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
    }
}