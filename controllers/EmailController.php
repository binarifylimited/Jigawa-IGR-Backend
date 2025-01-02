<?php

class EmailController
{
    private $apiUrl = "https://api.brevo.com/v3/smtp/email";
    private $apiKey = "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";

    public function invoiceEmail($email, $first_name, $due_date, $invoice_number, $revenue_head)
    {
        // Create an array with the email data
        $emailData = [
            "to" => [
                [
                    "email" => $email,
                    "name" => $first_name,
                ],
            ],
            "templateId" => 15,
            "params" => [
                "Fname" => $first_name,
                "due" => $due_date,
                "InvoiceN" => $invoice_number,
                "RevenueHead" => $revenue_head,
                "URL" => "https://plateauigr.com/viewinvoice.html?load=true&invnumber=$invoice_number"
            ],
            "headers" => [
                "X-Mailin-custom" => "custom_header_1:custom_value_1|custom_header_2:custom_value_2|custom_header_3:custom_value_3",
                "charset" => "iso-8859-1",
            ],
        ];

        return $this->sendEmail($emailData);
    }

    public function userVerificationEmail($email, $name, $Lname, $verification)
    {
        // Create an array with the email data
        $emailData = [
            "to" => [
                [
                    "email" => $email,
                    "name" => $name,
                ],
            ],
            "templateId" => 2,
            "params" => [
                "Fname" => $name,
                "Lname" => $Lname,
                "Verification" => $verification
            ],
            "headers" => [
                "X-Mailin-custom" => "custom_header_1:custom_value_1|custom_header_2:custom_value_2|custom_header_3:custom_value_3",
                "charset" => "iso-8859-1",
            ],
        ];

        return $this->sendEmail($emailData);
    }

    public function adminCreationEmail($email, $fullname, $dashboard_access, $analytics_access, $mda_access, $reports_access, $tax_payer_access, $users_access, $cms_access, $support, $enumeration, $audit_trail, $payee_access, $tax_manager, $last_id, $verification)
    {
        // Create an array with the email data
        $emailData = [
            "to" => [
                [
                    "email" => $email,
                    "name" => $fullname,
                ],
            ],
            "templateId" => 14,
            "params" => [
                "Fname" => $fullname,
                "email" => $email,
                "dashboard_access" => $dashboard_access,
                "analytics_access" => $analytics_access,
                "ada_access" => $mda_access,
                "reports_access" => $reports_access,
                "tax_payer_access" => $tax_payer_access,
                "users_access" => $users_access,
                "cms_access" => $cms_access,
                "support" => $support,
                "enum_access" => $enumeration,
                "audit_access" => $audit_trail,
                "payee_access" => $payee_access,
                "tax_manager" => $tax_manager,
                "URL" => "https://plateauigr.com/createpassword.html?id=$last_id&verification=$verification"
            ],
            "headers" => [
                "X-Mailin-custom" => "custom_header_1:custom_value_1|custom_header_2:custom_value_2|custom_header_3:custom_value_3",
                "charset" => "iso-8859-1",
            ],
        ];

        return $this->sendEmail($emailData);
    }

    public function mdaCreation($email, $fullname, $last_id)
    {
        // Create an array with the email data
        $emailData = [
            "to" => [
                [
                    "email" => $email,
                    "name" => $fullname,
                ],
            ],
            "templateId" => 9,
            "params" => [
                "Fname" => $fullname,
                "email" => $email,
                "URL" => "https://plateauigr.com/mdapassword.html?id=$last_id"
            ],
            "headers" => [
                "X-Mailin-custom" => "custom_header_1:custom_value_1|custom_header_2:custom_value_2|custom_header_3:custom_value_3",
                "charset" => "iso-8859-1",
            ],
        ];

        return $this->sendEmail($emailData);
    }

    public function enumCreation($email, $name, $password, $id, $verification)
    {
        // Create an array with the email data
        $emailData = [
            "to" => [
                [
                    "email" => $email,
                    "name" => $name,
                ],
            ],
            "templateId" => 12,
            "params" => [
                "Fname" => $name,
                "email" => $email,
                "password" => $password,
                "URL" => "https://plateauigr.com/emailverification2.html?id=$id&verification=$verification"
            ],
            "headers" => [
                "X-Mailin-custom" => "custom_header_1:custom_value_1|custom_header_2:custom_value_2|custom_header_3:custom_value_3",
                "charset" => "iso-8859-1",
            ],
        ];

        return $this->sendEmail($emailData);
    }

    private function sendEmail($emailData)
    {
        // Convert the email data to a JSON string
        $jsonData = json_encode($emailData);

        // Initialize cURL session
        $ch = curl_init($this->apiUrl);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'api-key: ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        // Execute cURL request
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            // Handle cURL error
            $errorMessage = curl_error($ch);
            curl_close($ch);
            return [
                'success' => false,
                'message' => "cURL Error: $errorMessage",
            ];
        }

        curl_close($ch);

        // Decode the response
        $responseData = json_decode($response, true);

        // Return response
        return [
            'success' => true,
            'data' => $responseData,
        ];
    }
}

?>
