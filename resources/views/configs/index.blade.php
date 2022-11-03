@extends('adminlte::page')

@section('title', '設定パラメータ一覧')

@section('content_header')
    <h1 class="m-0 text-dark">設定パラメータ一覧</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <form>
                        <div class="form-group">
                            <label for="tenant_id">テナント</label>
                            <select id="tenant_id" name="tenant_id">
                            @foreach ($tenants as $t)
                            <option value="{{ $t->id }}" @if (isset($tenant) && $t->id == $tenant->id) selected @endif >{{ $t->name }}</option>
                            @endforeach
                            </select>
                        </div>
                        <button class="btn btn-primary">検索</button>
                    </form>
                </div>
 @if (isset($configs))
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>番号</th>
                            <th>ドメイン</th>
                            <th>キー</th>
                            <th>値(文字列)</th>
                            <th>値(数値)</th>
                            <th>操作</th>
                        </tr>
                        @foreach ($configs as $config)
                        <tr>
                            <td>{{ $config->id }}</td>
                            <td>{{ $config->domain_id ? \App\Models\Domain::find($config->domain_id)->name : '' }}</td>
                            <td>{{ $config->ckey }}</td>
                            <td>{{ $config->cvalue }}</td>
                            <td>{{ $config->cnum }}</td>
                            <td>
                              <a href="{{ route('config.edit', [$tenant->id, $config->id]) }}" class="btn btn-primary">編集</a>
                              <a href="#" class="btn btn-danger" onclick="remove({{ $tenant->id }}, {{ $config->id }});">削除</a>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                    <a href="{{ route('config.create', $tenant->id) }}" class="btn btn-secondary">追加</a>
                </div>
                <div class="card-footer">
                    {{ $configs->links() }}
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
function remove(tenant, id) {
    if (!confirm('この操作は取り消しできません。実行してよろしいですか？')) {
         return;
    }
    const action = "config/" + tenant + "/delete/" + id;
    let form = document.getElementById('form');
    form.action = action;
    form.submit();
}
</script>
@endpush
