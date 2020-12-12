@extends('layouts.base')
@section('nav_home', 'active')
@section('content')
    <!--========== 小儀表  ==========-->
    <h2 class="mb-3">資訊面板</h2>
    <div class="row g-5">
        <!--寶可夢數量-->
        <div class="col-lg-3 col-md-6">
            <div class="card bg-blue-600">
                <div class="card-body">
                    <div class="text-blue-100">寶可夢</div>
                    <div class="text-blue-100"><span class="fs-2 text-white">{{$pkm_count}}</span>&nbsp;隻</div>
                </div>
            </div>
        </div>
        <!--#寶可夢數量-->

        <!--訓練家數量-->
        <div class="col-lg-3 col-md-6">
            <div class="card bg-teal-600">
                <div class="card-body">
                    <div class="text-teal-100">訓練家</div>
                    <div class="text-teal-100"><span class="fs-2 text-white">{{$trainer_count}}</span>&nbsp;位</div>
                </div>
            </div>
        </div>
        <!--#訓練家數量-->

        <!--總對戰數量-->
        <div class="col-lg-3 col-md-6">
            <div class="card bg-pink-500">
                <div class="card-body">
                    <div class="text-pink-100">總對戰</div>
                    <div class="text-pink-100"><span class="fs-2 text-white">{{$battle_count}}</span>&nbsp;場</div>
                </div>
            </div>
        </div>
        <!--#總對戰數量-->
        
        <!--勝場最多-->
        <div class="col-lg-3 col-md-6">
            <div class="card bg-orange-500">
                <div class="card-body">
                    <div class="text-orange-100">勝場最多</div>
                    <div class="text-orange-100"><span class="fs-2 text-white">{{$mvp_name}}</span>　<span class="fs-2 text-white">{{$mvp_wins}}</span>&nbsp;場</div>
                </div>
            </div>
        </div>
        <!--#勝場最多-->
    </div>
    <!--========== #小儀表 ==========-->

    <div class="row mt-5 g-3">
        <h2 class="mb-2">寶可夢圖鑑</h2>
        @foreach ($pokemons as $pokemon)
        <!--寶可夢Card-->
        <div class="col-lg-3 col-md-6">
            <div class="card mb-3" style="max-width: 540px;">
                <div class="row g-0">
                    <div class="col-md-4 d-flex">
                        <img class="img-fluid align-self-center" src="{{ asset('images/Poke_Ball.png') }}" alt="">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                        <h5 class="card-title"><span class="badge bg-blue-600">#{{ $pokemon['pokemon_id'] }}</span>　{{ $pokemon['pokemon_name'] }}</h5>
                        <p class="card-text">{{ $pokemon['description'] }}</p>
                        <p class="card-text"><small class="text-muted">屬性：{{ implode(', ',$pokemon['pokemon_type_name']) }}</small></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        <!--#寶可夢Card-->

        <div class="col-12">
            <a class="text-decoration-none" href="{{ route('pokemon') }}">
                <div class="card bg-blue-600">
                    <div class="card-body">
                        <div class="text-white text-center">查看全部</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

@stop

@section('custom_script')
    @include('layouts.sweetAlert')
@stop