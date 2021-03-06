<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\RoomType;
use Illuminate\Http\Request;
use Imgur;

class RoomTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $perPage = 25;

        if (!empty($keyword)) {
            $roomtypes = RoomType::where('name', 'LIKE', "%$keyword%")
                ->orWhere('description', 'LIKE', "%$keyword%")
                ->orWhere('image_url', 'LIKE', "%$keyword%")
                ->orWhere('daily_rate', 'LIKE', "%$keyword%")
                ->orWhere('weekly_rate', 'LIKE', "%$keyword%")
                ->orWhere('capacity', 'LIKE', "%$keyword%")
                ->latest()->with('rooms')->paginate($perPage);
        } else {
            $roomtypes = RoomType::latest()->with('rooms')->paginate($perPage);
        }

        return view('admin/room_types.index', compact('roomtypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin/room_types.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'description' => 'required',
            'weekly_rate' => 'required|numeric',
            'daily_rate' => 'required|numeric',
            'image_url' => 'required|image',
            'capacity' => 'required'
        ]);
        $requestData = $request->all();
        $image = Imgur::upload($request->file('image_url'));
        $requestData['image_url'] = $image->link();

        RoomType::create($requestData);

        return redirect('admin/room_types')->with('flash_message', 'RoomType added!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $roomtype = RoomType::findOrFail($id);

        return view('admin/room_types.show', compact('roomtype'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $roomtype = RoomType::findOrFail($id);

        return view('admin/room_types.edit', compact('roomtype'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'description' => 'required',
            'weekly_rate' => 'required|numeric',
            'daily_rate' => 'required|numeric',
            'image_url' => 'required|image',
            'capacity' => 'required'
        ]);
        $requestData = $request->all();
        $image = Imgur::upload($request->file('image_url'));
        $requestData['image_url'] = $image->link();

        $roomtype = RoomType::findOrFail($id);
        $roomtype->update($requestData);

        return redirect('admin/room_types')->with('flash_message', 'RoomType updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        RoomType::destroy($id);

        return redirect('admin/room_types')->with('flash_message', 'RoomType deleted!');
    }
}
