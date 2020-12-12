<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="description" content="寶可夢資料管理系統">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>寶可夢資料管理系統</title>

        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/earlyaccess/notosanstc.css" rel="stylesheet">
        <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i' rel='stylesheet'>

        <!-- Base Styles -->
        <link href="{{ mix('/css/bootstrap.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.18.1/dist/bootstrap-table.min.css" rel="stylesheet">

        <!-- Font Awesome-->
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.1/css/all.css"> 

        <style>
            body {
                min-height: 75rem;
                padding-top: 4.5rem;
                font-family: 'Poppins', '蘋方-繁';
            }
        </style>
    </head>
    <body>
    
        <!-- Nav Bar -->
        <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
          <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('home') }}">Pokémon</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarCollapse">
              <ul class="navbar-nav me-auto mb-2 mb-md-0">
                <li class="nav-item">
                  <a class="nav-link @yield('nav_home')" href="{{ route('home') }}">首頁</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link @yield('nav_trainer')" href="{{ route('trainer') }}">訓練家</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link @yield('nav_pokemon')" href="{{ route('pokemon') }}">寶可夢</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link @yield('nav_relationship')" href="{{ route('relationship') }}">持有寶可夢</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link @yield('nav_battle_view')" href="{{ route('battleReport') }}">對戰統計</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link @yield('nav_battle')" href="{{ route('battle') }}">對戰管理</a>
                </li>
              </ul>
            </div>

          </div>
        </nav>
        
        <!-- Main -->
        <main class="container-fluid">
          <div class="p-5 rounded">
            @yield('content')
          </div>
        </main>
        
        <!-- Base JS -->
        <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Bootstrap Table -->
        <script src="https://unpkg.com/bootstrap-table@1.18.1/dist/bootstrap-table.min.js"></script>
        <script src="https://unpkg.com/bootstrap-table@1.18.1/dist/bootstrap-table-locale-all.min.js"></script>
        
        @yield('custom_script')
    </body>
</html>
