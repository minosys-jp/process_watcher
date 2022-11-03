@extends('adminlte::page')

@section('title', 'モジュール一覧')

@section('content_header')
    <h1 class="m-0 text-dark">モジュール一覧</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>{{ $hostname->name }}</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>番号</th>
                            <th>名称</th>
                            <th>バージョン</th>
                            <th>最新更新日時</th>
                            <th>操作</th>
                        </tr>
                        @foreach ($modules as $module)
                        <tr>
                            <td>{{ $module->id }}</td>
                            <td>{{ $module->name }}</td>
                            <td>{{ $module->version }}</td>
                            <td>{{ $module->updated_at }}</td>
                            <td>
                                <a href="{{ route('module.sha_history', $module->id) }}"class="btn btn-success">更新履歴</a>
                                <a href="{{ route('module.graph_history', $module->id) }}" class="btn btn-primary">親グラフ履歴</a>
                                <a href="{{ route('module.dll_history', $module->id) }}" class="btn btn-warning">DLLグラフ履歴</a>
                        </tr>
                        @endforeach
                    </table>
                </div>
                <div class="card-footer">
                    {{ $modules->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
