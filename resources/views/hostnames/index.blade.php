@extends('adminlte::page')

@section('title', 'ホスト名一覧')

@section('content_header')
    <h1 class="m-0 text-dark">ホスト名一覧</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>番号</th>
                            <th>ホストコード</th>
                            <th>名称</th>
                            <th>操作</th>
                        </tr>
                        @foreach ($hostnames as $hostname)
                        <tr>
                            <td>{{ $hostname->id }}</td>
                            <td>{{ $hostname->code }}</td>
                            <td>{{ $hostname->name }}</td>
                            <td>
                                <a href="{{ route('hostname.show', $hostname->id) }}" class="btn btn-success">モジュール一覧</a>
                                <a href="{{ route('hostname.edit', $hostname->id) }}" class="btn btn-primary">編集</a>
                        </tr>
                        @endforeach
                    </table>
                </div>
                <div class="card-footer">
                    {{ $hostnames->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
