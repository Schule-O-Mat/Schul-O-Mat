<?php
namespace App\Http\Controllers;

use App\bewertungen;
use App\fragen;
use App\User;
use App\schulen;
use App\keywords;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Request;

class SchulDetailController extends Controller {

    function detail($schule){
        // cooler kommi
        try{
            $bewertungda = false;
            if(!Auth::guest())
                if(isset(Auth::user()->bewertung))
                    $bewertungda = DB::table('bewertungen')->select(DB::raw('COUNT(*) as cnt'))->where('userID', '=', Auth::user()->id)->first()->cnt > 0;
            $schulID = $schule;
            $schule = schulen::findOrFail($schule);
            $adresse = $schule->details->strasse . " " . $schule->details->plz . " " . $schule->details->ort;

            $cnt = DB::table("bewertungen")->join("users", "users.id", "=", "bewertungen.userID")->select(DB::raw("count(*) as cnt"))->where("users.schulID","=",$schulID)->first()->cnt;
            if($cnt > 0):

                //Berechne Durchschnitt der Bewertungen pro Schule per SQL-Query
                $durchschnitt = array();
                $durchschnitt[0] = DB::table('key_bew')->join('users', 'key_bew.userID', '=', 'users.id')->select(DB::raw('AVG(     ) as b1'))->where('users.schulID', '=', $schulID)->first()->b1;
                $durchschnitt[1] = DB::table('bewertungen')->join('users', 'bewertungen.userID', '=', 'users.id')->select(DB::raw('AVG(bewertung2) as b2'))->where('users.schulID', '=', $schulID)->first()->b2;
                $durchschnitt[2] = DB::table('bewertungen')->join('users', 'bewertungen.userID', '=', 'users.id')->select(DB::raw('AVG(bewertung3) as b3'))->where('users.schulID', '=', $schulID)->first()->b3;
                $durchschnitt[3] = DB::table('bewertungen')->join('users', 'bewertungen.userID', '=', 'users.id')->select(DB::raw('AVG(bewertung4) as b4'))->where('users.schulID', '=', $schulID)->first()->b4;
                $durchschnitt[4] = DB::table('bewertungen')->join('users', 'bewertungen.userID', '=', 'users.id')->select(DB::raw('AVG(bewertung5) as b5'))->where('users.schulID', '=', $schulID)->first()->b5;
                $durchschnitt[5] = DB::table('bewertungen')->join('users', 'bewertungen.userID', '=', 'users.id')->select(DB::raw('AVG(bewertung6) as b6'))->where('users.schulID', '=', $schulID)->first()->b6;
                $durchschnitt[6] = DB::table('bewertungen')->join('users', 'bewertungen.userID', '=', 'users.id')->select(DB::raw('AVG(bewertung7) as b7'))->where('users.schulID', '=', $schulID)->first()->b7;

                //Ermittle alle Keywords die mit der Schule zusammenhängen
                $posi = DB::table('bewertungen')->join('users', 'bewertungen.userID', '=', 'users.id')->join('key_bew', 'bewertungen.id', '=', 'key_bew.bewertungID')->join('keywords', 'key_bew.keywordID', '=', 'keywords.id')->select('keywords.bezeichnung')->where('users.schulID', '=', $schulID)->get();
                $keywords = array();
                foreach ($posi as $p)
                {
                    //Berechne Anzahl der Vorkommnisse positiv und negativ
                    $countpos = DB::table('bewertungen')->join('users', 'bewertungen.userID', '=', 'users.id')->join('key_bew', 'bewertungen.id', '=', 'key_bew.bewertungID')->join('keywords', 'key_bew.keywordID', '=', 'keywords.id')->select(DB::raw('COUNT(key_bew.keywordID) as pos'))->where('users.schulID', '=', $schulID)->where('keywords.bezeichnung', '=', $p->bezeichnung)->where('key_bew.positiv', '=', '1')->first()->pos;
                    $countneg = DB::table('bewertungen')->join('users', 'bewertungen.userID', '=', 'users.id')->join('key_bew', 'bewertungen.id', '=', 'key_bew.bewertungID')->join('keywords', 'key_bew.keywordID', '=', 'keywords.id')->select(DB::raw('COUNT(key_bew.keywordID) as neg'))->where('users.schulID', '=', $schulID)->where('keywords.bezeichnung', '=', $p->bezeichnung)->where('key_bew.positiv', '=', '0')->first()->neg;
                    $keywords[$p->bezeichnung] = [$countpos, $countneg];
                }

                //Hole alle Einzelbewertungen
                $reviews = DB::table('bewertungen')->join('users', 'bewertungen.userID', '=', 'users.id')->select('bewertung')->where('users.schulID', '=', $schulID)->get();

                //Hole den redaktionellen Inhalt
                //$redaktionell = DB::table('redaktion')->select('text')->where('schulID', '=', $schulID)->first()->text;
            endif;
            return view('detail', compact("schule", "hochwert", "rechtswert", "durchschnitt", "keywords", "reviews", "redaktionell", "schulID", "bewertungda", "adresse"));
        }
        catch(ModelNotFoundException $e){
            return redirect("/");
        }
    }

