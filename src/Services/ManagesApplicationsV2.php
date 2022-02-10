<?php

namespace Rootsoft\Algorand\Services;

use Brick\Math\BigInteger;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Application;
use Rootsoft\Algorand\Models\Applications\ApplicationLogsResult;
use Rootsoft\Algorand\Models\Applications\StateSchema;
use Rootsoft\Algorand\Models\Applications\TEALProgram;
use Rootsoft\Algorand\Models\Teals\DryrunRequest;
use Rootsoft\Algorand\Models\Teals\DryrunResponse;
use Rootsoft\Algorand\Models\Teals\TealCompilationResult;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Utils\Encoder;

trait ManagesApplicationsV2
{
    use ManagesNodesV2;
    use ManagesTransactionsV2;
    use ManagesTransactionParamsV2;

    /**
     * Gets application information.
     *
     * Given a application id, it returns application information including creator, approval and clear programs,
     * global and local schemas, and global state.
     *
     * @param int $applicationId
     * @return \Rootsoft\Algorand\Models\Application
     * @throws AlgorandException
     */
    public function getApplicationById(int $applicationId)
    {
        $response = $this->get($this->algodClient, "/v2/applications/$applicationId");

        $application = new Application();
        $this->jsonMapper->mapObject($response, $application);

        return $application;
    }

    /**
     * Lookup application logs by a given application id.
     *
     * @param int $applicationId
     * @return \Rootsoft\Algorand\Models\Applications\ApplicationLogsResult
     * @throws AlgorandException
     */
    public function getApplicationLogsById(int $applicationId): ApplicationLogsResult
    {
        $response = $this->get($this->algodClient, "/v2/applications/$applicationId/logs");

        $result = new ApplicationLogsResult();
        $this->jsonMapper->mapObject($response, $result);

        return $result;
    }

    /**
     * Executes TEAL program(s) in context and returns debugging information about the execution.
     * This endpoint is only enabled when a node's configuration file sets EnableDeveloperAPI to true.
     *
     * @param DryrunRequest $request
     * @return DryrunResponse
     * @throws AlgorandException
     */
    public function dryrun(DryrunRequest $request)
    {
        $data = Encoder::getInstance()->encodeMessagePack($request->toMessagePack());
        $response = $this->post($this->algodClient, '/v2/teal/dryrun', [], ['body' => $data], ['Content-Type' => 'application/x-binary']);

        $result = new DryrunResponse();
        $this->jsonMapper->mapObject($response, $result);

        return $result;
    }

    /**
     * Compile TEAL source code to binary, produce its hash.
     *
     * Given TEAL source code in plain text,
     * return base64 encoded program bytes and base32 SHA512_256 hash of program bytes (Address style).
     *
     * This endpoint is only enabled when a node's configuration file sets EnableDeveloperAPI to true.
     *
     * @param string $teal
     * @return \Rootsoft\Algorand\Models\Teals\TealCompilationResult
     * @throws AlgorandException
     */
    public function compileTEAL(string $teal)
    {
        $response = $this->post($this->algodClient, '/v2/teal/compile', [], ['body' => $teal], ['Content-Type' => 'application/x-binary']);

        $result = new TealCompilationResult();
        $this->jsonMapper->mapObject($response, $result);

        return $result;
    }

