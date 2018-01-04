<?php
namespace App\Http\Controllers;

use App\User;
use App\schulen;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Auth;

class SchulMasterController extends Controller
{

    function redirect()
    {
        return redirect("/schulen/0");
    }

    function pagination($page)
    {
        $calc = $page * 25;
        $cnt = schulen::count();
        $weiter = true;
        $zurueck = ($page == 0) ? false : true;
        if ($calc + 25 > $cnt)
            $weiter = false;
        $data = schulen::take(25)->skip($calc)->with("details")->get();
        $staedte = DB::table("schuldetails")->select("ort")->groupBy("ort")->get();
        return view('master', compact("data", "zurueck", "weiter", "page", "staedte", "cnt"));
    }

    function paginationFilter(Request $request)
    {
    	$page = $request->get('page');
	    $calc = $page * 25;
		$ort = $request->get("ort");
		$data = schulen::whereHas("details", function($query) use($ort) {
		    $query->where('ort', '=', $ort);
		});
	    $cnt = $data->count();
        $data = $data->take(25)->skip($calc)->get();
	    $weiter = true;
	    $zurueck = ($page == 0) ? false : true;
	    if ($calc + 25 > $cnt)
		    $weiter = false;
        return view("master_filter", compact("data", "zurueck", "weiter", "page", "ort", "cnt"));
    }

    function newKeyword() {
        if(Auth::guest())
            return redirect("/");

        return view("newkeyword");
    }

    function newKeywordEintragen(Request $request){
//        $userID = Auth::user()->id;
        //Hole Eingaben von allen Eingabefeldern
        $bezeichnung = $request->get("bezeichnung");
            //Füge positive Keywords ein
            DB::table('keywords')->insert(['bezeichnung' => $bezeichnung]);

        return redirect("/schule/");
    }
}
