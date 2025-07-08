@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
 /* Container utama log */
#activityLogCollapse {
    list-style: none;
    padding-left: 10px;
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
}

/* Setiap item log */
#activityLogCollapse li {
    position: relative;
    margin-bottom: 20px;
    margin-top: 5px;
    padding-left: 15px;
    color: #aaa;
    font-size: 13.5px;
    line-height: 1.5;
}

/* Bullet/titik log */
#activityLogCollapse li::before {
    content: "";
    position: absolute;
    left: -7px;
    top: 2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #ccc;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #ffffff;
}

/* Log terbaru di atas */
#activityLogCollapse li:first-child {
    font-weight: 600;
    color: inherit;
}

#activityLogCollapse li:first-child::before {
    background-color: #00b5ad;
}
#activityLogCollapse li:hover {
    color: #fff;
    
}
</style>



<!-- ############ PAGE Start-->


  <div class="app" id="app">

<div id="aside" class="app-aside modal fade folded md nav-expand">
    <div class="left navside indigo-900 dk" layout="column">
      <div class="navbar navbar-md no-radius">
        <a class="navbar-brand">
          
          <img src="{{ asset('layouts/assets/images/logo2.png') }}" >
          <span class="hidden-folded inline">SAP HANA</span>
        </a>
        </div>

      <div flex class="hide-scroll">
        <nav class="scroll nav-active-primary">
          <ul class="nav" ui-nav>
            <li class="nav-header hidden-folded">
              <small class="text-muted">Main</small>
            </li>

            <li>
              {{-- Compare the current URL with '/index' or pass a variable from the controller --}}
              <a href="{{ Route('user.dashboardUser') }}" class="{{ Request::is('index') ? 'active' : '' }}">
                <span class="nav-icon">
                  <i class="material-icons">&#xe3fc;
                    <span ui-include="'{{ asset('layouts/assets/images/i_0.svg') }}'"></span>
                  </i>
                </span>
                <span class="nav-text">Dashboard</span>
              </a>
            </li>

            <li>
              
              <a class="{{ Request::is('report') || Request::is('status') || Request::is('solved') ? 'active' : '' }}">
                <span class="nav-caret">
                  <i class="fa fa-caret-down"></i>
                </span>
               
                <span class="nav-icon">
                  <i class="material-icons">&#xe5c3;
                    <span ui-include="'{{ asset('layouts/assets/images/i_1.svg') }}'"></span>
                  </i>
                </span>
                <span class="nav-text">Activity SAP</span>
              </a>
              <ul class="nav-sub">
                <li><a href="{{ route('user.activity.report') }}">Activity Report</a></li>
                <li><a href="{{ route('user.activity.status') }}">Activity Status</a></li>
                <li><a href="{{ route('user.activity.solved') }}">Activity Solved</a></li>
              </ul>
            </li>
            
            
            <li class="nav-header hidden-folded">
              <small class="text-muted">Service</small>
            </li>
            <li>
              <a data-bs-toggle="collapse" href="#activityLogCollapse" aria-expanded="false">
                  <span class="nav-caret"><i class="fa fa-caret-down"></i></span>
                  <span class="nav-icon"><i class="material-icons">&#xe152;</i></span>
                  <span class="nav-text">Service Activity Log</span>
              </a>
              <ul class="nav-sub collapse show" id="activityLogCollapse" style="padding-left: 20px;">
                  <li><small class="text-muted" id="activityLogLoading">Memuat aktivitas...</small></li>
              </ul>
            </li>
            
          </ul>
        </nav>
      </div>

      
    </div>
  </div>
  
  <div id="content" class="app-content box-shadow-z0" role="main">
    <div class="app-header white box-shadow navbar-md">
    <div class="navbar navbar-toggleable-sm flex-row align-items-center">
        <a data-toggle="modal" data-target="#aside" class="hidden-lg-up mr-3">
            <i class="material-icons">&#xe5d2;</i>
        </a>
        <div class="mb-0 h5 no-wrap" ng-bind="$state.current.data.title" id="pageTitle"></div>

        <ul class="nav navbar-nav ml-auto flex-row">
            <li class="nav-item dropdown white">
                <a class="nav-link p-0 clear" href="#" data-toggle="dropdown">
                    <span class="avatar w-32">
                        <img src="{{ asset('layouts/assets/images/a0.jpg') }}" alt="...">
                        <i class="on b-white bottom"></i>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right white">
                    <a class="dropdown-item" href="{{ route('user.profile.edit') }}">
                        <i class="material-icons mr-2">&#xe7fd;</i> Update Profile
                    </a>
                    <a class="dropdown-item" href="{{ route('password.edit') }}">
                        <i class="material-icons mr-2">&#xe897;</i> Ganti Password
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('logout') }}">
                        <i class="material-icons mr-2">&#xe879;</i> Logout
                    </a>
                </div>
            </li>
        </ul>
    </div>
</div>

    <div class="app-footer">
      <div class="p-2 text-xs">
        <div class="pull-right text-muted py-1">
          &copy; Copyright <strong>SAP HANA</strong> <span class="hidden-xs-down">- Built With Brain</span>
          <a ui-scroll-to="content"><i class="fa fa-long-arrow-up p-x-sm"></i></a>
        </div>
        <div class="nav">
          <a class="nav-link" href="../">About</a>
        </div>
      </div>
    </div>
    <div ui-view class="app-body" id="view">
      <!-- ############ PAGE END-->
    