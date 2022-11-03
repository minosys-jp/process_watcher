@extends('adminlte::page')

@section('title', '親子関係')

@section('content_header')
    <h1 class="m-0 text-dark">親子関係</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>子番号</th>
                            <th>名称</th>
                            <th>更新日時</th>
                        </tr>
                        @foreach ($children as $child)
                        <tr>
                            <td>{{ $child->child_id }}</td>
                            <td>{{ $child->childModule->name }}</td>
                            <td>{{ $child->created_at }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
