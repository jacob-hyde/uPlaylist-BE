@extends('mail.layout')

@section('title', 'Pending Curator Order(s) | uPlaylist')

@section('preheader', 'Pending Curator Order(s) on uPlaylist')

@section('content')
<img src="https://uplaylist-static.s3.us-east-2.amazonaws.com/logo-dark.png" alt="header" />
<br />
<p>
    Dear {{$account->user->first_name}} {{$account->user->last_name}},
    <br />
    You have more than 2 orders in the past {{$is_subscribed ? 72 : 48}} working hours in which the status was not changed. Your Curator account has been suspended.
    <br />
    To repeal this suspension please email <a href="mailto:support@uplaylist.com">support@uplaylist.com</a>
</p>
@endsection
