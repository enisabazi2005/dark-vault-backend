<?php

namespace App\Http\Controllers;

use App\Models\WeeklyOnlineTimes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeeklyOnlineTimesController extends Controller
{
    public function getWeeklyOnlineTimes() { 
        $user = Auth::user();

        if(!$user->has_pro) return response()->json(['message' => 'Pro Version is required , please contact support team'], 403);

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $data = WeeklyOnlineTimes::where('dark_users_id', $user->id)
            ->whereIn('day', $daysOfWeek)
            ->get()
            ->keyBy('day');

        $result = [];

        foreach($daysOfWeek as $day) { 
            $minutes = $data[$day]->minutes_online ?? 0;

            $result[] = [
                'name' => $day,
                'value' => round($minutes / 60,2)
            ];
        }

        return response()->json($result);
    }
}
