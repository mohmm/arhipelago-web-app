<?php

namespace App\Enums;

/**
 * How an experience is travelled, which decides the router profile used to
 * measure it.
 */
enum TravelMode: string
{
    case Car = 'car';
    case Foot = 'foot';

    /**
     * The OSRM profile configured in `services.osrm.profiles`.
     */
    public function routingProfile(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return match ($this) {
            self::Car => __('By car'),
            self::Foot => __('On foot'),
        };
    }
}
