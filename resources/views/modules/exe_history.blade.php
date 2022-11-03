@extends('adminlte::page')

@section('title', '親EXE')

@section('content_header')
    <h1 class="m-0 text-dark">親EXE</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>親番号</th>
                            <th>名称</th>
                            <th>更新日時</th>
                        </tr>
                        @foreach ($parents as $parent)
                        <tr>
                            <td>{{ $parent->parent_id }}</td>
                            <td>{{ $parent->parentModule->name }}</td>
                            <td>{{ $parent->created_at }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
