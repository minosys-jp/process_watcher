@extends('adminlte::page')

@section('title', '受信者編集')

@section('content_header')
    <h1 class="m-0 text-dark">受信者編集</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('receiver.update', $receiver->id) }}" method="post">
                        @csrf
                        @method ('put')
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="aaa@example.com" value="{{ old('email', $receiver->email) }}">
                            @error ('email')<small class="text-muted">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label for="domain_id">対象ドメイン</label>
                            <?php $col = $receiver->domains()->pluck('id'); ?>
                            <select id="domain_id" name="domain_id[]" rows="10" multiple>
                            @foreach ($domains as $d)
                            <option value="{{ $d->id }}" @if ($col->search($d->id) !== FALSE) selected @endif >{{ $d->name }}</option>
                            @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">更新</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

