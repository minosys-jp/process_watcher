@extends('adminlte::page')

@section('title', '設定パラメータ作成')

@section('content_header')
    <h1 class="m-0 text-dark">設定パラメータ作成</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('config.store', $tenant->id) }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="domain_id">ドメイン</label>
                            <select id="domain_id" name="domain_id">
                            <option value="">選択してください</option>
                            @foreach ($domains as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="ckey">キー</label>
                            <input type="text" id="ckey" name="ckey" class="form-control" placeholder="KEY" value="{{ old('ckey') }}">
                            @error ('ckeyh')<small class="text-muted">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label for="cvalue">値(文字列)</label>
                            <input type="text" id="cvalue" name="cvalue" class="form-control" placeholder="文字列値" value="{{ old('cvalue') }}">
                            @error ('name')<small class="text-muted">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label for="cvalue">値(数値)</label>
                            <input type="text" id="cnum" name="cnum" class="form-control" placeholder="数値" value="{{ old('cnum') }}">
                            @error ('cnum')<small class="text-muted">{{ $message }}</small>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">作成</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

