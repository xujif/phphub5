@extends('layouts.default')

@section('title')
{{ lang('Recommended Sites') }} @parent
@stop

@section('content')

    <div class="box text-center site-intro rm-link-color">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        欢迎交换友链，只接受 PHP、Laravel 相关话题的站点，请见 <a style="text-decoration: underline;" href="https://phphub.org/topics/2453">关于酷站</a>
    </div>

    <div class="sites-index">

       

    </div>

    @include('layouts.partials.topbanner')

@stop
