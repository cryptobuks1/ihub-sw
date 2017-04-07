<?php

namespace App\Console\Commands\Orion;

use App\Components\ExternalServices\MicroGaming\Orion\SoapEmulator;
use App\Components\Integrations\MicroGaming\Orion\CompleteGameProcessor;
use App\Components\Integrations\MicroGaming\Orion\Request\GetFailedEndGameQueue;
use App\Components\Integrations\MicroGaming\Orion\Request\ManuallyCompleteGame;
use App\Components\Integrations\MicroGaming\Orion\SourceProcessor;
use App\Http\Requests\Validation\Orion\EndGameValidation;
use App\Http\Requests\Validation\Orion\ManualCompleteValidation;
use Illuminate\Console\Command;
use function app;

class EndGame extends Command {

    use Operation;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orion:endgame';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'EndGame orion transactions';

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

        $requestQueueData = new GetFailedEndGameQueue($soapEmul, $sourceProcessor);
        $validatorQueueData = app(EndGameValidation::class);
        $operationsProcessor = app(CompleteGameProcessor::class);
        $requestResolveData = new ManuallyCompleteGame($soapEmul, $sourceProcessor);
        $validatorResolveData = app(ManualCompleteValidation::class);
        
        $this->make($requestQueueData, $validatorQueueData, $operationsProcessor, $requestResolveData, $validatorResolveData);
    }

}
