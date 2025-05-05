<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\DB;

class SurveyResponseController extends Controller{
    public function store(Request $request){
        // validate the request
        $checking = DB::table('survey_responses')
            ->where('token', $request->token)
            ->exists();
        if ($checking) {
            return response()->json([
                'message' => 'Survey Already Submitted'
            ], 400);
        }
        $branch_id = substr($request->token, 12);  // Removes the first 12 characters
        $request->merge(['branch_id' => (int)$branch_id]); // Merge the branch_id into the request
/*         $request->validate([
            'name' => 'nullable|string|max:255',
            'rating' => 'required|integer|between:1,5',
            'ease_of_use' => 'required|string|in:Yes,No',
            'waiting_time_satisfaction' => 'required|string',
            'waiting_time_satisfaction' => 'required|between:1,5',
            'suggestions' => 'nullable|string',
        ]); */

        SurveyResponse::create($request->all());

        return response()->json(['message' => 'Survey Submitted Successfully'], 201);
    }

    public function SurveyStats(Request $request)
{
    if ($request->branch_id != 0) {
        $ratings = SurveyResponse::selectRaw('rating, COUNT(*) as total')
        ->where('branch_id',$request->branch_id)
        ->groupBy('rating')
        ->orderBy('rating')
        ->pluck('total', 'rating');

        $easeOfUse = SurveyResponse::selectRaw('ease_of_use, COUNT(*) as total')
            ->where('branch_id',$request->branch_id)
            ->groupBy('ease_of_use')
            ->pluck('total', 'ease_of_use');

        $waitingSatisfaction = SurveyResponse::selectRaw('waiting_time_satisfaction, COUNT(*) as total')
            ->where('branch_id',$request->branch_id)
            ->groupBy('waiting_time_satisfaction')
            ->orderBy('waiting_time_satisfaction')
            ->pluck('total', 'waiting_time_satisfaction');

        return response()->json([
            'ratings' => $ratings,
            'ease_of_use' => $easeOfUse,
            'waiting_time_satisfaction' => $waitingSatisfaction,
        ]);
    }else {
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

    public function index (Request $request) {
        try {
            $surveyResponses = "";
            if ($request->branch_id != 0) {
                $surveyResponses = $this->getData($request->branch_id);
            } else {
                $surveyResponses = $this->getData();
            }

            return response()->json([
                'rows' => $surveyResponses,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getData ($Branch_id = null) {
        try {
            $res = DB::table('survey_responses as sr')
                ->select(
                    'sr.id',
                    'sr.name',
                    'sr.suggestions',
                    'b.branch_name'
                )
                ->where('sr.suggestions', '!=', null)
                ->join('branchs as b', 'sr.branch_id', '=', 'b.id') 
                ->orderBy('sr.created_at', 'desc');
                
            if ($Branch_id != null) {
                $res = $res->where('sr.branch_id', $Branch_id);
            }
            $res = $res->get();
            return $res;
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }

}
