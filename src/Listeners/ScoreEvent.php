<?php

namespace Flagrow\Guardian\Listeners;

use Flagrow\Guardian\Contracts\Marker;
use Flagrow\Guardian\Events\Configuration;
use Flagrow\Guardian\Events\FootPrinting;
use Illuminate\Contracts\Events\Dispatcher;

class ScoreEvent
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(FootPrinting::class, [$this, 'score']);
    }

    public function score(FootPrinting $event)
    {
        $configuration = $this->configuration->get(get_class($event->event), []);

        /** @var int|string $score A scoring integer or a class to score the event with. */
        $score = array_get($configuration, 'score');

        if (!filter_var($score, FILTER_VALIDATE_INT) &&
            class_exists($score) &&
            in_array(Marker::class, class_implements($score))) {
            $score = $event->attributes->put('score', (new $score)->__invoke($event));
        }

        $event->attributes->put('score', $score);
    }
}
