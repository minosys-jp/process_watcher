@extends('adminlte::page')

@section('title', '受信者一覧')

@section('content_header')
    <h1 class="m-0 text-dark">受信者一覧</h1>
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
 @if (isset($receivers))
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>番号</th>
                            <th>Email</th>
                            <th>操作</th>
                        </tr>
                        @foreach ($receivers as $receiver)
                        <tr>
                            <td>{{ $receiver->id }}</td>
                            <td>{{ $receiver->email }}</td>
                            <td> <a href="{{ route('receiver.edit', $receiver->id) }}" class="btn btn-primary">編集</a> <a href="#" class="btn btn-danger" onclick="remove({{ $receiver->id }});">削除</a> </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                <div class="card-footer">
                    {{ $receivers->links() }}
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
    const action = "receiver/" + id;
    let form = document.getElementById('form');
    form.action = action;
    form.submit();
}
</script>
@endpush
