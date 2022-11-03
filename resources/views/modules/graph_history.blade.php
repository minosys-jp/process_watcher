@extends('adminlte::page')

@section('title', 'グラフ履歴')

@section('content_header')
    <h1 class="m-0 text-dark">グラフ履歴</h1>
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
                            <th>操作</th>
                        </tr>
                        @foreach ($parents as $parent)
                        <tr>
                            <td>{{ $parent->parent_id }}</td>
                            <td>{{ $parent->parentModule->name }}</td>
                            <td>{{ $parent->updated_at }}</td>
                            <td>
                                <a href="{{ route('module.child_history', $parent->parent_id) }}" class="btn btn-primary">従属DLL群</a>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                <div class="card-footer">
                    {{ $parents->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
