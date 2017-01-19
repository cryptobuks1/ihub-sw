<?php

namespace App\Components\Integrations\InspiredVirtualGaming;


use App\Components\Integrations\VirtualSports\Calculator;
use App\Exceptions\Api\InspiredVirtualGaming\EventNotFoundException;
use App\Models\InspiredVirtualGaming\EventLink;
use App\Models\Line\Market;
use App\Models\Line\ResultGame;
use App\Models\Line\StatusDesc;
use Illuminate\Support\Facades\DB;

class EventProcessor
{
    protected $eventId;

    public function __construct(int $eventId = null)
    {
        $this->eventId = $eventId;
    }

    public function create(array $eventData) : bool
    {
        $eventBuilder = new EventBuilder($eventData);

        DB::connection('line')->beginTransaction();
        DB::connection('trans')->beginTransaction();

        try
        {
            $eventBuilder->create();
        } catch (\Exception $exception) {
            DB::connection('line')->rollBack();
            DB::connection('trans')->rollBack();

            app('AppLog')->warning([
                'message' => $exception->getMessage()
            ], null, 'event-failed');

            return false;
        }
        DB::connection('trans')->commit();
        DB::connection('line')->commit();


        return true;
    }

    public static function getEvent(int $eventId) : EventProcessor
    {
        $eventId = EventLink::getEventId($eventId);

        if($eventId == null)
        {
            throw new \RuntimeException("Event not found");
        }

        return new static($eventId);
    }

    public function setResult(array $eventData)
    {
        DB::connection('line')->beginTransaction();
        try {
            if (!ResultGame::isResultsApproved($this->eventId)) {
                $this->sendMassageFinished();
            }

            $eventResult = new EventResult($eventData, $this->eventId);

            $eventResult->process();

        $this->sendMassageFinished();

        } catch (\Exception $exception) {
            DB::connection('line')->rollBack();
            throw $exception;
        }
        DB::connection('line')->commit();
    }

    public function cancel() : bool
    {
        if(!$this->eventId) {
            return false;
        }

        $result = ResultGame::getResult($this->eventId);

        if($result !== null) {

            DB::connection('line')->beginTransaction();

            try {
                $this->suspendMarket();
                $this->createStatusDesc(StatusDesc::STATUS_CANCELLED);
                $this->updateGameResult();
            } catch (\Exception $exception) {
                DB::connection('line')->rollBack();
                throw $exception;
            }

            DB::connection('line')->commit();

            if(($status = Calculator::sendMessageApprove($this->eventId)) !== 'ok')
            {
                throw new \RuntimeException("Unable to send approve");
            }

            $this->sendAmQpMessage(StatusDesc::STATUS_CANCELLED);
        }

        return true;
    }

    public function stopBets() : bool
    {
        if(!$this->eventId) {
            return false;
        }

        DB::connection('line')->beginTransaction();

        try {
            $this->suspendMarket();
            $this->createStatusDesc(StatusDesc::STATUS_IN_PROGRESS);
        } catch (\Exception $exception) {
            DB::connection('line')->rollBack();
            throw $exception;
        }

        DB::connection('line')->commit();

        if(($status = Calculator::sendMessageApprove($this->eventId)) !== 'ok')
        {
            throw new \RuntimeException("Unable to send approve");
        }

        $this->sendAmQpMessage(StatusDesc::STATUS_IN_PROGRESS);

        return true;
    }

    protected function sendMassageFinished()
    {
        if(($status = Calculator::sendMessageApprove($this->eventId)) !== 'ok')
        {
            throw new \RuntimeException("Unable to send approve");
        }

        $this->sendAmQpMessage(StatusDesc::STATUS_FINISHED);
    }

    protected function updateGameResult()
    {
        if(!ResultGame::updateApprove($this->eventId)) {
            throw new \RuntimeException("Can't update approve event");
        }
    }

    protected function suspendMarket()
    {
        if(!(new Market())->suspendMarketEvent($this->eventId))
        {
            throw new \RuntimeException("Can't suspend market");
        }
    }

    protected function createStatusDesc(string $statusName)
    {
        if(! StatusDesc::createStatus($statusName, $this->eventId)){
            throw new \RuntimeException("Can't insert status_desc");
        }
    }

    protected function sendAmQpMessage(string $status)
    {
        $data = [
            'type' => $status,
            'data' => ['event_id' => $this->eventId]
        ];

        $response = app('AmqpService')->sendMsg(
            config('integrations.inspired.amqp.exchange'),
            config('integrations.inspired.amqp.key') . $this->eventId,
            json_encode($data)
        );

        if(!$response){
            throw new \RuntimeException('AmQp send failed');
        }

        return true;
    }
}