<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
// use Auth;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Meal;
use App\Models\Cuisine;
use App\Models\Family;
use Illuminate\Support\Facades\DB;


class HomeController extends Controller
{
    public function index(Request $request )
    {
   
        $meals = Meal::all();
        $cuisines = Cuisine::orderBy('name', 'asc')->get();
        
        $search = [];

        if (Auth::check()) {
            $familys = Family::where('id', Auth::user()->family_id)->get();
            $selectedFamily = Auth::user()->family_id;
            $search['users.family_id'] = Auth::user()->family_id;
        } elseif (isset($request->family)) {
            $familys = Family::where('public_access', 1)->get();
            $selectedFamily = $request->family;
            $search['users.family_id'] = $request->family;
        } else {
            $familys = Family::where('public_access', 1)->get();
            $selectedFamily = 1;
            $search['users.family_id'] = 1;
        }

        $serves = $request->query('serves',0);
        $rating = isset($request->rating) ? $request->rating : 0;
        $oldCuisine = isset($request->cuisine) ? $request->cuisine : 0;
        $oldMeal = isset($request->meal) ? $request->meal : 0;
        
        $servesFrom = 0;
        $servesTo = 11;

        if (isset($request->rating) && $request->rating != "All") {
            $search['rating'] = $request->rating;
        }

        if (isset($request->cuisine) && $request->cuisine != "All") {
            $search['cuisine_id'] = $request->cuisine;
        }

        if (isset($request->meal) && $request->meal != "All") {
            $search['meal_id'] = $request->meal;
        }



        if ($request->serves == "1") {
            $servesTo = 2;
        } elseif ($request->serves == "3") {
            $servesFrom = 3;
            $servesTo = 4;
        } elseif ($request->serves == "5") {
            $servesFrom = 5;
        }

        $search['families.public_access'] = 1;
        // $recipes = DB::table('recipes')
        //     ->join('users', 'users.id', '=', 'recipes.user_id' )
        //     ->where('users.family_id','=', 1)
        //     ->get();
        $recipes = Recipe::join('users', 'users.id', '=', 'recipes.user_id' )
            ->join('families', 'families.id', '=', 'users.family_id' )
             ->where($search)
            ->whereBetween('serves', [$servesFrom, $servesTo])
            ->orderBy('rating', 'desc')
            ->select('recipes.*')
            ->get();
        
        return view('welcome', ['recipes' => $recipes, 'serves' => $serves, 'rating' => $rating ,'cuisines' => $cuisines, 
            'oldCuisine' => $oldCuisine  , 'meals' => $meals, 'oldMeal'=> $oldMeal, 'familys'=> $familys, 'selectedFamily'=>$selectedFamily]);
    }
}
