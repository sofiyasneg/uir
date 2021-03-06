<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html" >
<head>
    @yield('head')
    {!! HTML::style('css/bootstrap.css') !!}
    {!! HTML::style('css/full.css') !!}
    {!! HTML::style('css/materialadmin.css') !!}
    {!! HTML::style('css/material-design-iconic-font.min.css') !!}
    {!! HTML::style('css/materialadmin_demo.css') !!}
    {!! HTML::script('js/jquery.js') !!}
    <!-- Yandex.Metrika counter --> <script type="text/javascript"> (function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter41063559 = new Ya.Metrika({ id:41063559, clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true, trackHash:true }); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = "https://mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks"); </script> <noscript><div><img src="https://mc.yandex.ru/watch/41063559" style="position:absolute; left:-9999px;" alt="" /></div></noscript> <!-- /Yandex.Metrika counter -->
</head>
<body class="menubar-hoverable header-fixed" >
    <div id="base">
        <div class="offcanvas">
            </div>
<section>

    <nav class="navbar navbar-fixed-top style-primary">
        <div class="container">

            <div class="navbar-header">
                {{--<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">--}}
                {{--<span class="sr-only">Toggle navigation</span>--}}
                {{--<span class="icon-bar"></span>--}}
                {{--<span class="icon-bar"></span>--}}
                {{--<span class="icon-bar"></span>--}}
                {{--</button>--}}
                <a class="" href="{{URL::route('home')}}">
                    <img src="{{URL::asset('/img/AT2.png')}}" width="60px" alt="Главная" style=" padding-right: 10px;">
                </a>
            </div>

            <div id="navbar" class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    {{--                        <li><a href="{{URL::route('home')}}" class="btn">Главная</a></li>--}}
                    <li><a href="{{URL::route('tests')}}" class="btn">Тестирование</a></li>
                    <li><a href="{{URL::route('library_index')}}" class="btn">Библиотека</a></li>
                    <li><a href="{{URL::route('MT')}}" class="btn">Тьюринг</a></li>
                    <li><a href="{{URL::route('HAM')}}" class="btn">Марков</a></li>
                    <li><a href="{{URL::route('recursion_index')}}" class="btn">Рекурсия</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle btn" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <span class="glyphicon glyphicon-user"></span>
                            {{ Auth::user()['first_name'] }}
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu navbar-nav">
                            <li><a href="{{URL::route('personal_account')}}" class="btn">Личный кабинет</a></li>
                            <li><a href="{{URL::route('logout')}}" class="btn">Выйти</a></li>
                        </ul>
                    </li>
                    {{--<li><a href="{{URL::route('personal_account')}}" class="btn">Результаты</a></li>--}}
                    {{--<li><a href="{{URL::route('logout')}}" class="btn">Выйти</a></li>--}}
                </ul>
            </div>
        </div>
    </nav>

<div class="section-body" style="margin-top: 80px;">
@yield('content')
</div>
</section>

@yield('js-down')
{!! HTML::script('js/libs/jquery/jquery-1.11.2.min.js') !!}
{!! HTML::script('js/libs/jquery/jquery-migrate-1.2.1.min.js') !!}
{!! HTML::script('js/libs/bootstrap/bootstrap.min.js') !!}
</body>
</html>