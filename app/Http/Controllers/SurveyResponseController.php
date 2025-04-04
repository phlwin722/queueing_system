<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SurveyResponse;

class SurveyResponseController extends Controller{
    public function store(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'rating' => 'required|integer|between:1,5',
            'ease_of_use' => 'required|string|in:Yes,No',
            /* 'waiting_time_satisfaction' => 'required|string', */
            'waiting_time_satisfaction' => 'required|between:1,5',
            'suggestions' => 'nullable|string',
        ]);

        SurveyResponse::create($request->all());

        return response()->json(['message' => 'Survey Submitted Successfully'], 201);
    }
}
