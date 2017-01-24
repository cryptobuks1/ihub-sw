<?php

namespace App\Components\Integrations\InspiredVirtualGaming;

use App\Components\Integrations\InspiredVirtualGaming\Services\DataMapper;
use App\Components\Integrations\InspiredVirtualGaming\Services\OutcomeService;
use App\Components\Integrations\VirtualSports\Interfaces\EventBuilderInterface;
use App\Components\Integrations\VirtualSports\Services\CategoryService;
use App\Components\Integrations\VirtualSports\Services\EventService;
use App\Components\Integrations\VirtualSports\Services\TournamentService;
use App\Components\Traits\ConfigTrait;
use App\Models\InspiredVirtualGaming\EventLink;
use App\Models\Line\Category;
use App\Models\Line\Event;
use App\Models\Line\Market;
use App\Models\Line\MarketTemplate;
use App\Models\Line\Outcome;
use App\Models\Line\Tournament;
use Illuminate\Database\Eloquent\Collection;

class EventBuilder extends \App\Components\Integrations\VirtualSports\EventBuilder implements EventBuilderInterface
{
    use ConfigTrait;

    protected $controllerId;

    public function __construct(array $eventData)
    {
        $this->config = config('integrations.inspired');

        $this->eventType = (int) array_get($this->eventData, 'EventType');
        $this->controllerId = (int) array_get($this->eventData, 'ControllerId');

        parent::__construct($eventData, DataMapper::class);
    }

    protected function getCategory() : Category
    {
        $eventName = $this->getConfigOption('sports.'. $this->eventType . '.name');

        $category = (new CategoryService(
            "{$eventName}_{$this->controllerId}",
            $this->getConfigOption('sport_id'),
            100,
            $this->getConfigOption('gender'),
            (int) $this->getConfigOption('country_id')
        ))->resolve();

        return $category;
    }

    protected function getTournament(int $categoryId, string $eventName) : Tournament
    {
        return (new TournamentService(
            $eventName,
            $categoryId,
            100,
            (int) $this->getConfigOption('sports.'. $this->eventType . '.sportform_prebet_id'),
            (int) $this->getConfigOption('country_id'),
            $this->getConfigOption('sports.'. $this->eventType . '.max_bet'),
            $this->getConfigOption('sports.'. $this->eventType . '.max_payout'),
            $this->getConfigOption('sports.'. $this->eventType . '.stop_loss'),
            $this->getConfigOption('sports.'. $this->eventType . '.sportform_live_id'),
            $this->getConfigOption('gender'),
            (int) $this->getConfigOption('user_id')
        ))->resolve();
    }

    protected function getEvent(int $tournamentId, string $eventTime, string $eventName, $originalEventId, array $participants) : Event
    {
        $eventService = (new EventService(
            $tournamentId,
            $eventTime,
            $eventName,
            100,
            $this->getConfigOption('sports.'. $this->eventType . '.max_bet'),
            $this->getConfigOption('sports.'. $this->eventType . '.max_payout'),
            $this->getConfigOption('sports.'. $this->eventType . '.stop_loss'),
            $this->getConfigOption('country_id'),
            $this->getConfigOption('sport_id'),
            $this->getConfigOption('user_id'),
            $participants
        ));

        $event = $eventService->resolve();

        EventLink::create([
            'event_id' => $event->id,
            'event_id_ivg' => (int) $originalEventId
        ]);

        $this->eventParticipants = $eventService->getEventParticipants();

        return $event;
    }

    protected function getOutcome(string $market, array $outcome, array $mappedMarketsWithOutcomes, MarketTemplate $marketTemplate, Collection $outcomeTypes, Market $marketModel, Collection $eventParticipants) : Outcome
    {
        return (new OutcomeService(
            $market,
            $outcome,
            $mappedMarketsWithOutcomes,
            $marketTemplate,
            $outcomeTypes,
            $marketModel,
            $eventParticipants
        ))->resolve();
    }
}