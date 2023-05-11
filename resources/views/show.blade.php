@extends('layouts.app')

@section('title', 'Here is your data')


@section('styles')
    <style>
        .production {
            font-size: 18px;
        }
        .main {
            height: 100vh
        }
    </style>
@endsection


@section('content')
    <div class="step main">
        <p>{{$count}} Productions</p>
        <ol>
            @foreach($productions as $movie)
                <li class="production">
                    <div>
                        <span>({{$movie->type}}) {{$movie->title}}</span><br/>
                        <ol>
                            @foreach($movie->sites as $site)
                                <li>
                                    <span>Shoot: {{$site->shoot_date}} @ {{$site->name}}</span><br/>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
@endsection