    function karte($schule){
        $schule = schulen::find($schule);
        $hochwert = $schule->adresse->hw; //hochwert
        $rechtswert = $schule->adresse->rw; //rechtswert
        return view('karten', compact("hochwert", "rechtswert"));
    }

    function fragebogen($id){
        if(Auth::guest() or Auth::user()->type != "student" or Auth::user()->schulID != $id)
            return redirect("/");

        //Finde die Schule anhand der übergebenen ID
        $schule = schulen::findOrFail($id)->with("details")->first();

        //Hole alle für diese Schule aktivierten Fragen aus der DB
        $frageIDs = unserialize($schule->details->aktivierte_fragen);
        if (!$frageIDs) {
            $fragenList = [];
        }
        else {
            $fragenList = fragen::whereIn('id', $frageIDs)->get();
        }

        //Hole alle nicht deaktivierten Keywords
        $keywordIDs = unserialize($schule->details->deaktivierte_keywords);
        if (!$keywordIDs) {
            $keywordIDs = [];
        }
        $keywordList = keywords::whereNotIn('id', $keywordIDs)->get();

        return view("fragebogen", compact("id", "fragenList", "keywordList"));
    }

    function eintragen(Request $request, $id){

        //Hole Benutzerdetails
        $schulID = Auth::user()->schulID;
        $userID = Auth::user()->id;

        //Finde die Schule anhand der übergebenen ID
        $schule = schulen::findOrFail($schulID)->with("details")->first();

        //Hole aktivierte Fragen
        $frageIDs = unserialize($schule->details->aktivierte_fragen);
        if (!$frageIDs) {
            $fragenList = [];
        }
        else {
            $fragenList = fragen::whereIn('id', $frageIDs)->get();
        }

        //Füge Bewertungen für alle Fragen in die DB ein
        foreach ($fragenList as $frage) {
            $bewertung = new bewertungen();
            $bewertung->userID = $userID;
            $bewertung->frageID = $frage->id;
            $bewertung->bewertung = Request::get($frage->id);
        }

        //Hole die restlichen Felder
        $positiv = Request::get("positive");
        $negativ = Request::get("negative");
        $freitext = Request::get("freitext");

        //Füge Freitext ein
        bewertungen::insert(['userID' => $userID, 'frageID' => 0, 'bewertung' => $freitext]);

        //Füge positive und negative Keywords ein
        foreach ($positiv as $keyword)
        {
        	print($keyword);
            //Füge positive Keywords ein
            DB::table('key_bew')->insert(['userID' => $userID, 'keywordID' => $keyword, 'positiv' => '1']);
        }
        foreach ($negativ as $keyword)
        {
            //Füge negative Keywords ein
            DB::table('key_bew')->insert(['userID' => $userID, 'keywordID' => $keyword, 'positiv' => '0']);
        }

//        return redirect("/schule/".$schulID);
    }

    function redaktion($id){
        return view("redaktion", compact("id"));
    }

    function redaktionEintragen(Request $request, $id){
        $toWrite = Request::get("redaktionstext");
        DB::table("redaktion")->insert([
            'schulID' => $id,
            'text' => $toWrite
        ]);
        return $request;
        return view("redaktion", compact("id"));
    }
}
