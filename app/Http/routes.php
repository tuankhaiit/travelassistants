<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Place;
use App\Province;
use App\Favorite;
use App\User;
use App\Review;
use App\Schedule;
use App\SchedulePlace;

Route::get('/', 'WelcomeController@index');

Route::get('home', 'HomeController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);

Route::get('myroute', function() {
	echo "Hello";
});

Route::resource('Province', 'ControllerProvince');

Route::resource('Place', 'ControllerPlace');

Route::post('Province/get/all', function(Request $request){
	$provinces = Province::all();
	$result = array('status' => "OK", 'result' => $provinces, 'message' => "");
	return $result;
});

Route::post('PlaceSlider/get/all', function(Request $request) {
	$places = array();
	$array = array('P3523', 'P3562', 'P3672', 'P2332', 'P2312');
	for ($i=0; $i < sizeof($array); $i++) { 
		$place = Place::find($array[$i]);
		array_push($places, $place);
	}
	$result = array('status' => "OK", 'result' => $places, 'message' => "");
	return $result;
});

Route::post('Place/find/province', function(Request $request){
	$result = Place::where('province_id', $request->input("id"))->get();
	return array('status' => "OK", 'result' => $result, 'message' => "");
});

Route::post('Place/find/query', function(Request $request){
	$query = "%".$request->input('query')."%";
	$places = Place::where('long_name', 'like', $query)
					->orwhere('province_name', 'like', $query)
					->get();
	$provinces = null;
	
	return array('status' => "OK", 'result' => ['places' => $places], 'message' => "");
});

Route::post('User/add/new', function(Request $request){
	$count = User::where('email', $request->input("email"))->count();
	if ($count > 0){
		return array('status' => "ERROR", 'result' => array(), 'message' => "User đã tồn tại!");
	} else {
		try {
			$user = new User;
			$user->id = $request->input("id");
			$user->name = $request->input("name");
			$user->email = $request->input("email");
			$user->profile_photo_url = $request->input("profile_photo_url");
			$user->save();
			return array('status' => "OK", 'result' => response($user, 201), 'message' => "");
		} catch (Exception $e) {
			return array('status' => "ERROR", 'result' => array(), 'message' => "");
		}
	}
});

Route::post('Favorite/add/new', function(Request $request){
	$count = Favorite::where('idUser', $request->input("idUser"))
						->where('idPlace', $request->input("idPlace"))
						->count();
	if ($count > 0) {
		return array('status' => "ERROR", 'result' => "", 'message' => "Đã tồn tại!");
	} else {
		$favorite = new Favorite;
		$favorite->idUser = $request->input("idUser");
		$favorite->idPlace = $request->input("idPlace");
		$favorite->save();
		$result = Place::where('id', $favorite->idPlace)->get();
		return array('status' => "OK", 'result' => $result, 'message' => "");
	}
});

Route::post('Favorite/remove/new', function(Request $request){
	$favorite = Favorite::where('idUser', $request->input("idUser"))
						->where('idPlace', $request->input("idPlace"))
						->delete();
	$result = Place::where('id', $request->input("idPlace"))->get();
	return array('status' => "OK", 'result' => $result, 'message' => "");
});

Route::post('Favorite/get/user', function(Request $request){
	$favorites = Favorite::where('idUser', $request->input("idUser"))->get();
	$result = array();
	for ($i = 0; $i < count($favorites); $i++) { 
		$tam = Place::where('id', $favorites[$i]->idPlace)->first();
		array_push($result, $tam);
	}
	return array('status' => "OK", 'result' => $result, 'message' => "");
});

Route::post('Favorite/check/id', function(Request $request){
	$count = Favorite::where('idUser', $request->input("idUser"))
		->where('idPlace', $request->input("idPlace"))->count();
	$result = false;
	if ($count > 0) {
		$result = true;
	}
	return array('status' => "OK", 'result' => $result, 'message' => "");
});

Route::post('Place/find/type', function(Request $request){
	$type = $request->input("type");
	switch ($type) {
		case '2':
			$result = Place::where('type_sea', "1")->get();
			return array('status' => "OK", 'result' => $result, 'message' => "");

		case '3':
			$result = Place::where('type_attractions', "1")->get();
			return array('status' => "OK", 'result' => $result, 'message' => "");

		case '4':
			$result = Place::where('type_cultural', "1")->get();
			return array('status' => "OK", 'result' => $result, 'message' => "");

		case '5':
			$result = Place::where('type_entertainment', "1")->get();
			return array('status' => "OK", 'result' => $result, 'message' => "");

		case '6':
			$result = Place::where('type_spring', "1")->get();
			return array('status' => "OK", 'result' => $result, 'message' => "");

		case '7':
			$result = Place::where('type_summer', "1")->get();
			return array('status' => "OK", 'result' => $result, 'message' => "");

		case '8':
			$result = Place::where('type_autumn', "1")->get();
			return array('status' => "OK", 'result' => $result, 'message' => "");

		case '9':
			$result = Place::where('type_winter', "1")->get();
			return array('status' => "OK", 'result' => $result, 'message' => "");
		
		default:
			return array('status' => "ERROR", 'result' => "", 'message' => $type);
	}
});

Route::post('Review/add/user', function(Request $request){
	$review = Review::where('email', $request->input("email"))
						->where('id_place', $request->input("id_place"))
						->count();
	if ($review > 0) {
		return array('status' => "ERROR", 'result' => array(), 'message' => "Đã tồn tại!");
	} else {
		$result = new Review;
		$result->author_name = $request->input("author_name");
		$result->email = $request->input("email");
		$result->profile_photo_url = $request->input("profile_photo_url");
		$result->id_place = $request->input("id_place");
		$result->rating = $request->input("rating");
		$result->text = $request->input("text");
		$result->time = $request->input("time");
		$result->save();
		return array('status' => "OK", 'result' => true, 'message' => "");
	}
});

Route::post('Review/edit/user', function(Request $request){
	$review = Review::where('email', $request->input("email"))
						->where('id_place', $request->input("id_place"))
						->first();
	if ($review == null) {
		return array('status' => "ERROR", 'result' => "", 'message' => "Không tồn tại!");
	} else {
		$review->rating = $request->input("rating");
		$review->text = $request->input("text");
		$review->time = $request->input("time");
		$review->save();
		return array('status' => "OK", 'result' => true, 'message' => "");
	}
});

Route::post('Review/delete/user', function(Request $request){
	$review = Review::where('email', $request->input("email"))
						->where('id_place', $request->input("id_place"))
						->first();
	if ($review == null) {
		return array('status' => "ERROR", 'result' => "", 'message' => "Không tồn tại!");
	} else {
		$review->delete();
		return array('status' => "OK", 'result' => true, 'message' => "Xóa thành công");
	}
});

Route::post('Review/get/id', function(Request $request){
	$result = Review::where('id_place', $request->input("id_place"))->get();
	return array('status' => "OK", 'result' => $result, 'message' => "");
});

Route::post('Place/edit/all', function(Request $request){
	$place = Place::where('id', $request->input("id"))->first();
	if ($place == null) {
		return array('status' => "ERROR", 'result' => false, 'message' => "Khong tim thay id");
	}
	$place->rating = $request->input("rating");
	if ($place->address === '') {
		$place->address = $request->input("address");
	}
	if ($place->phone === '') {
		$place->phone = $request->input("phone");
	}
	if ($place->location_lat == 0) {
		$place->location_lat = $request->input("location_lat");
	}
	if ($place->location_lng == 0) {
		$place->location_lng = $request->input("location_lng");
	}
	if ($place->opening_hours === '') {
		$place->opening_hours = $request->input("opening_hours");
	}
	if ($place->website === '') {
		$place->website = $request->input("website");
	}
	$place->save();
	return array('status' => "OK", 'result' => true, 'message' => "");
});

Route::post('Schedule/add/new', function(Request $request){
	$array = Schedule::where('email', $request->input("email"))->get()->toArray();
	$flag = true;

	$start = $request->input('date_start');
	$end = $request->input('date_end');
	foreach ($array as $item) {
		if (($start > $item['date_start'] && $start < $item['date_end']) 
			|| ($end > $item['date_start'] && $end < $item['date_end'])
			|| ($start <= $item['date_start'] && $end >= $item['date_end'])){
			$flag = false;
			break;
		}
	};

	if (!$flag) {
		return array('status' => "ERROR", 'result' => array(), 'message' => "Đã tồn tại!");
	} else {
		$schedule = new Schedule;
		$schedule->name = $request->input("name");
		$schedule->email = $request->input("email");
		$schedule->date_start = $request->input("date_start");
		$schedule->date_end = $request->input("date_end");
		$schedule->length = (int)(($request->input("date_end") - $request->input("date_start") + 86400)/86400);
		$schedule->save();
		//$result = Place::where('id', $favorite->idPlace)->get();
		return array('status' => "OK", 'result' => $schedule, 'message' => "");
	}
});

Route::post('Schedule/delete/id', function(Request $request){
	$schedule = Schedule::where('email', $request->input("email"))
						->where('id', $request->input("id"))
						->first();
	if ($schedule == null) {
		return array('status' => "ERROR", 'result' => array(), 'message' => "Không tồn tại!");
	} else {
		$schedule->delete();
		return array('status' => "OK", 'result' => array(), 'message' => "Xóa thành công");
	}
});

Route::post('Schedule/edit/new', function(Request $request){
	$schedule = Schedule::where('email', $request->input("email"))
			->where('id', $request->input("id"))
			->first();

	$flag = true;
	$array = Schedule::where('email', $request->input("email"))->get()->toArray();
	foreach ($array as $item) {
		if ($item['id'] === $request->input("id")) {
			continue;
		}
		$start = $request->input('date_start');
		$end = $request->input('date_end');
		if (($start > $item['date_start'] && $start < $item['date_end']) 
			|| ($end > $item['date_start'] && $end < $item['date_end'])
			|| ($start <= $item['date_start'] && $end >= $item['date_end'])){
			$flag = false;
			break;
		}
	};

	if (!$flag) {
		return array('status' => "ERROR", 'result' => array(), 'message' => "Đã tồn tại!");
	} else {
		$schedule->name = $request->input("name");
		$schedule->date_start = $request->input("date_start");
		$schedule->date_end = $request->input("date_end");
		$schedule->length = (int)(($request->input("date_end") - $request->input("date_start") + 86400)/86400);
		$schedule->save();
		//$result = Place::where('id', $favorite->idPlace)->get();
		return array('status' => "OK", 'result' => $schedule, 'message' => "");
	}
});

Route::post('Schedule/get/email', function(Request $request){
	$array = Schedule::where('email', $request->input("email"))->get()->toArray();
	foreach ($array as $item) { 
		$item['place'] = SchedulePlace::where('id_schedule', $item['id'])->count();
	}
	return array('status' => "OK", 'result' => $array, 'message' => "");
});

Route::post('Schedule/get/id', function(Request $request){
	$array = Schedule::where('email', $request->input("email"))
					->where('id', $request->input("id"))->get();
	return array('status' => "OK", 'result' => $array, 'message' => "");
});