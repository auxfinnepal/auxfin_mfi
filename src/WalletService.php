<?php

namespace Auxfin\Mfi;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Exception;

class WalletService
{
    private Client $client;
    private string $apiUrl;
    private MfiService $mfiService;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = config('mfi.api');
        $this->mfiService = new MfiService();
    }



    public function getWallet(int $user_id)
    {
        try {
            $token = $this->mfiService->getMfiToken();
            $response = $this->client->get(
                $this->apiUrl . '/api/wallet',
                [
                    'query' => [

                        "user_id" => $user_id,

                    ],
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );
            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function transaction($user_id, $wallet_id, $mfi_id, $amount, $transaction_type, $to_user_id, $account, $remark, $pin = null)
    {
        try {
            $token = $this->mfiService->getMfiToken();

            $connection = null;

            if ($mfi_id) {
                $connection = $this->mfiService->checkConnection($mfi_id, 'transaction');
            }

            $response = $this->client->post(
                $this->apiUrl . '/api/wallet/transfer',
                [
                    'form_params' => [
                        "user_id" => $user_id,
                        "wallet_id" => $wallet_id ?? null,
                        "amount" => $amount,
                        "mfi_id" => $mfi_id,
                        "class" => $connection->class ?? null,
                        "transaction_type" => $transaction_type,
                        "to_user_id" => $to_user_id ?? null,
                        "account_number" => $account ?? null,
                        "remark" => $remark ?? null,
                        "pin" => $pin,
                    ],
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );
            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Handle MFI to Mobile Money transaction
     * This is a separate method that can be called directly
     * 
     * @param array $data Transaction data containing mfi_id, user_id, amount, pin, transaction_type, remark
     * @return object Transaction object with response details
     * @throws Exception
     */
    public function mfiToMobileMoney(array $data)
    {
        $this->validateTransferData($data, ['mfi_id', 'user_id', 'amount', 'pin']);

        return DB::transaction(function () use ($data) {
            // Note: Assuming Mfi model exists in App\Models namespace
            $mfiModel = config('mfi.mfi_model', '\App\Models\Mfi');
            $mfi = $mfiModel::find($data['mfi_id']);

            if (!$mfi) {
                throw new Exception("mfi_not_found");
            }

            $transactionCode = $this->generateTransactionCode($data['transaction_type']);

            $transaction = $this->createTransaction([
                'amount' => $data['amount'],
                'user_id' => $data['user_id'],
                'transaction_type' => $data['transaction_type'],
                'transaction_code' => $transactionCode,
                'mfi_id' => $data['mfi_id'],
                'remark' => $data['remark'] ?? 'mfi_to_mobile_money'
            ]);

            // Note: Assuming MfiConnect class exists
            $mfiConnectClass = config('mfi.mfi_connect_class', '\App\Services\MfiConnect');
            $mfiConnect = new $mfiConnectClass();
            $integration = $mfiConnect->resolveIntegration($data['mfi_id']);

            if (!method_exists($integration, 'depositIn')) {
                $integrationClass = is_object($integration) ? get_class($integration) : 'integration';
                throw new Exception("Method 'depositIn' does not exist in {$integrationClass}", 404);
            }

            $result = $integration->depositIn($data['pin'], $data['amount']);

            // Update transaction status based on MFI response and save to database
            $updateData = [];
            if (isset($result['status'])) {
                $updateData['status'] = $result['status'];
            }

            if (isset($result['status']) && ($result['status'] === 'success' || $result['status'] === 'completed')) {
                $updateData['completed_at'] = now();
            }

            if (!empty($updateData)) {
                $transaction->update($updateData);
            }

            // Add MFI response details to transaction object
            $transaction->setAttribute('transaction_id', $result['transaction_id'] ?? null);
            $transaction->setAttribute('sp_transaction_id', $result['sp_transaction_id'] ?? null);
            $transaction->setAttribute('message', $result['message'] ?? null);
            $transaction->setAttribute('dmark_response', $result['dmark_response'] ?? null);

            return $transaction;
        });
    }

    /**
     * Validate required fields in transfer data
     * 
     * @param array $data Data to validate
     * @param array $requiredFields List of required field names
     * @throws Exception
     */
    private function validateTransferData(array $data, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }
    }

    /**
     * Generate a unique transaction code
     * 
     * @param string $transactionType Type of transaction
     * @return string Transaction code
     */
    private function generateTransactionCode(string $transactionType): string
    {
        $prefix = strtoupper(substr($transactionType, 0, 3));
        $timestamp = time();
        $random = strtoupper(substr(md5(uniqid()), 0, 6));

        return "{$prefix}-{$timestamp}-{$random}";
    }

    /**
     * Create a new transaction record
     * 
     * @param array $data Transaction data
     * @return object Transaction model instance
     */
    private function createTransaction(array $data)
    {
        // Note: Assuming Transaction model exists in App\Models namespace
        $transactionModel = config('mfi.transaction_model', '\App\Models\Transaction');

        return $transactionModel::create($data);
    }

    public function getWalletTransactions($from_date, $to_date, $user_id, $wallet_id)
    {
        try {
            $token = $this->mfiService->getMfiToken();
            $response = $this->client->get(
                $this->apiUrl . '/api/wallet/transactions',
                [
                    'query' => [
                        "from_date" => $from_date,
                        "to_date" => $to_date,
                        "wallet_id" => $wallet_id,
                        "user_id" => $user_id,

                    ],
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );
            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
