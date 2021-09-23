@extends('mail.layout')

@section('title', 'Pending Curator Order(s) | uPlaylist')

@section('preheader', 'Pending Curator Order(s) on uPlaylist')

@section('content')
<img src="https://uplaylist-static.s3.us-east-2.amazonaws.com/logo-dark.png" alt="header" />
<br />
<p>
    Dear {{$account->user->first_name}} {{$account->user->last_name}},
    <br />
    You have pending order(s) for your Curator account to review. If you do not change the status and leave feedback for these orders within {{$is_subscribed ? 72 : 48}} hours, and there are more than two orders, your account will be suspended.
</p>
<table role="presentation" border="0" cellpadding="0" cellspacing="0">
    <tbody>
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr>
                            <td>
                                <a href="{{config('app.fe_url').'login'}}" target="_blank" class="btn btn-primary">View Orders</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
@endsection
