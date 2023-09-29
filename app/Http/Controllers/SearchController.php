<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SearchHistory;

class SearchHistoryController extends Controller
{
    public function index()
    {
        // Get the search history for the authenticated user
        $searchHistory = SearchHistory::where('user_id', auth()->user()->id)->get();

        return response()->json(['search_history' => $searchHistory]);
    }
}
