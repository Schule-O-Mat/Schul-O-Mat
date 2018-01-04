@extends('layouts.app')
@section("main")
  <div class="row">
    <div class="col s12">
        <ul class="pagination">
            <div class="center-align">
                <p>Seite {{ $page+1 }} von {{ $cnt+1 }}</p>
            </div>
            @if ($zurueck)
                <li class="waves-effect"><a href="{{ action("SearchController@searchGet", ["page" => $page-1, "searchword" => $userSearch]) }}"><i class="material-icons">chevron_left</i></a></li>
            @else
                <li class="disabled"><a><i class="material-icons">chevron_left</i></a></li>
            @endif @if ($weiter)
                <li class="chevron_right waves-effect"><a href="{{ action("SearchController@searchGet", ["page" => $page+1, "searchword" => $userSearch]) }}"><i class="material-icons">chevron_right</i></a></li>
            @else
                <li class="chevron_right disabled"><a><i class="material-icons">chevron_right</i></a></li>
            @endif
        </ul>
      <ul class="collection">
        @foreach ($data as $d)
          <li class="collection-item avatar">
            <i class="material-icons circle
            @if ($d->schulformID == 1) light-green
            @elseif ($d->schulformID == 7) blue
            @elseif ($d->schulformID == 2) #aa00ff
            @elseif ($d->schulformID == 4) #d50000
            @elseif ($d->schulformID == 5) #ffff00
            @else #9e9e9e @endif">
              school</i>
              <span class="title">{{$d->bezeichnung}}</span>
              <p> @if($d->bezeichnung_kurz != ""){{$d->bezeichnung_kurz}}@endif     </p>
              <p> @if($d->details->strasse != "" and $d->details->ort != ""){{$d->details->strasse ." ". $d->details->ort}}@endif     </p>
            <a href="{{ action("SchulDetailController@detail", ["id" => $d->id]) }}" class="secondary-content"><i class="blue-text material-icons">arrow_forward</i></a>
          </li>
        @endforeach
      </ul>
        <ul class="pagination">
            @if ($zurueck)
                <li class="waves-effect"><a href="{{ action("SearchController@searchGet", ["page" => $page-1, "searchword" => $userSearch]) }}"><i class="material-icons">chevron_left</i></a></li>
            @else
                <li class="disabled"><a><i class="material-icons">chevron_left</i></a></li>
            @endif @if ($weiter)
                <li class="chevron_right waves-effect"><a href="{{ action("SearchController@searchGet", ["page" => $page+1, "searchword" => $userSearch]) }}"><i class="material-icons">chevron_right</i></a></li>
            @else
                <li class="chevron_right disabled"><a><i class="material-icons">chevron_right</i></a></li>
            @endif
                <div class="center-align">
                    <p>Seite {{ $page+1 }} von {{ $cnt+1 }}</p>
                </div>
        </ul>
    </div>
  </div>
@endsection
