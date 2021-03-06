<table class="table">
    <thead>
    <tr>
        <th>Room Name</th>
        <th>Unit Price</th>
        <th>Total Price</th>
    </tr>
    </thead>
    <tbody>
    @php
        $total = 0;
    @endphp
    @foreach($reservation->roomTypes()->withPivot('price')->wherePivot('deleted_at', null)->get() as $room)
        <tr>
            <td>{{ $room->name }}</td>
            <td>{{ number_format($room->pivot->price, 2) }}</td>
            <td>{{ number_format(($room->pivot->price * $diff), 2) }}</td>
        </tr>
        @php
            $total += ($room->pivot->price * $diff);
        @endphp

    @endforeach
    </tbody>
</table>
<hr>
@php
    $tax = setting('tax');
    $taxTotal = $total * ($tax / 100);

    $total += $taxTotal;
@endphp
<h3 style="text-align:right"> {{$tax}}% VAT Total: {{ $taxTotal }} </h3>
<h3 style="text-align:right">Total Amount: {{ number_format($total, 2) }}</h3>
