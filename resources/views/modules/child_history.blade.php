@extends('adminlte::page')

@section('title', '構成情報')

@section('content_header')
    <h1 class="m-0 text-dark">構成情報</h1>
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
                            <th></th>
                        </tr>
                        @foreach ($graphs as $graph)
                        <?php $child = $graph->child; ?>
                        <tr>
                            <td>{{ $child->id }}</td>
                            <td>{{ $child->name }}</td>
                            <td><a class="btn btn-primary" href="{{ route('module.sha_history', $child) }}">改変履歴</a></td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                <div class="card-footer">
                  {{ $graphs->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
