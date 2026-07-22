<?php
// Classes/PaystackService.php

class PaystackService
{
    private string $secretKey;
    private string $baseUrl = 'https://api.paystack.co';

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    private function request(string $method, string $endpoint, array $data = []): array
    {
        $curl = curl_init();

        $options = [
            CURLOPT_URL => $this->baseUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->secretKey}",
                "Content-Type: application/json",
            ],
        ];

        if (!empty($data) && $method !== 'GET') {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            throw new Exception("Paystack cURL error: {$err}");
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid Paystack response: {$response}");
        }

        return $decoded;
    }

    // Starts a transaction, returns an authorization_url to redirect the user to
    public function initializeTransaction(string $email, int $amountInSubunit, string $reference, string $callbackUrl, array $metadata = []): array
    {

        $amount = $amount * 100;
        return $this->request('POST', '/transaction/initialize', [
            'email' => $email,
            'amount' => $amountInSubunit,
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'metadata' => $metadata,
        ]);
    }

    // The source of truth — never trust the redirect query string alone
    public function verifyTransaction(string $reference): array
    {
        return $this->request('GET', '/transaction/verify/' . rawurlencode($reference));
    }
}