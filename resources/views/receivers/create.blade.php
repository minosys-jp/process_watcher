@extends('adminlte::page')

@section('title', '受信者作成')

@section('content_header')
    <h1 class="m-0 text-dark">受信者作成</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('receiver.store') }}" method="post">
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
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="aaa@example.com" value="{{ old('email') }}">
                            @error ('email')<small class="text-muted">{{ $message }}</small>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">作成</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

