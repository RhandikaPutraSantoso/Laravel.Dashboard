<style>
.sidebarbottom{
  position: absolute;
  top:300px
}


  #notif-items {
  max-height: 350px;
  overflow-y: auto;
  scroll-behavior: smooth;
}

.dropdown-item-title {
  font-weight: 600;
  font-size: 13px;
  text-transform: uppercase;
  padding: 8px 12px;
}

.notif-link {
  display: block;
  padding: 10px 12px;
  font-size: 13px;
  border-bottom: 1px solid #f1f1f1;
  color: #343a40;
  background-color: #fff;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  transition: background-color 0.2s ease;
}

.notif-link:hover {
  background-color: #f8f9fa;
  color: #007bff;
  text-decoration: none;
}

.notif-badge {
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.2); opacity: 0.7; }
  100% { transform: scale(1); opacity: 1; }
}

/* DROPDOWN SHADOW */
#notif-list {
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.767);
  border-radius: 8px;
  z-index: 1050;
  max-width: 360px; /* Lebar maksimum untuk desktop */
}

/* --- Media Queries untuk perangkat seluler --- */
@media (max-width: 768px) { /* Untuk layar dengan lebar maksimum 768px (tablet dan ponsel) */
  #notif-list {
    max-width: 90%; /* Mengatur lebar notifikasi menjadi 90% dari lebar layar */
    width: auto; /* Membiarkan lebar menyesuaikan secara otomatis */
    left: 50%; /* Menempatkan notifikasi di tengah */
    transform: translateX(-60%); /* Menggeser notifikasi ke kiri 50% dari lebarnya sendiri */
    position: fixed; /* Menjadikan posisi tetap di layar */
    top: 30px; /* Sedikit jarak dari atas */
    margin: 0 auto; /* Memastikan posisi tengah */
  }

  .notif-link {
    font-size: 14px; /* Sedikit memperbesar ukuran font untuk keterbacaan yang lebih baik */
    padding: 12px 15px; /* Menambah padding untuk area sentuh yang lebih besar */
  }

  .dropdown-item-title {
    font-size: 14px;
    padding: 10px 15px;
  }
}

@media (max-width: 480px) { /* Untuk layar dengan lebar maksimum 480px (ponsel kecil) */
  #notif-list {
    max-width: 110%; /* Mengatur lebar notifikasi menjadi 90% dari lebar layar */
    width: auto; /* Membiarkan lebar menyesuaikan secara otomatis */
    left: 50%; /* Menempatkan notifikasi di tengah */
    transform: translateX(-70%); /* Menggeser notifikasi ke kiri 50% dari lebarnya sendiri */
    position: fixed; /* Menjadikan posisi tetap di layar */
    top: 30px; /* Sedikit jarak dari atas */
    margin: 0 auto; /* Memastikan posisi tengah */
  }

  .notif-link,
  .dropdown-item-title {
    font-size: 13px; /* Menyesuaikan ukuran font lagi jika diperlukan */
    padding: 10px 12px;
  }
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
                <b class="label rounded label-sm primary" id="activity-sap-count">0</b>
              </span>
              <span class="nav-icon">
                <i class="material-icons">&#xe5c3;
                  <span ui-include="'{{ asset('layouts/assets/images/i_1.svg') }}'"></span>
                </i>
              </span>
              <span class="nav-text">Activity SAP</span>
            </a>
            <ul class="nav-sub">
              <li>
                <a href="{{ route('admin.activity.report') }}" class="{{ Request::routeIs('admin.activity.report') ? 'active' : '' }}">
                  Activity Report
                  <b class="label rounded label-xs primary ml-2" id="activity-sap-sub-count">0</b>
                </a>
              </li>
              <li>
                <a href="{{ route('admin.activity.status') }}" class="{{ Request::routeIs('admin.activity.status') ? 'active' : '' }}">
                  Activity Status
                  <b class="label rounded label-xs warning ml-2" id="activity-status-sub-count">0</b>
                </a>
              </li>
              <li>
                <a href="{{ route('admin.activity.solved') }}" class="{{ Request::routeIs('admin.activity.solved') ? 'active' : '' }}">
                  Activity Solved
                  <b class="label rounded label-xs success ml-2" id="activity-solved-sub-count">0</b>
                </a>
              </li>

              </ul>
          </li>


            <li>
              {{-- Check if any sub-item is active to keep the parent expanded --}}
              <a class="{{ Request::routeIs('admin.pengaturan.email', 'admin.pengaturan.difficult', 'admin.pengaturan.status') ? 'active' : '' }}">

                <span class="nav-caret">
                  <i class="fa fa-caret-down"></i>
                </span>
                <span class="nav-label">
                  
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

            <li class="sidebarbottom" >
              {{-- Check if any sub-item is active to keep the parent expanded --}}
              <a class=" ? 'active' : '' }}">

                <span class="nav-caret">
                  <i class="fa fa-caret-down"></i>
                </span>
                <span class="nav-label">
                  
                </span>
                <span class="nav-icon">
                  <i class="material-icons">&#xe8b8;
                    <span ui-include="'{{ asset('layouts/assets/images/i_1.svg') }}'"></span>
                  </i>
                </span>
                <span class="nav-text">service Activity log</span>
              </a>
              <ul class="nav-sub">
                {{-- service log --}}
                
                



              </ul>
            </li>




          </ul>
        </nav>
      </div>

          <div flex-no-shrink>
            <div ui-include="'{{ asset('layouts/views/blocks/aside.bottom.0.html') }}'"></div>
          </div>
        </div>
      </div>
      
                <div id="content" class=" app-content box-shadow-z0" role="main">
                  
                  <div  class="app-header white box-shadow navbar-md">
                      <div class="navbar navbar-toggleable-sm flex-row align-items-center">
                          <a data-toggle="modal" data-target="#aside" class="hidden-lg-up mr-3">
                            <i class="material-icons">&#xe5d2;</i>
                          </a>
                          <div class="mb-0 h5 no-wrap" ng-bind="$state.current.data.title" id="pageTitle"></div>


                          <ul class=" nav navbar-nav ml-auto flex-row">
                            <li class=" nav-item dropdown pos-stc-xs" id="notif-wrapper">
                        <a class="nav-link mr-2" href="#" id="notifDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <i class="material-icons">&#xe7f5;</i>
                          <span class="label label-sm up warn notif-badge" id="notif-count">0</span>
                        </a>

                        <div class=" dropdown-menu dropdown-menu-right white" aria-labelledby="notifDropdown" id="notif-list" style="min-width: 280px; max-width: 340px;">
                          <div class="dropdown-header text-info">📢 Notifikasi Baru</div>
                          <div  id="notif-items"></div>
                          <div class="dropdown-footer p-2 text-center ">
                            <a href="{{ route('admin.activity.report') }}" class="small text-primary">Lihat Semua Aktivitas</a>
                          </div>
                        </div>
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