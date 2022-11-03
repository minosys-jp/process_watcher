@extends('adminlte::page')

@section('title', 'テナント一覧')

@section('content_header')
    <h1 class="m-0 text-dark">テナント一覧</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>番号</th>
                            <th>コード</th>
                            <th>名前</th>
                            <th>操作</th>
                        </tr>
                        @foreach ($tenants as $tenant)
                        <tr>
                            <td>{{ $tenant->id }}</td>
                            <td>{{ $tenant->code }}</td>
                            <td>{{ $tenant->name }}</td>
                            <td>
                              <a href="{{ route('tenant.edit', $tenant->id) }}" class="btn btn-primary">編集</a>
                              <a href="#" class="btn btn-danger" onclick="remove({{ $tenant->id }});">削除</a>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                <div class="card-footer">
                    {{ $tenants->links() }}
                </div>
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
    if (!confirm('テナントに属するすべてのドメインも削除されます。実行してよろしいですか？')) {
         return;
    }
    const action = "tenant/" + id;
    let form = document.getElementById('form');
    form.action = action;
    form.submit();
}
</script>
@endpush
