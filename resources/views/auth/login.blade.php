@extends('layouts.app')

@section('content')
    <div class="content-box">
        @if (Session::has('flash_message'))
            <div class="container-fluid">
                <div class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{ Session::get('flash_message') }}
                </div>
            </div>
        @endif
        <div class="logo"><img src="/images/logo.jpg" alt="platanus logo" class="icon"></div>
        <div class="content-body">

            <form method="POST" action="{{ route('login') }}" aria-label="{{ __('Login') }}" class="loginForm">
                @csrf

                <div class="form-group">
                    <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" placeholder="Email" name="email" value="{{ old('email') }}" required autofocus>

                    @if ($errors->has('email'))
                        <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                    @endif
                </div>

                <div class="form-group">
                    <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="Password" name="password" required>

                    @if ($errors->has('password'))
                        <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                    @endif
                </div>

                <div class="form-group">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                            <label class="form-check-label" for="remember">
                                {{ __('Remember Me') }}
                            </label>
                        </div>
                        <button type="submit" class="btn w-25 btn-success">
                            {{ __('Login') }}
                        </button>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <div class="text-center d-flex justify-content-between">
                        <a class="btn btn-link" href="{{ route('password.request') }}">
                            {{ __('Forgot Your Password?') }}
                        </a>
                        <a class="btn btn-link" href="/register">
                            Don't have an account yet?
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
