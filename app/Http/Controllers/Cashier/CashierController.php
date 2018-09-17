<?php

namespace App\Http\Controllers\Cashier;


use App\Mail\ReservationApproved;
use App\Mail\ReservationRejected;
use App\Reservation;
use App\ReservationRoom;
use App\Room;
use App\RoomType;
use App\Service;
use App\Transaction;
use Auth;
use Carbon\Carbon;
use PDF;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Mail;

class CashierController
{
    public function index()
    {
        $user = Auth::user();
        $reservations = Reservation::whereDate('start_date', Carbon::today())->where('status', 'approved')->get();

        return view('cashier.dashboard', compact('user', 'reservations'));
    }

    public function show($id)
    {
        $reservation = Reservation::find($id);
        $startDate = \DateTime::createFromFormat('Y-m-d', $reservation->start_date);
        $endDate = \DateTime::createFromFormat('Y-m-d', $reservation->end_date);
        $adults = $reservation->adults;
        $services = Service::all();

        $reservations = Reservation::whereBetween('start_date', [$startDate, $endDate])->orWhereBetween('end_date', [$startDate, $endDate])->get();

        //this will hold the value of room id and its corresponding current quantity in the reservations selected
        $rooms = [];
        foreach ($reservations as $reservation) {
            $roomTypes = $reservation->roomTypes()->pluck('room_types.id');
            foreach ($roomTypes as $room) {
                $rooms[$room] = 0;
            }
        }

        foreach ($reservations as $reservation) {
            foreach($reservation->roomTypes()->get() as $type) {
                foreach($rooms as $key => $room) {
                    if($type->id == $key) {
                        $rooms[$key] = $room + 1 ;
                    }
                }
            }
        }

        $roomTypes = RoomType::whereIn('id', array_keys($rooms))->get();
        $dontDisplay = [];

        foreach($roomTypes as $type) {
            $max = $type->rooms()->where('status', '!=', 'inactive')->count();
            if($max <= $rooms[$type->id]) {
                $dontDisplay[] = $type->id;
            }
        }

        $roomTypes = RoomType::has("validRooms")->whereNotIn('id', $dontDisplay)->where('capacity', '>=', $adults)->get();

        $diff = date_diff($startDate, $endDate);
        $diff = $diff->days;

        return view('cashier.show', compact('reservation', 'diff', 'roomTypes', 'services'));
    }

    public function deposits()
    {
        $reservations = Reservation::where('status', 'waiting_for_approval')->get();

        return view('cashier.deposit', compact('reservations'));
    }

    public function view($id)
    {
        $reservation = Reservation::find($id);
        $startDate = \DateTime::createFromFormat('Y-m-d', $reservation->start_date);
        $endDate = \DateTime::createFromFormat('Y-m-d', $reservation->end_date);

        $diff = date_diff($startDate, $endDate);
        $diff = $diff->days;

        return view('cashier.view', compact('reservation', 'diff'));
    }

