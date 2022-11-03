@extends('adminlte::page')

@section('title', 'ホスト名編集')

@section('content_header')
    <h1 class="m-0 text-dark">ホスト名編集</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('hostname.update', $hostname->id) }}" method="post">
                        @csrf
                        @method ('put')
                        <div class="form-group">
                            <label for="code">ホストコード</label>
                            <input type="text" id="code" name="code" class="form-control" value="{{ old('code', $hostname->code) }}" readonly>
                            @error ('code')<small class="text-muted">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label for="name">名前</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="山田太郎" value="{{ old('name', $hostname->name) }}">
                            @error ('name')<small class="text-muted">{{ $message }}</small>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">更新</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

