@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-border">
                        <tr>
                            <th>テナントID</th><th>テナント名</th><th>状態</th>
                        </tr>
@foreach ($mtenants as $mtenant)
                        <tr>
                            <td>{{ $mtenant['id'] }}</td>
                            <td>{{ $mtenant['name'] }}</td>
                            <td class="@if ($mtenant['status'] >= \App\Models\ModuleLog::FLG_BLACK1) red @endif ">{{ \App\Models\ModuleLog::FLG_NAMES[$mtenant['status']] }}</td>
                        </tr>
@endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@push ('css')
<style>
.red {
    font-weight: bold;
    color: red;
}
</style>
@endpush
