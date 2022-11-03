@extends('adminlte::page')

@section('title', '管理者一覧')

@section('content_header')
    <h1 class="m-0 text-dark">管理者一覧</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>番号</th>
                            <th>Email</th>
                            <th>名前</th>
                            <th>操作</th>
                        </tr>
                        @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->name }}</td>
                            <td>
                              <a href="{{ route('user.edit', $user->id) }}" class="btn btn-primary">編集</a>
                              <a href="#" class="btn btn-danger" onclick="remove({{ $user->id }});">削除</a>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                <div class="card-footer">
                    {{ $users->links() }}
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
    if (!confirm('この操作は取り消しできません。実行してよろしいですか？')) {
         return;
    }
    const action = "user/" + id;
    let form = document.getElementById('form');
    form.action = action;
    form.submit();
}
</script>
@endpush
