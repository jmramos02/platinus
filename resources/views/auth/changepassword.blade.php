@extends($layout)

@section('content')
    <div class="view-content">
        <h1 class="mb-3">Change Password</h1>
        <div class="d-flex justify-content-between flex-wrap">
            {{ Form::open(['url' => '/auth/change-password', 'method' => 'POST']) }}
                {{ Form::label('old_password', 'Old Password') }}
                {{ Form::password('old_password', ['required' => 'true']) }}
                {{ Form::label('password', 'New Password') }}
                {{ Form::password('password', ['required' => 'true']) }}
                {{ Form::label('password_confirmation', 'Confirm New Password') }}
                {{ Form::password('password_confirmation', ['required' => 'true']) }}
                <button class="btn btn-success mt-3 d-block">Save</button>
            {{ Form::close() }}
        </div>
    </div>
@endsection
