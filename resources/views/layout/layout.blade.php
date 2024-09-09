<!DOCTYPE html>
<html>
<head>
    <title>{{isset($page_title)?$page_title:'Home'}} - Vitana</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{asset('css/form.css')}}"/>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/select2.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/css/styles.css') }}" />
    <link rel="stylesheet" href="{{asset('css/bootstrap-datepicker3.css')}}"/>
    <link rel="stylesheet" href="{{asset('css/bootstrap-datepicker3.min.css')}}"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/all.min.css" />
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">


    {{-- Signature --}}
    {{-- <link rel="stylesheet" type="text/css" href="http://keith-wood.name/css/jquery.signature.css"> --}}

 
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark no-print">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard/patients">Vital Health Services</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @if(\Auth::check())
                    @foreach(\Config('constants.menu') as $key => $val)
                        @if(\in_array(\Auth::user()->role, $val['access_roles']) && $val['label']!='Users' &&
                        $val['label']!='Specialists' && 
                        $val['label']!='Doctors' && 
                        $val['label']!='Insurances' && 
                        $val['label']!='Programs' && 
                        $val['label']!='Physicians' && 
                        $val['label']!='Clinics' && 
                        $val['label']!='Clinic Admins')
                        <li class="nav-item">
                            <a class="nav-link {{ (\Request::segment(2)==$val['route']) ? 'text-info' : ''}}" aria-current="page" href="{{url('/dashboard/'.$val['route'])}}">{{$val['label']}}</a>
                        </li>
                        @endif
                    @endforeach
                    @endif
                </ul>
                <ul class="navbar-nav">
                    <div class="dropdown">
                        <div class="dropdown">
                          <button class="dropbtn"><i class="fa fa-cog" ></i></button>
                          <div class="dropdown-content">

                            @foreach(\Config('constants.menu') as $key => $val)
                            
                                @if(\in_array(\Auth::user()->role, $val['access_roles']) && $val['label']!='Patients' && $val['label']!='Question Survey')
                        
                                    <a class="{{ (\Request::segment(2)==$val['route']) ? 'text-info' : ''}}" aria-current="page" href="{{url('/dashboard/'.$val['route'])}}">
                                    {{$val['label']}}</a>
                                @endif

                            @endforeach
                           
                          </div>
                        </div>
                    </div>
                </ul>
                <!-- <div class="dropdown">
                  <button class="dropbtn">{{ \Auth::check() ? \Auth::user()->first_name.' '.\Auth::user()->mid_name.' '.\Auth::user()->last_name:'' }}</button>
                  <div class="dropdown-content">
                    <a class="nav-link text-white" href="{{ url('/dashboard/logout') }}" id="navbarDropdown"role="button">
                        <i class="fa fa-power-off" style="color:black"></i>&emsp;&emsp;
                    </a>Logout
                  </div>
                </div> -->
                  <ul class="navbar-nav">
                    <li class="nav-item dropdown ">
                        
                            <a class="nav-link text-white" href="{{ url('/dashboard/logout') }}" id="navbarDropdown"
                            role="button">
                            
                             {{ \Auth::check() ? \Auth::user()->first_name.' '.\Auth::user()->mid_name.' '.\Auth::user()->last_name:'' }}&nbsp;
                             <i class="fa fa-power-off" style="color:white">  </i>
                            </a>
                        
                    </li>
                </ul>
                

            </div>
        </div>
    </nav>
    @yield('content')
    @php
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$param = \Request::segment(2);


if($param === 'patients')
{
@endphp
<div class="container">
        <!-- Modal -->
    <div class="modal fade bd-example-modal-xl" id="data_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-xl">
                <div class="modal-content">
                  ...
                </div>
              </div>
    </div>
</div>
   @php }    @endphp
    <div class="container">
        <!-- Modal -->
        <div class="modal fade" id="data_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datepicker.min.js') }}" ></script>
    <script src="{{ asset('js/bootstrap-datepicker.js') }}" ></script>
    
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/toastr.min.js') }}"></script>
    <div id="site-url" style="display:none">{{ url('/') }}</div>
    <script type="text/javascript" src="{{ asset('js/form.js') }}"></script>
    <script type="text/javascript" src="{{ asset('app.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
    <script src="https://igorescobar.github.io/jQuery-Mask-Plugin/js/jquery.mask.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js" type="text/javascript"></script>


    {{-- Signature --}}
    {{-- <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script type="text/javascript" src="http://keith-wood.name/js/jquery.signature.js"></script> --}}
    @yield('footer')
    
</body>
</html>