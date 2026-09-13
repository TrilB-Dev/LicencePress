<?php

/**
 * Replace {{variables}} in template
 */
function lp_receipt_render($template, $vars) {
    return str_replace(array_keys($vars), array_values($vars), $template);
}


/**
 * STANDARD HTML/CSS RECEIPT
 */
function lp_receipt_standard($vars) {

$template = <<<HTML
<style>
    .lp-doc {
        max-width: 800px;
        margin: 40px auto;
        padding: 24px;
        border: 1px solid #ddd;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        font-size: 14px;
        color: #222;
        background: #fff;
    }
    .lp-doc-logo {
        width: 180px;
        height: auto;
        margin-bottom: 20px;
    }
    .lp-doc-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 24px;
    }
    .lp-doc-title {
        font-size: 22px;
        font-weight: 600;
    }
    .lp-doc-meta {
        text-align: right;
        font-size: 13px;
        color: #555;
    }
    .lp-doc-section-title {
        font-weight: 600;
        margin-top: 16px;
        margin-bottom: 4px;
        font-size: 14px;
    }
    .lp-doc-block {
        margin-bottom: 12px;
        white-space: pre-line;
    }
    .lp-doc-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
        margin-bottom: 12px;
    }
    .lp-doc-table th,
    .lp-doc-table td {
        border: 1px solid #ddd;
        padding: 8px;
        font-size: 13px;
    }
    .lp-doc-table th {
        background: #f7f7f7;
        text-align: left;
    }
    .lp-doc-footer {
        margin-top: 24px;
        font-size: 12px;
        color: #666;
    }
</style>

<div class="lp-doc lp-receipt">

    <img src="{{company_logo_url}}" alt="Company Logo" class="lp-doc-logo">

    <div class="lp-doc-header">
        <div>
            <div class="lp-doc-title">Receipt</div>
            <div class="lp-doc-block">
                {{company_name}}<br>
                {{company_address}}<br>
                {{company_country}}<br>
                Email: {{company_email}}<br>
                Phone: {{company_phone}}
            </div>
        </div>
        <div class="lp-doc-meta">
            Receipt No: {{receipt_number}}<br>
            Date: {{receipt_date}}
        </div>
    </div>

    <div class="lp-doc-section">
        <div class="lp-doc-section-title">Customer</div>
        <div class="lp-doc-block">
            {{customer_name}}<br>
            {{customer_email}}
        </div>
    </div>

    <div class="lp-doc-section">
        <div class="lp-doc-section-title">Product</div>
        <table class="lp-doc-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Licence Key</th>
                    <th>Amount Paid</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{product_name}}</td>
                    <td>{{licence_key}}</td>
                    <td>{{currency}} {{amount_paid}}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="lp-doc-section">
        <div class="lp-doc-section-title">Payment Details</div>
        <div class="lp-doc-block">
            Payment Method: {{payment_method}}<br>
            Transaction ID: {{transaction_id}}
        </div>
    </div>

    <div class="lp-doc-footer">
        This receipt confirms payment for the above product. For support or billing enquiries,
        please contact {{company_email}}.
    </div>
</div>
HTML;

return lp_receipt_render($template, $vars);
}



/**
 * PDF-FRIENDLY RECEIPT (tables only)
 */
function lp_receipt_pdf($vars) {

$template = <<<HTML
<div style="width: 100%; font-family: Arial, sans-serif; font-size: 13px; color: #222;">

    <div style="text-align: left; margin-bottom: 20px;">
        <img src="{{company_logo_url}}" alt="Company Logo" style="width: 160px; height: auto;">
    </div>

    <table width="100%" cellpadding="4" cellspacing="0" border="0">
        <tr>
            <td valign="top" width="60%">
                <h2 style="margin: 0 0 10px 0;">Receipt</h2>
                {{company_name}}<br>
                {{company_address}}<br>
                {{company_country}}<br>
                Email: {{company_email}}<br>
                Phone: {{company_phone}}
            </td>
            <td valign="top" width="40%" align="right">
                <strong>Receipt No:</strong> {{receipt_number}}<br>
                <strong>Date:</strong> {{receipt_date}}
            </td>
        </tr>
    </table>

    <h3 style="margin-top: 20px;">Customer</h3>
    {{customer_name}}<br>
    {{customer_email}}

    <h3 style="margin-top: 20px;">Product</h3>

    <table width="100%" cellpadding="6" cellspacing="0" border="1" style="border-collapse: collapse;">
        <tr style="background: #f0f0f0;">
            <th align="left">Product</th>
            <th align="left">Licence Key</th>
            <th align="left">Amount Paid</th>
        </tr>
        <tr>
            <td>{{product_name}}</td>
            <td>{{licence_key}}</td>
            <td>{{currency}} {{amount_paid}}</td>
        </tr>
    </table>

    <h3 style="margin-top: 20px;">Payment Details</h3>
    Payment Method: {{payment_method}}<br>
    Transaction ID: {{transaction_id}}

    <p style="margin-top: 20px; font-size: 12px; color: #555;">
        Thank you for your payment. For support, contact {{company_email}}.
    </p>
</div>
HTML;

return lp_receipt_render($template, $vars);
}



/**
 * COMPACT EMAIL RECEIPT
 */
function lp_receipt_email($vars) {

$template = <<<HTML
<table width="100%" cellpadding="6" cellspacing="0" style="font-family: Arial, sans-serif; font-size: 13px; color: #222;">
    <tr>
        <td>
            <img src="{{company_logo_url}}" alt="Logo" style="width: 140px; height: auto; margin-bottom: 10px;">
        </td>
    </tr>

    <tr>
        <td>
            <strong>Receipt</strong><br>
            Receipt No: {{receipt_number}}<br>
            Date: {{receipt_date}}<br><br>

            <strong>{{company_name}}</strong><br>
            {{company_address}}<br>
            {{company_country}}<br>
            Email: {{company_email}}<br>
            Phone: {{company_phone}}<br><br>

            <strong>Customer</strong><br>
            {{customer_name}}<br>
            {{customer_email}}<br><br>

            <strong>Product</strong><br>
            {{product_name}}<br>
            Licence Key: {{licence_key}}<br>
            Amount Paid: {{currency}} {{amount_paid}}<br><br>

            <strong>Payment Method:</strong> {{payment_method}}<br>
            <strong>Transaction ID:</strong> {{transaction_id}}<br><br>

            <em>Thank you for your payment.</em>
        </td>
    </tr>
</table>
HTML;

return lp_receipt_render($template, $vars);
}