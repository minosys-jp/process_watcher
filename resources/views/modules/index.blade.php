@extends('adminlte::page')

@section('title', 'プロセス一覧')

@section('content_header')
    <h1 class="m-0 text-dark">プロセス一覧</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>番号</th>
                            <th>状態</th>
                            <th>更新日時</th>
                            <th></th>
                        </tr>
                        @foreach ($modules as $module)
                        <tr>
                            <td>{{ $module->id }}</td>
                            <?php $status = $module->getStatus(); ?>
                            <td class="@if ($status >= \App\Models\ModuleLog::FLG_BLACK1) red @endif ">{{ \App\Models\ModuleLog::FLG_NAMES[$status] }}</td>
                            <td>{{ $module->updated_at }}</td>
                            <td>
                                <a class="btn btn-primary" href="{{ route('module.sha_history', $module) }}">改変履歴</a>
                                <a class="btn btn-danger" href="{{ route('module.graph_history', $module) }}">DLL履歴</a>
                        </tr>
                        @endforeach
                    </table>
                </div>
                <div class="card-footer">
                    {{ $shas->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

@push('css')
<style>
.red {
    font-weight: bold;
    color: red;
}
</style>
@endpush
