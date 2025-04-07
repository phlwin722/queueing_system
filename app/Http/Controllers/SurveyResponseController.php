<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SurveyResponse;

class SurveyResponseController extends Controller{
    public function store(Request $request){
        $request->validate([
            'name' => 'nullable|string|max:255',
            'rating' => 'required|integer|between:1,5',
            'ease_of_use' => 'required|string|in:Yes,No',
            /* 'waiting_time_satisfaction' => 'required|string', */
            'waiting_time_satisfaction' => 'required|between:1,5',
            'suggestions' => 'nullable|string',
        ]);

        SurveyResponse::create($request->all());

        return response()->json(['message' => 'Survey Submitted Successfully'], 201);
    }

    public function SurveyStats()
{
    $ratings = SurveyResponse::selectRaw('rating, COUNT(*) as total')
        ->groupBy('rating')
        ->orderBy('rating')
        ->pluck('total', 'rating');

    $easeOfUse = SurveyResponse::selectRaw('ease_of_use, COUNT(*) as total')
        ->groupBy('ease_of_use')
        ->pluck('total', 'ease_of_use');

    $waitingSatisfaction = SurveyResponse::selectRaw('waiting_time_satisfaction, COUNT(*) as total')
        ->groupBy('waiting_time_satisfaction')
        ->orderBy('waiting_time_satisfaction')
        ->pluck('total', 'waiting_time_satisfaction');

    return response()->json([
        'ratings' => $ratings,
        'ease_of_use' => $easeOfUse,
        'waiting_time_satisfaction' => $waitingSatisfaction,
    ]);
}

}
