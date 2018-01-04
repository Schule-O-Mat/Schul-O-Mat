<?php
namespace App\Http\Controllers;

use App\User;
use App\schulen;
use App\Http\Controllers\Controller;
use DB;
use Request;
class SearchController extends Controller {
    public function searchGet(Request $request) {

        // Sets the parameters from the get request to the variables.
        $userSearch = $request::get("searchword");
        // Perform the query using Query Builder
        $page = $request::get('page');
        $calc = $page * 25;
	    $weiter = true;
	    $zurueck = ($page == 0) ? false : true;
//        $data = DB::table('schulen')
//            ->select("*")
//            ->join('schulbezeichnung', 'schulbezeichnung.id', '=', 'schulen.fkbezeichnungen')
//            ->where('kurzbez', 'LIKE', "%$userSearch%")
//            ->orWhere('schulbez1', 'LIKE', "%$userSearch%")
//            ->orWhere('schulbez2', 'LIKE', "%$userSearch%")
//            ->orWhere('schulbez3', 'LIKE', "%$userSearch%")
//            ->take(25)
//            ->skip($calc)
//            ->get();
//	    $cnt = DB::table('schulen')
//		    ->select("*")
//		    ->join('schulbezeichnung', 'schulbezeichnung.id', '=', 'schulen.fkbezeichnungen')
//		    ->where('kurzbez', 'LIKE', "%$userSearch%")
//		    ->orWhere('schulbez1', 'LIKE', "%$userSearch%")
//		    ->orWhere('schulbez2', 'LIKE', "%$userSearch%")
//		    ->orWhere('schulbez3', 'LIKE', "%$userSearch%")
//            ->count();

        $result = schulen::where('bezeichnung', 'LIKE', "%$userSearch%")->orWhere('bezeichnung_kurz', 'LIKE', "%$userSearch%");
        $cnt = $result->count();
        $data = $result->take(25)->skip($calc)->get();
	    if ($calc + 25 > $cnt)
		    $weiter = false;
        return view('master_search', compact("data", "zurueck", "weiter", "page", "userSearch", "cnt"));
    }

    public function index () {
        return redirect("/schulen/0");
    }
}
?>
