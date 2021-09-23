@extends('mail.layout')

@section('title', 'Order Confirmtion | uPlaylist')

@section('preheader', 'Your order confirmation from Artist Republik')

@section('content')
<img src="https://uplaylist-static.s3.us-east-2.amazonaws.com/logo-dark.png" alt="header" />
<p class="uppercase text-center">
    Thanks for your order!
    <br>
    We will email you when the status of your order changes
</p>
<hr />
<p>
    <strong>Track Name:</strong> <a href="{{$track->url}}">{{$track->name}}</a>
</p>
<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body padding-td">
    <tbody>
        @foreach($orders as $order)
        <tr>
            <td width="25%">
                <img src="{{$order->playlist->img_url}}" alt="playlist image" />
            </td>
            <td width="50%">
                <p style="line-height: 1.5;">
                    <span><strong>Spotify Username:</strong> {{$order->playlist->username}}</span>
                    <br />
                    <span><strong>Playlist Name:</strong> {{$order->playlist->name}}</span>
                    <br />
                    <span><strong>Genre:</strong> {{collect($order->playlist->genres)->pluck("name")->join(", ") }}</span>
                    <br />
                    <span><strong>Followers:</strong> {{$order->playlist->followers}}</span>
                </p>
            </td>
            <td width="25%" class="text-right">
                <h3>${{convertCentsToDollars($order->playlist_price)}}</h3>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<hr>
<p style="text-align: right">
    Total: ${{$total}}
</p>
<table role="presentation" border="0" cellpadding="0" cellspacing="0">
    <tbody>
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr>
                            <td> <a href="mailto:support@uplaylist.com" target="_blank" class="btn btn-primary">File a Complaint</a> </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
@endsection
