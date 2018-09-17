@extends('layouts.backend')

@section('content')
<div class="card">
    <div class="card-header">Create New Transaction</div>
    <div class="card-body">
        <a href="{{ url('/admin/transactions') }}" title="Back"><button class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</button></a>
        <br />
        <br />

        @if ($errors->any())
            <ul class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        {!! Form::open(['url' => '/admin/transactions', 'class' => 'form-horizontal', 'files' => true]) !!}

        @include ('admin/transactions.transactions.form', ['formMode' => 'create'])

        {!! Form::close() !!}

    </div>
</div>
@endsection
