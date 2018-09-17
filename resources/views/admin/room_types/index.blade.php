@extends('layouts.backend')

@section('content')
<div class="card">
    <div class="card-header">Room Types</div>
    <div class="card-body">
        <a href="{{ url('/admin/room_types/create') }}" class="btn btn-custom-primary btn-sm" title="Add New reservation">
            <i class="fa fa-plus" aria-hidden="true"></i> Add New
        </a>

        {!! Form::open(['method' => 'GET', 'url' => '/admin/room_types', 'class' => 'form-inline my-2 my-lg-0 float-right', 'role' => 'search'])  !!}
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Search..." value="{{ request('search') }}">
            <span class="input-group-append">
                <button class="btn btn-secondary" type="submit">
                    <i class="fa fa-search"></i>
                </button>
            </span>
        </div>
        {!! Form::close() !!}

        <br/>
        <br/>
        <div class="table-responsive">
            <table class="table table-borderless">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Room Name</th>
                        <th class="w-25">Description</th>
                        <th>No. of Pax</th>
                        <th>Daily Rate</th>
                        <th class="w-25">Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($roomtypes as $item)
                    <tr>
                        <td>{{ $loop->iteration or $item->id }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->capacity }}</td>
                        <td>PHP {{ $item->daily_rate }}</td>
                        <td><img class="w-100" src="{{ $item->image_url }}" alt=""></td>
                        <td>
                            <a href="{{ url('/admin/room_types/' . $item->id) }}" title="View RoomType"><button class="btn btn-info btn-sm"><i class="fa fa-eye" aria-hidden="true"></i></button></a>
                            <a href="{{ url('/admin/room_types/' . $item->id . '/edit') }}" title="Edit RoomType"><button class="btn btn-primary btn-sm"><i class="fa fa-edit" aria-hidden="true"></i></button></a>
                            {!! Form::open([
                                'method' => 'DELETE',
                                'url' => ['/admin/room_types', $item->id],
                                'style' => 'display:inline'
                            ]) !!}
                                {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i>', array(
                                        'type' => 'submit',
                                        'class' => 'btn btn-danger btn-sm',
                                        'title' => 'Delete reservation',
                                        'onclick'=>'return confirm("Confirm delete?")'
                                )) !!}
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="pagination-wrapper"> {!! $roomtypes->appends(['search' => Request::get('search')])->render() !!} </div>
        </div>
    </div>
</div>
@endsection