    /**
     * Create a new Algorand Stateful Smart Contract.
     * This is a helper function to create a new application on the blockchain.
     *
     * @param Account $account
     * @param TEALProgram $approvalProgram
     * @param TEALProgram $clearProgram
     * @param StateSchema $globalStateSchema
     * @param StateSchema $localStateSchema
     * @param bool $waitForConfirmation
     * @param int $timeout
     *
     * @return \Rootsoft\Algorand\Models\Transactions\PendingTransaction|string
     *
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function createApplication(
        Account     $account,
        TEALProgram $approvalProgram,
        TEALProgram $clearProgram,
        StateSchema $globalStateSchema,
        StateSchema $localStateSchema,
        bool        $waitForConfirmation = false,
        int         $timeout = 5
    )
    {
        $address = $account->getAddress();

        // Get the suggested transaction params
        $params = $this->getSuggestedTransactionParams();

        // Create a new asset
        $transaction = TransactionBuilder::applicationCreate()
            ->sender($address)
            ->approvalProgram($approvalProgram)
            ->clearStateProgram($clearProgram)
            ->globalStateSchema($globalStateSchema)
            ->localStateSchema($localStateSchema)
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        return $this->sendTransaction($signedTransaction, $waitForConfirmation, $timeout);
    }

    /**
     * Opt in to the smart contract.
     * This is a helper function to opt in to an existing smart contract.
     *
     * Before any account, including the creator of the smart contract, can begin to make Application Transaction calls
     * that use local state, it must first opt into the smart contract.
     *
     * This prevents accounts from being spammed with smart contracts.
     * To opt in, an ApplicationCall transaction of type OptIn needs to be signed and submitted by the account desiring
     * to opt into the smart contract.
     *
     * @param Account $account
     * @param BigInteger $applicationId
     * @param bool $waitForConfirmation
     * @param int $timeout
     *
     * @return \Rootsoft\Algorand\Models\Transactions\PendingTransaction|string
     *
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function optIn(
        Account    $account,
        BigInteger $applicationId,
        bool       $waitForConfirmation = false,
        int        $timeout = 5
    )
    {
        $address = $account->getAddress();

        // Get the suggested transaction params
        $params = $this->getSuggestedTransactionParams();

        // Create a new asset
        $transaction = TransactionBuilder::applicationOptIn()
            ->sender($address)
            ->applicationId($applicationId)
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        return $this->sendTransaction($signedTransaction, $waitForConfirmation, $timeout);
    }

    /**
     * Call a Stateful Smart Contract.
     * This is a helper function to easily call a stateful smart contract and pass arguments to it.
     *
     * Arguments can be passed to any of the supported application transaction calls, including create.
     * The number and type can also be different for any subsequent calls to the stateful smart contract.
     *
     * Once an account has opted into a stateful smart contract it can begin to make calls to the contract.
     * These calls will be in the form of ApplicationCall transactions that can be submitted with goal or the SDK.
     *
     * Depending on the individual type of transaction as described in The Lifecycle of a Stateful Smart Contract,
     * either the ApprovalProgram or the ClearStateProgram will be called.
     *
     * Generally, individual calls will supply application arguments.
     *
     * @param Account $account
     * @param BigInteger $applicationId
     * @param array $arguments
     * @param bool $waitForConfirmation
     * @param int $timeout
     *
     * @return \Rootsoft\Algorand\Models\Transactions\PendingTransaction|string
     *
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function call(
        Account    $account,
        BigInteger $applicationId,
        array      $arguments,
        bool       $waitForConfirmation = false,
        int        $timeout = 5
    )
    {
        $address = $account->getAddress();

        // Get the suggested transaction params
        $params = $this->getSuggestedTransactionParams();

        // Create a new asset
        $transaction = TransactionBuilder::applicationCall()
            ->sender($address)
            ->applicationId($applicationId)
            ->arguments($arguments)
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        return $this->sendTransaction($signedTransaction, $waitForConfirmation, $timeout);
    }

    /**
     * Updates an existing Stateful Smart Contract.
     * This is a helper function to easily update a stateful smart contract.
     *
     * A stateful smart contract’s programs can be updated at any time.
     * This is done by an ApplicationCall transaction type of UpdateApplication.
     * This operation can be done with goal or the SDKs and requires passing the new programs and specifying the
     * application ID.
     *
     * The one caveat to this operation is that global or local state requirements for the smart contract can never be
     * updated.
     *
     * @param Account $account
     * @param BigInteger $applicationId
     * @param TEALProgram $approvalProgram
     * @param TEALProgram $clearStateProgram
     * @param array|null $arguments
     * @param bool $waitForConfirmation
     * @param int $timeout
     *
     * @return \Rootsoft\Algorand\Models\Transactions\PendingTransaction|string
     *
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function update(
        Account     $account,
        BigInteger  $applicationId,
        TEALProgram $approvalProgram,
        TEALProgram $clearStateProgram,
        ?array      $arguments = null,
        bool        $waitForConfirmation = false,
        int         $timeout = 5
    )
    {
        $address = $account->getAddress();

        // Get the suggested transaction params
        $params = $this->getSuggestedTransactionParams();

        // Create a new asset
        $transaction = TransactionBuilder::applicationUpdate()
            ->sender($address)
            ->applicationId($applicationId)
            ->approvalProgram($approvalProgram)
            ->clearStateProgram($clearStateProgram)
            ->arguments($arguments)
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        return $this->sendTransaction($signedTransaction, $waitForConfirmation, $timeout);
    }

    /**
     * Discontinue use of the application by sending a close out transaction.
     * This will remove the local state for this application from the user's account.
     *
     * Accounts use this transaction to close out their participation in the contract.
     * This call can fail based on the TEAL logic, preventing the account from removing the contract from its balance
     * record.
     *
     * @param Account $account
     * @param BigInteger $applicationId
     * @param bool $waitForConfirmation
     * @param int $timeout
     *
     * @return \Rootsoft\Algorand\Models\Transactions\PendingTransaction|string
     *
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function closeOut(
        Account    $account,
        BigInteger $applicationId,
        bool       $waitForConfirmation = false,
        int        $timeout = 5
    )
    {
        $address = $account->getAddress();

        // Get the suggested transaction params
        $params = $this->getSuggestedTransactionParams();

        // Create a new asset
        $transaction = TransactionBuilder::applicationCloseOut()
            ->sender($address)
            ->applicationId($applicationId)
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        return $this->sendTransaction($signedTransaction, $waitForConfirmation, $timeout);
    }

    /**
     * Deletes an existing Stateful Smart Contract.
     * This is a helper function to easily delete a stateful smart contract.
     *
     * To delete a smart contract, an ApplicationCall transaction of type DeleteApplication must be submitted to
     * the blockchain.
     * The ApprovalProgram handles this transaction type and if the call returns true the application will be deleted.
     * The approval program defines the creator as the only account able to delete the application.
     * This removes the global state, but does not impact any user's local state.
     *
     * @param Account $account
     * @param BigInteger $applicationId
     * @param bool $waitForConfirmation
     * @param int $timeout
     *
     * @return \Rootsoft\Algorand\Models\Transactions\PendingTransaction|string
     *
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function deleteApplication(
        Account    $account,
        BigInteger $applicationId,
        bool       $waitForConfirmation = false,
        int        $timeout = 5
    )
    {
        $address = $account->getAddress();

        // Get the suggested transaction params
        $params = $this->getSuggestedTransactionParams();

        // Create a new asset
        $transaction = TransactionBuilder::applicationDelete()
            ->sender($address)
            ->applicationId($applicationId)
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        return $this->sendTransaction($signedTransaction, $waitForConfirmation, $timeout);
    }

    /**
     * Clear the local state for an application at any time, even if the application was deleted by the creator.
     *
     * Similar to CloseOut, but the transaction will always clear a contract from the account’s balance record whether
     * the program succeeds or fails.
     *
     * @param Account $account
     * @param BigInteger $applicationId
     * @param bool $waitForConfirmation
     * @param int $timeout
     *
     * @return \Rootsoft\Algorand\Models\Transactions\PendingTransaction|string
     *
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function clearState(
        Account    $account,
        BigInteger $applicationId,
        bool       $waitForConfirmation = false,
        int        $timeout = 5
    )
    {
        $address = $account->getAddress();

        // Get the suggested transaction params
        $params = $this->getSuggestedTransactionParams();

        // Create a new asset
        $transaction = TransactionBuilder::applicationClearState()
            ->sender($address)
            ->applicationId($applicationId)
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        return $this->sendTransaction($signedTransaction, $waitForConfirmation, $timeout);
    }
}
