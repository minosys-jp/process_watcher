@extends('adminlte::page')

@section('title', 'ドメイン一覧')

@section('content_header')
    <h1 class="m-0 text-dark">ドメイン一覧</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <form>
                        <div class="form-group">
                            <label for="tenant">テナント</label>
                            <select id="tenant" name="tenant">
                            @foreach ($tenants as $t)
                            <option value="{{ $t->id }}" @if (isset($tenant) && $t->id == $tenant->id) selected @endif >{{ $t->name }}</option>
                            @endforeach
                            </select>
                        </div>
                        <button class="btn btn-primary">検索</button>
                    </form>
                </div>
 @if (isset($domains))
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>番号</th>
                            <th>コード</th>
                            <th>名前</th>
                            <th>状態</th>
                            <th>操作</th>
                        </tr>
                        @foreach ($domains as $domain)
                        <tr>
                            <td>{{ $domain->id }}</td>
                            <td>{{ $domain->code }}</td>
                            <td>{{ $domain->name }}</td>
                            <td class="@if ($domain->status >= \App\Models\ModuleLog::FLG_BLACK1) red @endif">{{ \App\Models\ModuleLog::FLG_NAMES[$domain->status] }}</td>
                            <td>
                              <a href="{{ route('hostname.index', $domain->id) }}" class="btn btn-success">ホスト一覧</a>
                              <a href="{{ route('domain.edit', $domain->id) }}" class="btn btn-primary">編集</a>
                              <a href="#" class="btn btn-danger" onclick="remove({{ $domain->id }});">削除</a>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                <div class="card-footer">
                    {{ $domains->links() }}
                </div>
@endif
            </div>
        </div>
    </div>
    <form id="form" action="" method="post" class="d-none">
        @csrf
        @method ('delete')
    </form>
@stop

@push ('js')
<script>
function remove(id) {
    if (!confirm('この操作は取り消しできません。実行してよろしいですか？')) {
         return;
    }
    const action = "domain/" + id;
    let form = document.getElementById('form');
    form.action = action;
    form.submit();
}
</script>
@endpush

@push ('css')
<style>
.red {
    font-weight: bold;
    color: red;
}
</style>
@endpush