    public function approve(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'amount' => 'required|numeric',
        ]);

        $id = $request->get('id');
        $amount = $request->get('amount');

        $reservation = Reservation::findOrFail($id);
        $reservation->status = "approved";
        $reservation->save();

        $transaction = new Transaction();
        $transaction->item = "Bank Deposit";
        $transaction->price = $amount;
        $transaction->reservation_id = $reservation->id;
        $transaction->status = 'paid';
        $transaction->save();

        Mail::to($reservation->user()->first()->email)->send(new ReservationApproved($reservation));

        Session::flash('flash_message', 'Reservation approved');
        return redirect('/cashier/deposit');
    }

    public function reject(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'reason' => 'required',
        ]);

        $id = $request->get('id');
        $reason = $request->get('reason');

        $reservation = Reservation::findOrFail($id);
        $reservation->status = "pending";
        $reservation->deposit_slip = '';
        $reservation->save();

        Mail::to($reservation->user()->first()->email)->send(new ReservationRejected($reservation, $reason));

        Session::flash('flash_message', 'Reservation rejected');
        return redirect('/cashier/deposit');
    }

    public function checkIn(Request $request)
    {
        $rooms = $request->get('rooms');

        if (count($rooms) !== count(array_unique($rooms))) {
            Session::flash('error_message', 'Duplicate rooms detected, please select different rooms');
            return redirect()->back();
        }

        $request->validate([
            'rooms.*' => 'required|not_in:-1,'
        ], [
            'rooms.*' => 'Invalid Room Number'
        ]);

        $id = $request->get('reservation_id');
        $ids = $request->get('ids');
        foreach($rooms as $key => $room) {
            $load = ReservationRoom::find($ids[$key]);
            $load->room_number_id = $room;
            $load->save();

            //set room as in used
            $roomNumber = Room::find($room);
            $roomNumber->status = "checked-in";
            $roomNumber->save();
        }


        $reservation = Reservation::find($id);
        $reservation->check_in = Carbon::now();
        $reservation->status = "checked_in";
        $reservation->save();

        Session::flash('flash_message', 'Check in successful');
        return redirect()->back();
    }

    public function reservation(Request $request)
    {
        $keyword = $request->get('search');
        $perPage = 25;

        if (!empty($keyword)) {
            $reservations = Reservation::where('start_date', 'LIKE', "%$keyword%")
                ->orWhere('end_date', 'LIKE', "%$keyword%")
                ->orWhere('status', 'LIKE', "%$keyword%")
                ->orWhere('user_id', 'LIKE', "%$keyword%")
                ->orWhere('deposit_slip', 'LIKE', "%$keyword%")
                ->latest()->paginate($perPage);
        } else {
            $reservations = Reservation::latest()->paginate($perPage);
        }

        return view('cashier.reservations', compact('reservations'));
    }

    public function printReservation($id)
    {
        $reservation = Reservation::find($id);
        $startDate = \DateTime::createFromFormat('Y-m-d', $reservation->start_date);
        $endDate = \DateTime::createFromFormat('Y-m-d', $reservation->end_date);

        $transactions = Transaction::where('reservation_id', $reservation->id)->where('item', '!=', 'Bank Deposit')->where('item', '!=', 'Balance Payment')->get();

        $diff = date_diff($startDate, $endDate);
        $diff = $diff->days;

        $pdf = PDF::loadView('reports.reservation', compact('reservation', 'diff', 'transactions'));
        return $pdf->stream();
    }

    public function checkOut(Request $request)
    {
        $id = $request->get('id');

        $reservation = Reservation::find($id);
        $reservation->status = "checked_out";
        $startDate = \DateTime::createFromFormat('Y-m-d', $reservation->start_date);
        $endDate = \DateTime::createFromFormat('Y-m-d', $reservation->end_date);
        $diff = date_diff($startDate, $endDate)->days;
        $reservation->save();


        $dt = new \DateTime();
        $endDate = \DateTime::createFromFormat('Y-m-d', $reservation->end_date);
        $endDate->setTime(12, 0);
        $indicator = $endDate->diff($dt)->format("%r%a");
        $difference = $endDate->diff($dt)->h;
        //balance computation
        $total = 0;
        $totalPaid = 0;
        foreach($reservation->roomTypes()->withPivot('price')->get() as $room) {
            $total += ($room->pivot->price * $diff);
        }

        if ($indicator >= 0) {
            $transaction = new Transaction();
            $transaction->name = "Penalty (Overstay)";
            $transaction->price = $difference * 100;
            $transaction->save();
        }

        //set all room to ready
        $rooms = $reservation->room()->get();
        foreach($rooms as $room) {
            $room->status = "ready";
            $room->save();
        }

        foreach($reservation->transactions()->get() as $transaction) {
            if($transaction->item != "Bank Deposit") {
                $total += $transaction->price;
            }
            $transaction->status = "paid";
            $transaction->save();

            if ($transaction->status == "paid") {
                $totalPaid += $transaction->price;
            }
        }

        // insert balance payment on the transactions table
        if ($totalPaid < $total) {
            $balance = $total - $totalPaid;

            $transaction = new Transaction();
            $transaction->item = "Balance Payment";
            $transaction->reservation_id = $id;
            $transaction->price = $balance;
            $transaction->status = "paid";
            $transaction->save();
        }

        Session::flash('flash_message', 'Check out successful');
        return redirect()->back();
    }

    public function getAvailableRooms(Request $request)
    {
        $roomTypeID = $request->get('room_id');

        $room = RoomType::find($roomTypeID);
        $rooms = $room->rooms()->where('status', 'ready')->pluck('number','id');

        return $rooms->toJson();
    }

    public function updateRoom(Request $request)
    {
        $roomID = $request->get('number');
        $reservationID = $request->get('reservation_id');

        $roomLoad = ReservationRoom::find($reservationID);
        $previousRoomID = $roomLoad->room_number_id;
        $roomLoad->room_number_id = $roomID;
        $roomLoad->save();

        $room = Room::find($previousRoomID);
        $room->status = 'ready';
        $room->save();

        $room = Room::find($roomID);
        $room->status = 'checked-in';
        $room->save();

        Session::flash('flash_message', 'Room successfully updated');
        return redirect()->back();
    }

    public function deleteRoom(Request $request)
    {
        $reservationID = $request->get('reservation_id');

        $reservationLoad = ReservationRoom::find($reservationID);
        $roomID = $reservationLoad->room_number_id;
        $reservationLoad->delete();

        if ($roomID != 0) {
            $room = Room::find($roomID);
            $room->status = "ready";
            $room->save();
        }

        Session::flash('flash_message', 'Room successfully deleted');
        return redirect()->back();
    }

    public function addRoom(Request $request)
    {
        $roomID = $request->get('room_id');
        $reservationID = $request->get('reservation_id');
        $roomTypeID = $request->get('room_type_id');

        //add the room to the reservation
        $type = RoomType::find($roomTypeID);
        $reservationLoad = new ReservationRoom();
        $reservationLoad->reservation_id = $reservationID;
        $reservationLoad->price = $type->daily_rate;
        $reservationLoad->room_id = $roomTypeID;
        $reservationLoad->room_number_id = $roomID;
        $reservationLoad->save();

        //set the room to "checked_in"
        $room = Room::find($roomID);
        $room->status = "checked-in";
        $room->save();

        Session::flash('flash_message', 'Room successfully added');
        return redirect()->back();
    }

    public function addServices(Request $request)
    {
        $name = $request->get('name');
        $price = $request->get('price');
        $quantity = $request->get('quantity');
        $reservationID = $request->get('reservation_id');

        $transaction = new Transaction();
        $transaction->item = $name;
        $transaction->price = $price * $quantity;
        $transaction->status = "unpaid";
        $transaction->reservation_id = $reservationID;
        $transaction->save();

        Session::flash('flash_message', 'Item successfully added');
        return redirect()->back();
    }

    public function removeService($id)
    {
        $transaction = Transaction::find($id);
        $transaction->delete();

        Session::flash('flash_message', 'Item successfully removed');
        return redirect()->back();
    }

    public function settleTransaction($id)
    {
        $transaction = Transaction::find($id);
        $transaction->status = "paid";
        $transaction->save();

        Session::flash('flash_message', 'Transaction successfully settled');
        return redirect()->back();
    }

    public function cancelReservation(Request $request)
    {
        $id = $request->get('id');
        $reservation = Reservation::find($id);
        $reservation->status = "cancelled";
        $reservation->save();

        //Delete all relation
        ReservationRoom::where('reservation_id', $reservation->id)->delete();

        Session::flash('flash_message', 'Reservation Cancelled!');
        return redirect()->back();
    }
}