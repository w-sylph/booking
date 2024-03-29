<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\BookingLocation;
use App\BookingReport;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index()
    {
    	return view('pages.booking.index');
    }

    public function store(BookingDateRequest $request)
    {
    	return response()->json(true);
    }

    public function checkDate(Request $request) {

        $message = 'Date is unavailable';

    	$action = BookingReport::isUnavailable($request->input('booking_date'), $request->input('booking_location_id'));

        if (!$action) {
            $message = 'Date is available';
        }

    	return response()->json([
    		'action' => !$action,
    		'message' => $message,
    	]);
    }

    public function fetchEvents(Request $request) {

    	$events = [];
        $locationid = $request->location_id;

        $year = Carbon::parse($request->input('calendar_date'))->format('Y');
        $month = Carbon::parse($request->input('calendar_date'))->format('m');

        $reports = BookingReport::select('booking_date')
                    ->whereYear('booking_date', $year)
                    ->whereMonth('booking_date', $month)
                    ->groupBy('booking_date')
                    ->get();


        foreach($reports as $report){
            if(BookingReport::isUnavailable($report->booking_date, $request->input('booking_location_id'))){
                $startDate = Carbon::parse($report->booking_date)->format('Y-m-d');
                array_push($events, array(
                    'title' => 'Unavailable',
                    'allDay' => true,
                    'start' => $startDate,
                    'color' => '#666',
                    'textColor' => '#fff',
                    'backgroundColor' => '#e3342f',
                ));
            }
        }

        return response()->json([
            'events' => $events,
        ]); 
    }
}
