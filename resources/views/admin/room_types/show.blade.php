@extends('layouts.backend')

@section('content')
<div class="card">
    <div class="card-header">RoomType {{ $roomtype->id }}</div>
    <div class="card-body">

        <a href="{{ url('/admin/room_types') }}" title="Back"><button class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</button></a>
        <a href="{{ url('/admin/room_types/' . $roomtype->id . '/edit') }}" title="Edit RoomType"><button class="btn btn-primary btn-sm"><i class="fa fa-edit" aria-hidden="true"></i> Edit</button></a>
        {!! Form::open([
            'method'=>'DELETE',
            'url' => ['admin/room_types', $roomtype->id],
            'style' => 'display:inline'
        ]) !!}
            {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> Delete', array(
                    'type' => 'submit',
                    'class' => 'btn btn-danger btn-sm',
                    'title' => 'Delete RoomType',
                    'onclick'=>'return confirm("Confirm delete?")'
            ))!!}
        {!! Form::close() !!}
        <br/>
        <br/>

        <div class="table-responsive">
            <table class="table">
                <tbody>
                    <tr>
                        <th>ID</th><td>{{ $roomtype->id }}</td>
                    </tr>
                    <tr><th> Name </th><td> {{ $roomtype->name }} </td></tr><tr><th> Description </th><td> {{ $roomtype->description }} </td></tr><tr><th> Image Url </th><td> {{ $roomtype->image_url }} </td></tr>
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
