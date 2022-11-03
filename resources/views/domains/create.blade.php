@extends('adminlte::page')

@section('title', 'ドメイン作成')

@section('content_header')
    <h1 class="m-0 text-dark">ドメイン作成</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('domain.store') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="tenant_id">テナント</label>
                            <select id="tenant_id" name="tenant_id">
                            @foreach ($tenants as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="code">コード</label>
                            <input type="text" id="code" name="code" class="form-control" placeholder="DOMAIN" value="{{ old('code') }}">
                            @error ('code')<small class="text-muted">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label for="name">名前</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="ドメイン名称" value="{{ old('name') }}">
                            @error ('name')<small class="text-muted">{{ $message }}</small>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">作成</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

