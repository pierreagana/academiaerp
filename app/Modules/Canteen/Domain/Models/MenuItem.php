<?php

namespace App\Modules\Canteen\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Support\Tenancy\BelongsToSchool;

class MenuItem extends Model
{
    use BelongsToSchool;
    protected $table = 'canteen_menu_items';

    protected $fillable = [
        'school_id',
        'date',
        'slot',
        'title',
        'description',
        'tags',
        'allergens',
    ];

    protected $casts = [
        'date' => 'date',
        'tags' => 'array',
        'allergens' => 'array',
    ];

    /**
     * Monday of the week the parent app and the school's planner/reservations
     * pages should default to: the current week while we're still in it
     * (Mon–Fri), but next week once Friday has gone by — nobody should land
     * on a menu that's entirely in the past with nothing left to order.
     * Shared by ParentPortalService (mobile/web parent views) and
     * SchoolDashboard's CanteenController (planning/reservations), which
     * otherwise have no reason to depend on each other.
     */
    public static function currentWeekStart(): Carbon
    {
        $monday = now()->startOfWeek(Carbon::MONDAY);
        $fridayEnd = $monday->copy()->addDays(4)->endOfDay();

        return now()->greaterThan($fridayEnd) ? $monday->addWeek() : $monday;
    }
}
