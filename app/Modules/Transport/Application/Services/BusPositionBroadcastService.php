<?php

namespace App\Modules\Transport\Application\Services;

use App\Modules\Transport\Domain\Events\BusPositionUpdated;
use App\Modules\Transport\Domain\Models\Bus;
use App\Modules\Transport\Domain\Models\TransportBusPositionLog;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Support\Facades\Log;

/**
 * Persists a bus's real position, logs it for replay, and broadcasts it
 * live — the durable path behind the driver app's HTTP position push
 * (DriverController::updatePosition), the only source of a bus's position
 * now. The driver app also pushes over a Reverb client event straight to
 * other subscribers for lower latency (see DriverSocketClient), but that
 * fast path is never persisted — this is the only place a position
 * actually gets saved.
 */
class BusPositionBroadcastService
{
    public function updateAndBroadcast(Bus $bus, float $latitude, float $longitude): Bus
    {
        $now = now();

        $bus->update([
            'current_latitude' => $latitude,
            'current_longitude' => $longitude,
            'position_updated_at' => $now,
        ]);
        $bus->refresh();

        TransportBusPositionLog::create([
            'bus_id' => $bus->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'recorded_at' => $now,
        ]);

        // The position is already persisted above — that's the real signal a
        // driver or dispatcher just reported. Broadcasting it live is a
        // nice-to-have on top: if Reverb is unreachable (not running, network
        // blip), that shouldn't turn an otherwise-successful position update
        // into a 500 for the caller. Every currently-connected watcher just
        // won't see this one tick move live; the next successful position
        // update (or their next page load) catches them back up.
        try {
            broadcast(new BusPositionUpdated($bus));
        } catch (BroadcastException $e) {
            Log::warning('BusPositionUpdated broadcast failed, position was still saved', [
                'bus_id' => $bus->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $bus;
    }
}
