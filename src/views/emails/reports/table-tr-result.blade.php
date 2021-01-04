<tr>
    @foreach($columns as $k => $column)
        <td>{{ \Illuminate\Support\Arr::get($stats,$k) }}</td>
    @endforeach
</tr>