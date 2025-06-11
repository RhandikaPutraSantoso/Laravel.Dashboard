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
              <a href="{{ route('admin.dashboardAdmin') }}" class="{{ Request::routeIs('admin.dashboardAdmin') ? 'active' : '' }}">
                <span class="nav-icon">
                  <i class="material-icons">&#xe3fc;
                    <span ui-include="'{{ asset('layouts/assets/images/i_0.svg') }}'"></span>
                  </i>
                </span>
                <span class="nav-text">Dashboard</span>
              </a>
            </li>

            <li>
              {{-- Check if any sub-item is active to keep the parent expanded --}}
              <a class="{{ Request::routeIs('admin.activity.report', 'admin.activity.status', 'admin.activity.solved') ? 'active' : '' }}">
                <span class="nav-caret">
                  <i class="fa fa-caret-down"></i>
                </span>
                <span class="nav-label">
                  <b class="label rounded label-sm primary">3</b>
                </span>
                <span class="nav-icon">
                  <i class="material-icons">&#xe5c3;
                    <span ui-include="'{{ asset('layouts/assets/images/i_1.svg') }}'"></span>
                  </i>
                </span>
                <span class="nav-text">Activity SAP</span>
              </a>
              <ul class="nav-sub">
                <li><a href="{{ route('admin.activity.report') }}" class="{{ Request::routeIs('admin.activity.report') ? 'active' : '' }}">Activity Report</a></li>
                <li><a href="{{ route('admin.activity.status') }}" class="{{ Request::routeIs('admin.activity.status') ? 'active' : '' }}">Activity Status</a></li>
                <li><a href="{{ route('admin.activity.solved') }}" class="{{ Request::routeIs('admin.activity.solved') ? 'active' : '' }}">Activity Solved</a></li>

              </ul>
            </li>

            <li>
              {{-- Check if any sub-item is active to keep the parent expanded --}}
              <a class="{{ Request::routeIs('admin.pengaturan.email', 'admin.pengaturan.difficult', 'admin.pengaturan.status') ? 'active' : '' }}">

                <span class="nav-caret">
                  <i class="fa fa-caret-down"></i>
                </span>
                <span class="nav-label">
                  <b class="label rounded label-sm primary">3</b>
                </span>
                <span class="nav-icon">
                  <i class="material-icons">&#xe8b8;
                    <span ui-include="'{{ asset('layouts/assets/images/i_1.svg') }}'"></span>
                  </i>
                </span>
                <span class="nav-text">Setting</span>
              </a>
              <ul class="nav-sub">
                  <li><a href="{{ route('admin.pengaturan.email') }}" class="{{ Request::routeIs('admin.pengaturan.email') ? 'active' : '' }}">Email</a></li>
                  <li><a href="{{ route('admin.pengaturan.difficult') }}" class="{{ Request::routeIs('admin.pengaturan.difficult') ? 'active' : '' }}">Difficult Level</a></li>
                  <li><a href="{{ route('admin.pengaturan.status') }}" class="{{ Request::routeIs('admin.pengaturan.status') ? 'active' : '' }}">Status Level</a></li>

              </ul>
            </li>

            <li>
              {{-- Compare the current URL with '/index' or pass a variable from the controller --}}
              <a href="{{ Route('logout') }}" class="{{ Request::is('index') ? 'active' : '' }}">
                <span class="nav-icon">
                  <i class="material-icons">&#xe566;
                    <span ui-include="'{{ asset('layouts/assets/images/i_0.svg') }}'"></span>
                  </i>
                </span>
                <span class="nav-text">LogOut</span>
              </a>
            </li>

          </ul>
        </nav>
      </div>

      <div flex-no-shrink>
        <div ui-include="'{{ asset('layouts/views/blocks/aside.bottom.0.html') }}'"></div>
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
              <li class="nav-item dropdown pos-stc-xs">
                <a class="nav-link mr-2" href data-toggle="dropdown">
                  <i class="material-icons">&#xe7f5;</i>
                  <span class="label label-sm up warn">3</span>
                </a>
                <div ui-include="'../views/blocks/dropdown.notification.html'"></div>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link p-0 clear" href="#" data-toggle="dropdown">
                  <span class="avatar w-32">
                    <img src="{{ asset('layouts/assets/images/a0.jpg') }}" alt="...">
                    <i class="on b-white bottom"></i>
                  </span>
                </a>
                <div ui-include="'../views/blocks/dropdown.user.html'"></div>
              </li>
              <li class="nav-item hidden-md-up">
                <a class="nav-link pl-2" data-toggle="collapse" data-target="#collapse">
                  <i class="material-icons">&#xe5d4;</i>
                </a>
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