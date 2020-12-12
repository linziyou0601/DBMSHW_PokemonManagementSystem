@extends('layouts.base')
@section('nav_relationship', 'active')
@section('content')

    <div class="row">
        <h2 class="mb-3">訓練家選擇</h2>
        @foreach ($trainers as $trainer)
        <!--訓練家Card-->
        <div class="col-lg-3 col-md-6">
            <a class="text-decoration-none" href="{{ route('relationship_mgr', $trainer['trainer_id']) }}">
                <div class="card bg-blue-600">
                    <div class="card-body">
                        <div class="text-blue-100">{{ $trainer['trainer_id'] }}</div>
                        <div class="text-blue-100"><span class="fs-3 text-white">{{ $trainer['trainer_name'] }}</span></div>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
        <!--#訓練家Card-->
    </div>
@stop

@section('custom_script')
    @include('layouts.sweetAlert')
@stop