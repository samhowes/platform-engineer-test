<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/show', function (Request $request) {
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
    ]);
    $data = file_get_contents('https://c2t-cabq-open-data.s3.amazonaws.com/film-locations-json-all-records_03-19-2020.json');
    /**
     * Assignment: filter data in php
     * docs on data: http://data.cabq.gov/business/filmlocations/MetaData.pdf/view
     *
     * Filter data based on start and end date inputs (shoot date must fall between the start and end dates)
     * Adjust for your timezone
     * Filter out duplicate productions
     * Data should be returned as a json in this format:
     * {
     *      count: 1,
     *      productions: [
     *          {
     *              title: "production name",
     *              type: "movie, tv or other",
     *              sites: [
     *                  {
     *                      name: "site name",
     *                      shoot_date: "Month Date, Year"
     *                  }
     *              ]
     *          }
     *      ]
     * }
     *
     * On the front end (show.blade.php):
     * Display all data to user (just a bulleted list is fine)
     * Display date in a human readable format in your timezone
     */

    $parsed = json_decode($data);
    $shootLocations = $parsed->{'features'};
    $tz = new DateTimeZone($request->tz); // from hidden input inserted by client JS

    $startDate = strtotime($request->start_date) * 1000;
    $endDate = strtotime($request->end_date) * 1000;

    $filtered = collect($shootLocations)
        ->reject(function ($shootLocation) use ($endDate, $startDate) {
            return $shootLocation->attributes->ShootDate < $startDate
                || $shootLocation->attributes->ShootDate > $endDate;
        })
        ->groupBy('attributes.Title')
        ->map(function ($sites, $key) use ($tz) {
            $production = new Production();
            $production->title = $key;
            $firstType = $sites[0]->attributes->Type;

            if (str_starts_with($firstType, "Movie")) {
                $production->type = "movie";
            }
            else if (str_starts_with($firstType, "TV")) {
                $production->type = "tv";
            }
            else {
                $production->type = "other";
            }
            $production->sites = $sites->map(function ($site) use ($tz) {
                $s = new Site();
                if ($site->attributes->Site) {
                    $s->name = $site->attributes->Site;
                }
                $shootDate = new DateTime();
                $shootDate->setTimezone($tz);
                $shootDate->setTimestamp($site->attributes->ShootDate /1000);
                $s->shoot_date = $shootDate->format("M d, Y");

                return $s;
            })->toArray();
            return $production;
        })
        ->toArray();

    return view('show', [
        'count' => count($filtered),
        'productions' => $filtered
    ]);
});

class Production {
    public $title;
    public $type;
    public $sites;
}

class Site {
    public $name;
    public $shoot_date;
}
