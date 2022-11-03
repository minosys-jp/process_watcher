@extends('adminlte::page')

@section('title', '管理者作成')

@section('content_header')
    <h1 class="m-0 text-dark">管理者作成</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('user.store') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="aaa@example.com" value="{{ old('email') }}">
                            @error ('email')<small class="text-muted">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label for="name">名前</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="山田太郎" value="{{ old('name') }}">
                            @error ('name')<small class="text-muted">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label for="password">パスワード</label>
                            <input type="password" id="password" name="password" class="form-control" value="{{ old('password') }}">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" value="{{ old('password_confirmation') }}">
                            @error ('password')<small class="text-muted">{{ $message }}</small>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">作成</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

