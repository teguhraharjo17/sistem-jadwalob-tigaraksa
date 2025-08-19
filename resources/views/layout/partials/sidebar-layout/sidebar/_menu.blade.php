<!--begin::Sidebar menu-->
<div class="app-sidebar-menu overflow-hidden flex-column-fluid">
	<!--begin::Menu wrapper-->
	<div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper hover-scroll-overlay-y my-5" 
		data-kt-scroll="true"
		data-kt-scroll-activate="true"
		data-kt-scroll-height="auto"
		data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
		data-kt-scroll-wrappers="#kt_app_sidebar_menu"
		data-kt-scroll-offset="5px"
		data-kt-scroll-save-state="true">
		
		<!--begin::Menu-->
		<div class="menu menu-column menu-rounded menu-sub-indention px-3" id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false">

			<!--begin::Menu item - Dashboard-->
			<div class="menu-item">
				<a class="menu-link" href="/dashboard">
					<span class="menu-icon">{!! getIcon('element-11', 'fs-2') !!}</span>
					<span class="menu-title">Dashboard</span>
				</a>
			</div>
			<!--end::Menu item-->

			<!--begin::Menu section title-->
			<div class="menu-item pt-5">
				<div class="menu-content">
					<span class="menu-heading fw-bold text-uppercase fs-7">Cleaning Services</span>
				</div>
			</div>
			<!--end::Menu section title-->

			<!--begin::Menu item - Checklist Area Pembersihan-->
			<div class="menu-item">
				<a class="menu-link {{ request()->routeIs('checklist.index') ? 'active' : '' }}" href="{{ route('checklist.index') }}">
					<span class="menu-icon">{!! getIcon('check-square', 'fs-2') !!}</span>
					<span class="menu-title">Checklist Area Pembersihan</span>
				</a>
			</div>
			<!--end::Menu item-->

			<!--begin::Menu item - Laporan Kerja Harian-->
			<div class="menu-item">
				<a class="menu-link {{ request()->routeIs('laporanharian.index') ? 'active' : '' }}" href="{{ route('laporanharian.index') }}">
					<span class="menu-icon">{!! getIcon('book', 'fs-2') !!}</span>
					<span class="menu-title">Laporan Kerja Harian</span>
				</a>
			</div>
			<!--end::Menu item-->

		</div>
		<!--end::Menu-->
	</div>
	<!--end::Menu wrapper-->
</div>
<!--end::Sidebar menu-->
