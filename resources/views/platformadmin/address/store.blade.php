@extends('platformAdmin::layouts.app')

@section('title_name') Pocket Address Created @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Pocket Address Created</h1>
    </div>

    <p>{{ successInterjection() }} The address was created.</p>

    <p style="margin-top: 6%;">
      <a class="button" href="{{ route('platform.admin.address.index') }}">Return to Pocket Addresses</a>
    </p>
</div>

@endsection

