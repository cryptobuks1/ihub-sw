<?php

namespace App\Console\Commands\Orion;

use App\Components\ExternalServices\MicroGaming\Orion\SoapEmulator;
use App\Components\Integrations\MicroGaming\Orion\CommitRollbackProcessor;
use App\Components\Integrations\MicroGaming\Orion\Request\GetCommitQueueData;
use App\Components\Integrations\MicroGaming\Orion\Request\ManuallyValidateBet;
use App\Components\Integrations\MicroGaming\Orion\SourceProcessor;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use App\Http\Requests\Validation\Orion\CommitValidation;
use App\Http\Requests\Validation\Orion\ManualValidation;
use Illuminate\Console\Command;
use function app;

class Commit extends Command {

    use Operation;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orion:commit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commit orion transactions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $sourceProcessor = app(SourceProcessor::class);
        $soapEmul = app(SoapEmulator::class);
        $requestQueueData = new GetCommitQueueData($soapEmul, $sourceProcessor);
        $validatorQueueData = app(CommitValidation::class);
        $requestResolveData = new ManuallyValidateBet($soapEmul, $sourceProcessor);
        $validatorResolveData = app(ManualValidation::class);
        $operationsProcessor = new CommitRollbackProcessor('CommitQueue', TransactionRequest::TRANS_WIN);
        $this->make($requestQueueData, $validatorQueueData, $operationsProcessor, $requestResolveData, $validatorResolveData);
    }

}
