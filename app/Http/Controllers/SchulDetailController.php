<?php
namespace App\Http\Controllers;

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
      $hochwert = $schule->adresse->hw; //hochwert
      $rechtswert = $schule->adresse->rw; //rechtswert
      $schueler = $schule->schueler;

      $cnt = DB::table("bewertungen")->join("users", "users.id", "=", "bewertungen.userID")->select(DB::raw("count(*) as cnt"))->where("users.schulID","=",$schulID)->first()->cnt;
      if($cnt > 0):

      //Berechne Durchschnitt der Bewertungen pro Schule per SQL-Query
      $durchschnitt = array();
      $durchschnitt[0] = DB::table('bewertungen')->join('users', 'bewertungen.userID', '=', 'users.id')->select(DB::raw('AVG(bewertung1) as b1'))->where('users.schulID', '=', $schulID)->first()->b1;
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
      $reviews = DB::table('bewertungen')->join('users', 'bewertungen.userID', '=', 'users.id')->select('freitext')->where('users.schulID', '=', $schulID)->get();

      //Hole den redaktionellen Inhalt
      //$redaktionell = DB::table('redaktion')->select('text')->where('schulID', '=', $schulID)->first()->text;
      endif;
    return view('detail', compact("schule", "hochwert", "rechtswert", "durchschnitt", "keywords", "reviews", "redaktionell", "schulID", "bewertungda"));
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
    $keywords = keywords::all();
    return view("fragebogen", compact("id", "keywords"));
  }

  function eintragen(Request $request, $id){
    $schulID = Auth::user()->schulID;
    $userID = Auth::user()->id;
    //Hole Eingaben von allen EIngabefeldern
    $question1 = Request::get("schoolgeneral");
    $question2 = Request::get("mensa");
    $question3 = Request::get("ag");
    $question4 = Request::get("austattung");
    $question5 = Request::get("toilet");
    $question6 = Request::get("length");
    $question7 = Request::get("time");
    $positiv = Request::get("positive");
    $negativ = Request::get("negative");
    $freitext = Request::get("freitext");
    //Füge die Bewertung mit den Slidern ein und hole die Bewertungs-ID
    $bewertungID = DB::table('bewertungen')->insertGetID(['userID' => $userID, 'bewertung1' => $question1, 'bewertung2' => $question2, 'bewertung3' => $question3, 'bewertung4' => $question4, 'bewertung5' => $question5, 'bewertung6' => $question6, 'bewertung7' => $question7,'freitext' => $freitext]);
    foreach ($positiv as $keyword)
    {
        //Füge positive Keywords ein
        DB::table('key_bew')->insert(['bewertungID' => $bewertungID, 'keywordID' => $keyword, 'positiv' => '1']);
    }
    foreach ($negativ as $keyword)
    {
        //Füge negative Keywords ein
        DB::table('key_bew')->insert(['bewertungID' => $bewertungID, 'keywordID' => $keyword, 'positiv' => '0']);
    }

    return redirect("/schule/".$schulID);
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
