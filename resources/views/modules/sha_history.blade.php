@extends('adminlte::page')

@section('title', '更新履歴')

@section('content_header')
    <h1 class="m-0 text-dark">更新履歴</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="post">
                        @csrf
                        @method('put')
                        <div class="form-group">
                            <label for="status">プロセス状態: {{ \App\Models\ProgramModule::FLG_NAMES[$module->status] }}/{{ $module->notified ? '設定済み' : '設定待ち' }}</label>
                            <select id="status" name="status">
                                <option value="1" selected>Black</option>
                                <option value="2">White</option>
                            </select>
                        </div>
                        <button class="btn btn-primary">設定</button>
                    </form>
                    <table class="table">
                        <tr>
                            <th>番号</th>
                            <th>バージョン</th>
                            <th>ハッシュ値</th>
                            <th>更新日時</th>
                        </tr>
                        @foreach ($shas as $sha)
                        <tr>
                            <td>{{ $sha->id }}</td>
                            <td>{{ $sha->version }}</td>
                            <td>{{ $sha->finger_print }}</td>
                            <td>{{ $sha->created_at }}</td>
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
