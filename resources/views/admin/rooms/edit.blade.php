@extends('layouts.backend')

@section('content')
<div class="card">
    <div class="card-header">Edit Room #{{ $room->id }}</div>
    <div class="card-body">
        <a href="{{ url('/admin/room') }}" title="Back"><button class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</button></a>
        <br />
        <br />

        @if ($errors->any())
            <ul class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        {!! Form::model($room, [
            'method' => 'PATCH',
            'url' => ['/admin/room', $room->id],
            'class' => 'form-horizontal',
            'files' => true
        ]) !!}

        @include ('admin/rooms.form', ['formMode' => 'edit'])

        {!! Form::close() !!}

    </div>
</div>
@endsection
