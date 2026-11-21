<div class="table-wrap">
    <table>
        <thead><tr>@foreach ($headers as $header)<th>{{ $header }}</th>@endforeach @if (!empty($actions))<th>Aksi</th>@endif</tr></thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    @foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach
                    @if (!empty($actions))
                        <td class="action-cell"><div class="action-buttons"><a class="btn-action-edit" href="#">Edit</a><button class="btn-action-delete" type="button" aria-label="Hapus"><i class="fa-solid fa-trash"></i></button></div></td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
