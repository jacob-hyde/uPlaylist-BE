@extends('mail.layout')

@section('title', 'Curator Order Status Changed | uPlaylist')

@section('preheader', 'Your curator order status has changed on uPlaylist')

@section('content')
<img src="https://uplaylist-static.s3.us-east-2.amazonaws.com/logo-dark.png" alt="header" />
<p class="uppercase text-center">
    Your Curator order has been updated!
</p>
<hr />
<p>
    <strong>Track Name:</strong> <a href="{{$order->user_track->url}}">{{$order->user_track->name}}</a>
</p>
<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body padding-td">
    <tbody>
        <tr>
            <td width="25%">
                <img src="{{$order->playlist->img_url}}" alt="playlist image" />
            </td>
            <td width="50%">
                <p style="line-height: 1.5;">
                    <span><strong>Spotify Username:</strong> {{$order->playlist->spotify_id}}</span>
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
    </tbody>
</table>
<p><strong>Status:</strong> {{ucfirst($order->status)}}</p>
<p><strong>Feedback:</strong> {{$order->feedback}}</p>
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
