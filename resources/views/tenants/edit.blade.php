@extends('adminlte::page')

@section('title', 'テナント編集')

@section('content_header')
    <h1 class="m-0 text-dark">テナント編集</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('tenant.update', $tenant->id) }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="code">コード</label>
                            <input type="text" id="code" name="code" class="form-control" placeholder="TENANT" value="{{ old('code', $tenant->code) }}">
                            @error ('code')<small class="text-muted">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label for="name">名前</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="テナント名称" value="{{ old('name', $tenant->name) }}">
                            @error ('name')<small class="text-muted">{{ $message }}</small>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">更新</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

