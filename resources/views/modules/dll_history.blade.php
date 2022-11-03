@extends('adminlte::page')

@section('title', 'DLLグラフ履歴')

@section('content_header')
    <h1 class="m-0 text-dark">DLLグラフ履歴</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>子番号</th>
                            <th>バージョン</th>
                            <th>名称</th>
                            <th>更新日時</th>
                            <th>操作</th>
                        </tr>
                        @foreach ($children as $child)
                        <tr>
                            <td>{{ $child->child_id }}</td>
                            <td>{{ $child->child_version }}</td>
                            <td>{{ $child->childModule->name }}</td>
                            <td>{{ $child->created_at }}</td>
                            <td>
                                <a href="{{ route('module.exe_history', $child->child_id) }}" class="btn btn-primary">EXE群</a>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
